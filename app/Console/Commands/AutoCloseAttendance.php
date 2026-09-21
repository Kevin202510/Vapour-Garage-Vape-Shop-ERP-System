<?php

namespace App\Console\Commands;

use App\Services\AttendanceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class AutoCloseAttendance extends Command
{
    protected $signature   = 'attendance:auto-clockout {--time=23:59:59 : Time of day to record as the forced clock-out}';
    protected $description = 'Force clock-out any attendance record left open from a previous day (forgotten clock-out).';

    public function __construct(protected AttendanceService $service)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $defaultTimeOut = $this->option('time');

        try {
            $result = $this->service->autoCloseForgottenShifts($defaultTimeOut);
        } catch (Throwable $e) {
            Log::error("attendance:auto-clockout failed: {$e->getMessage()}", ['exception' => $e]);
            $this->error("Failed: {$e->getMessage()}");
            return self::FAILURE;
        }

        if ($result['closed'] === 0) {
            $this->info('No forgotten clock-outs found.');
            return self::SUCCESS;
        }

        $this->info("Auto clocked-out {$result['closed']} forgotten shift(s):");
        foreach ($result['employee_names'] as $name) {
            $this->line("  - {$name}");
        }

        return self::SUCCESS;
    }
}
