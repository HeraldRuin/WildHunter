<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
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
        Payment::query()
            ->where('status', Payment::PROCESSING)
            ->whereNotNull('invoice_id')
            ->where(function ($q) {
                $q->whereNull('next_check_at')
                    ->orWhere('next_check_at', '<=', now());
            })
            ->limit(100)
            ->get()
            ->each(function ($payment) {

                $service = app(PaymentService::class);

                $status = $service->checkStatus($payment->invoice_id);

                $payment->update([
                    'last_checked_at' => now(),
                ]);

                if ($status === Payment::PAID) {
                    $service->handlePaymentSuccess($payment);

                    $payment->update([
                        'next_check_at' => null,
                    ]);

                    return;
                }

                if ($payment->expires_at && now()->greaterThan($payment->expires_at)) {
                    $payment->update([
                        'status' => Payment::FAILED,
                        'next_check_at' => null,
                    ]);

                    return;
                }

                $attempt = $payment->attempts + 1;
                $age = now()->diffInSeconds($payment->created_at);

                $delay = match (true) {

                    $age <= 900 => 60,        // 0–15 мин → каждую минуту
                    $age <= 7200 => 120,      // до 2 часов → каждые 2 мин
                    default => 900,           // дальше → каждые 15 мин
                };

                $payment->update([
                    'attempts' => $attempt,
                    'next_check_at' => now()->addSeconds($delay),
                ]);
            });
    }
}
