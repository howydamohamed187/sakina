<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('prayer-notifications:send')
    ->everyMinute()
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('occasion-reminders:send')
    ->dailyAt((string) config('reminders.occasion_time', '08:00'))
    ->withoutOverlapping()
    ->onOneServer();
