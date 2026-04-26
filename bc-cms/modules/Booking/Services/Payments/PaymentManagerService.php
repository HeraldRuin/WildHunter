<?php

namespace Modules\Booking\Services\Payments;

use Modules\Booking\Models\Booking;
use Modules\Booking\Models\Payment;

class PaymentManagerService
{
    public function __construct(protected PaymentService $paymentService)
    {
    }

    public function getStatusFromPayment(Booking $booking, $userId): array
    {
        $payment = $booking->payments()->where('create_user', $userId)->first();

        $status = $payment?->status ?? Payment::PROCESSING;

        return [
            'data' => [
                'status' => $status,
            ],
        ];
    }

    public function store(Booking $booking, $userId): array
    {
        $paymentUrl = $this->paymentService->getOrCreatePrepayment($booking, $userId);

        return [
            'data' => [
                'payment_url' => $paymentUrl,
            ],
        ];
    }
}
