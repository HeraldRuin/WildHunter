<?php

namespace Modules\Booking\Services\Payments;

use Modules\Booking\Gateways\PaymentGatewayResolver;
use Modules\Booking\Jobs\SendCheckToEmailJob;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\Payment;

class PaymentService
{
    public function __construct(private readonly PaymentGatewayResolver $gatewayResolver) {}
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
        $payment = Payment::where('booking_id', $booking->id)
            ->where('status', Booking::PROCESSING)
            ->where('create_user', $userId)
            ->first();

        if (!$payment) {
            return null;
        }

        if ($this->isExpired($payment)) {
            $this->expirePaymentLink($payment);
            return null;
        }

        return $payment;
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
            SendCheckToEmailJob::dispatch($result['invoice_id'])->afterResponse();
        }

        return $url;
    }

//
//        //TODO сделать проверку, что клиент оплатил счет и тогда делать что он оплатил
//
//        $booking->invitationUser(Auth::id())?->update(['prepayment_paid' => true, 'prepayment_paid_status' => BookingHunterInvitation::PREPAYMENT_PAID]);
//
//        if ($booking->countAcceptedAndPaidHunters() !== $booking->countAcceptedHunters()) {
//            return $this->sendSuccess([
//                'message' => __('Payment already created'),
//                'payment_url' => $url,
//            ]);
//        }
//
//        $this->bookingTimerService->startBedTimer($booking);
//
//        $booking->prepayment_paid = true;
//        $booking->save();
}
