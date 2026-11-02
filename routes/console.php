<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('reminders:send-due')
    ->dailyAt('07:00')
    ->timezone(config('app.display_timezone'))
    ->withoutOverlapping();

Schedule::command('inventory:low-stock-alerts')
    ->dailyAt('07:05')
    ->timezone(config('app.display_timezone'))
    ->withoutOverlapping();
