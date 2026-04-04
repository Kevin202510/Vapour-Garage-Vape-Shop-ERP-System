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

    public function clockIn(int $employeeId, int $branchId): Attendance;

    public function clockOut(Attendance $attendance): Attendance;

    public function getMonthlySummary(int $employeeId, int $year, int $month): array;
}
