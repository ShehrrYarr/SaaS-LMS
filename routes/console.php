<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Shared hosting: process one queued job per minute via cron `* * * * * php artisan schedule:run`
Schedule::command('queue:work --once --timeout=60')
    ->everyMinute()
    ->withoutOverlapping();
