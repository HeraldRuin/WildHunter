<?php

namespace Modules\Booking\Jobs;

use Modules\Booking\Models\Payment;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Booking\Services\Payments\PaymentService;

class CheckPaymentStatusJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $invoiceId,
        public int $attempt = 0
    ) {}

    public function handle(PaymentService $paymentService): void
    {
        $payment = $paymentService->queryByInvoice($this->invoiceId)->first();

        if (!$payment || $payment->status !== Payment::PROCESSING) {
            return;
        }

        $status = $paymentService->checkStatus($this->invoiceId);

        if ($status === Payment::PAID) {
            $paymentService->handlePaymentSuccess($payment);
            return;
        }

        $this->releaseWithBackoff();
    }

    protected function releaseWithBackoff(): void
    {
        $delays = [120, 300, 600, 1800];

        if ($this->attempt >= count($delays)) {
            return;
        }

        $delay = $delays[$this->attempt];

        self::dispatch($this->invoiceId, $this->attempt + 1)->delay(now()->addSeconds($delay));
    }
}
