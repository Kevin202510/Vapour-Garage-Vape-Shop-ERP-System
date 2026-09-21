<?php

namespace App\Contracts\Repositories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Collection;

interface AttendanceRepositoryInterface extends BaseRepositoryInterface
{
    public function findById(int $id, array $relations = []): ?Attendance;

    public function getByEmployee(int $employeeId, string $from, string $to): Collection;

    public function getByBranchAndDate(int $branchId, string $date): Collection;

    public function findTodayRecord(int $employeeId): ?Attendance;

    /**
     * Find the employee's most recent still-open shift (time_out is null),
     * regardless of work_date — used for clock-out so a shift spanning
     * midnight is still found.
     */
    public function findOpenRecord(int $employeeId): ?Attendance;

    public function clockIn(int $employeeId, int $branchId): Attendance;

    public function clockOut(Attendance $attendance): Attendance;

    /**
     * Force-close a shift that was never clocked out, anchoring hours worked
     * to a fixed end-of-shift time on the record's own work_date (never to
     * "now", which could be days later and produce nonsensical hours).
     */
    public function autoCloseRecord(Attendance $attendance, string $defaultTimeOut = '23:59:59'): Attendance;

    /**
     * Shifts still open (time_out is null) from before the given date —
     * i.e. spanning at least one full day, almost certainly a forgotten
     * clock-out rather than a shift genuinely still in progress.
     */
    public function getOpenRecordsBefore(string $date): Collection;

    public function getMonthlySummary(int $employeeId, int $year, int $month): array;
}
