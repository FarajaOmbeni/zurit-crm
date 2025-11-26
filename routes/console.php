<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule task reminders to run daily at 9 AM
Schedule::command('tasks:send-reminders')
    ->dailyAt('09:00')
    ->description('Send email reminders for tasks due soon');

// Schedule follow-up processing to run hourly
Schedule::command('follow-ups:process')
    ->hourly()
    ->description('Process due follow-up schedules and create tasks');

// Schedule follow-up emails to run every 2 hours
Schedule::command('follow-ups:send-emails')
    ->everyTwoHours()
    ->description('Send scheduled follow-up reminder emails to users');
