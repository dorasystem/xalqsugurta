<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Deletes API journal rows older than ApiLog::KEEP_DAYS (needs the scheduler cron: `php artisan schedule:run` every minute)
Schedule::command('model:prune')->daily();
