<?php

use Illuminate\Console\Scheduling\Schedule;
use Modules\Booking\Jobs\CheckPaymentStatusJob;

// Заполняет автоматически койки теми, кто не выбрал для себя место в номере
app()->booted(function () {
    $schedule = app(Schedule::class);
    $schedule->command('beds:process-expired')->everyMinute()->timezone('Europe/Moscow');
    $schedule->job(new CheckPaymentStatusJob())->everyMinute()->timezone('Europe/Moscow');
});
