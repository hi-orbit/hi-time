<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule the cleanup of completed tasks to run daily at 2 AM
Schedule::command('tasks:cleanup-completed')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Completed tasks cleanup ran successfully');
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Completed tasks cleanup failed to run');
    });
