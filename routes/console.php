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
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/sales-periods.log'))
    ->onFailure(fn () => \Illuminate\Support\Facades\Log::error('sales:close-period daily failed — see storage/logs/sales-periods.log'));

// Weekly close: runs every Monday at 00:10 AM — closes last week (Mon–Sun)
Schedule::command('sales:close-period weekly')
    ->weeklyOn(1, '00:10')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/sales-periods.log'))
    ->onFailure(fn () => \Illuminate\Support\Facades\Log::error('sales:close-period weekly failed — see storage/logs/sales-periods.log'));

// Monthly close: runs on the 1st at 00:15 AM — closes last month
Schedule::command('sales:close-period monthly')
    ->monthlyOn(1, '00:15')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/sales-periods.log'))
    ->onFailure(fn () => \Illuminate\Support\Facades\Log::error('sales:close-period monthly failed — see storage/logs/sales-periods.log'));

// Yearly close: runs on January 1st at 00:20 AM — closes last year
Schedule::command('sales:close-period yearly')
    ->yearlyOn(1, 1, '00:20')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/sales-periods.log'))
    ->onFailure(fn () => \Illuminate\Support\Facades\Log::error('sales:close-period yearly failed — see storage/logs/sales-periods.log'));

// ── Sales report schedule ────────────────────────────────────────────────────
// Ensure `php artisan schedule:run` is called every minute via cron:
//   * * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1

// Daily: generated automatically at 12:00 AM (midnight) for the previous day's sales
Schedule::command('sales:send-report daily')
    ->dailyAt('00:00')
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

// ── Operational cleanup ──────────────────────────────────────────────────────

// Forgotten clock-outs: force-close any shift left open from a previous day
// so payroll generation (which reads hours_worked) isn't computed against an
// open-ended shift.
Schedule::command('attendance:auto-clockout')
    ->dailyAt('00:30')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/attendance-auto-clockout.log'))
    ->onFailure(fn () => \Illuminate\Support\Facades\Log::error('attendance:auto-clockout failed — see storage/logs/attendance-auto-clockout.log'));

// Abandoned online orders: cancel storefront orders staff never confirmed.
// A pending order never touches inventory (stock is only decremented on
// fulfillment), so this is a pure status change — safe to automate.
Schedule::command('orders:cancel-stale --hours=48')
    ->hourly()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/orders-cancel-stale.log'))
    ->onFailure(fn () => \Illuminate\Support\Facades\Log::error('orders:cancel-stale failed — see storage/logs/orders-cancel-stale.log'));

// Queue/token housekeeping — built-in Artisan commands, no custom code needed.
// Failed jobs older than 7 days are pruned; Sanctum tokens past their
// expiration are pruned (a no-op today since config/sanctum.php has no
// expiration set, but safe to leave scheduled if that's configured later).
Schedule::command('queue:prune-failed --hours=168')->daily();
Schedule::command('sanctum:prune-expired --hours=24')->daily();
