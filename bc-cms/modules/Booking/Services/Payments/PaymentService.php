<?php

namespace Modules\Booking\Services\Payments;

use Illuminate\Database\Eloquent\Builder;
use Modules\Booking\Gateways\PaymentGatewayResolver;
use Modules\Booking\Jobs\SendCheckToEmailJob;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingHunterInvitation;
use Modules\Booking\Models\Payment;
use Modules\Booking\Services\BookingTimerService;

class PaymentService
{
    public function __construct(private readonly PaymentGatewayResolver $gatewayResolver, public BookingTimerService $bookingTimerService) {}
    public function getOrCreatePrepayment(Booking $booking, int $userId)
    {
        $payment = $this->findValidPayment($booking, $userId);

        if ($payment) {
            return $payment->payment_url;
        }

        return $this->createPayment($booking, $userId);
    }
    private function findValidPayment(Booking $booking, int $userId): ?Payment
    {
        $payment = $this->queryPayments($booking, $userId, Booking::PROCESSING)->first();

        if (!$payment) {
            return null;
        }

        if ($this->isExpired($payment)) {
            $this->expirePaymentLink($payment);
            return null;
        }

        return $payment;
    }

    public function queryPayments(Booking $booking, int $userId, $status): Builder
    {
        return Payment::query()
            ->byBooking($booking->id)
            ->byUser($userId)
            ->byStatus($status);
    }
    public function queryByInvoice(int $invoiceId): Builder
    {
        return Payment::query()
            ->byInvoice($invoiceId);
    }
    private function isExpired(Payment $payment): bool
    {
        $ttlPayLive = config('paykeeper.pay_ttl_days');

        return now()->greaterThan(
            $payment->created_at->copy()->addDays($ttlPayLive)
        );
    }

    public function expirePaymentLink(Payment $payment): void
    {
        $gateway = $this->gatewayResolver->resolve();
        $deleted = $gateway->deleteInvoice($payment->invoice_id);

        if ($deleted) {
            $payment->delete();
        }
    }

    public function createPayment(Booking $booking, int $userId): string
    {
        $gateway = $this->gatewayResolver->resolve();
        $dto = $gateway->handlePurchaseData(['amount' => $booking->getAmountPerPerson()], $booking);
        $result = $gateway->createOrder($dto);
        $url = $result['invoice_url'];

        $gateway->processFromBooking([
            'amount' => $booking->getAmountPerPerson(),
            'payment_url' => $url,
            'invoice_id' => $result['invoice_id'],
        ], $booking);

        if (config('paykeeper.send_check')) {
            SendCheckToEmailJob::dispatch($result['invoice_id'])->onQueue('low');
        }

        return $url;
    }

    public function checkStatus($invoiceId)
    {
        $gateway = $this->gatewayResolver->resolve();
        $result = $gateway->getPayKeeperOrder($invoiceId);

        return $result['status'];
    }
    public function handlePaymentSuccess(Payment $payment): void
    {
        if (!$payment->markAsPaid($payment)) {
            return;
        }

        $booking = $payment->booking;
        $userId = $payment->create_user;

       $booking->invitationUser($userId)?->update(['prepayment_paid' => true, 'prepayment_paid_status' => BookingHunterInvitation::PREPAYMENT_PAID]);

        if ($booking->countAcceptedAndPaidHunters() !== $booking->countAcceptedHunters()) {
            return;
        }

        $this->bookingTimerService->startBedTimer($booking);
        $booking->prepayment_paid = true;
        $booking->save();
    }
}
