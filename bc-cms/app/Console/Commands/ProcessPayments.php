<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Modules\Booking\Models\Payment;
use Modules\Booking\Services\Payments\PaymentService;

class ProcessPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Cache::lock('payment-cron-lock', 120)->block(10, function () {

            Payment::query()
                ->where('status', Payment::PROCESSING)
                ->whereNotNull('invoice_id')
                ->where(function ($q) {
                    $q->whereNull('next_check_at')
                        ->orWhere('next_check_at', '<=', now());
                })
                ->orderBy('id')
                ->chunkById(100, function ($payments) {

                    foreach ($payments as $payment) {

                        try {
                            $service = app(PaymentService::class);

                            $status = $service->checkStatus($payment->invoice_id);

                            $payment->update([
                                'last_checked_at' => now(),
                            ]);

                            if ($status === Payment::PAID) {

                                $service->handlePaymentSuccess($payment);

                                $payment->update([
                                    'status' => Payment::PAID,
                                    'next_check_at' => null,
                                ]);

                                logger()->info('Payment PAID', [
                                    'payment_id' => $payment->id,
                                ]);

                                continue;
                            }

                            if ($payment->expires_at && now()->greaterThan($payment->expires_at)) {

                                $payment->update([
                                    'status' => Payment::FAILED,
                                    'next_check_at' => null,
                                ]);

                                continue;
                            }

                            $attempt = $payment->attempts + 1;
                            $age = now()->diffInSeconds($payment->created_at);

                            $delay = match (true) {
                                $age <= 900 => 60,
                                $age <= 7200 => 120,
                                default => 900,
                            };

                            $payment->update([
                                'attempts' => $attempt,
                                'next_check_at' => now()->addSeconds($delay),
                            ]);

                        } catch (\Throwable $e) {

                            logger()->error('Payment check failed', [
                                'payment_id' => $payment->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                });
        });
    }
}
