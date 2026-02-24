<?php

use Illuminate\Console\Scheduling\Schedule;

// Заполняет автоматически койки теми, кто не выбрал для себя место в номере
app()->booted(function () {
    $schedule = app(Schedule::class);
    $schedule->command('beds:process-expired')->everyMinute()->timezone('Europe/Moscow');
});
