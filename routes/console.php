<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Sales period closing (reset) schedule ────────────────────────────────────
// Each job runs just after midnight/end-of-period so it captures the full period.

// Daily close: runs at 00:05 AM — closes yesterday
Schedule::command('sales:close-period daily')
    ->dailyAt('00:05')
    ->appendOutputTo(storage_path('logs/sales-periods.log'));

// Weekly close: runs every Monday at 00:10 AM — closes last week (Mon–Sun)
Schedule::command('sales:close-period weekly')
    ->weeklyOn(1, '00:10')
    ->appendOutputTo(storage_path('logs/sales-periods.log'));

// Monthly close: runs on the 1st at 00:15 AM — closes last month
Schedule::command('sales:close-period monthly')
    ->monthlyOn(1, '00:15')
    ->appendOutputTo(storage_path('logs/sales-periods.log'));

// Yearly close: runs on January 1st at 00:20 AM — closes last year
Schedule::command('sales:close-period yearly')
    ->yearlyOn(1, 1, '00:20')
    ->appendOutputTo(storage_path('logs/sales-periods.log'));

// ── Sales report schedule ────────────────────────────────────────────────────
// Ensure `php artisan schedule:run` is called every minute via cron:
//   * * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1

// Daily: sent at 6:00 AM every day for the previous day's sales
Schedule::command('sales:send-report daily')
    ->dailyAt('06:00')
    ->appendOutputTo(storage_path('logs/sales-reports.log'));

// Weekly: sent every Monday at 7:00 AM for the previous week (Mon–Sun)
Schedule::command('sales:send-report weekly')
    ->weeklyOn(1, '07:00')
    ->appendOutputTo(storage_path('logs/sales-reports.log'));

// Monthly: sent on the 1st of every month at 7:00 AM for the previous month
Schedule::command('sales:send-report monthly')
    ->monthlyOn(1, '07:00')
    ->appendOutputTo(storage_path('logs/sales-reports.log'));

// Yearly: sent on January 1st at 8:00 AM for the previous year
Schedule::command('sales:send-report yearly')
    ->yearlyOn(1, 1, '08:00')
    ->appendOutputTo(storage_path('logs/sales-reports.log'));
