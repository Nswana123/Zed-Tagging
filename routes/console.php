<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();
Schedule::command('update-claim-status')->hourly();
//Schedule::command('send-hourly-report')->everyMinute();
Schedule::command('daily-ticket-summary')->dailyAt('17:00');
Schedule::command('daily-ticket-reminder')->dailyAt('08:00');