<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Booking\Models\Booking;
use Modules\Booking\Services\BookingTimerService;
use Symfony\Component\Console\Command\Command as CommandAlias;

class ProcessExpiredBedsTimers extends Command
{
    protected BookingTimerService $timerService;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'beds:process-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process expired beds timers';

    public function __construct(BookingTimerService $timerService)
    {
        parent::__construct();
        $this->timerService = $timerService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('ljl');
//        $this->timerService->processExpiredBeds();

        return CommandAlias::SUCCESS;
    }
}
