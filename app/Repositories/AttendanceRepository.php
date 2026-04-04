<?php

namespace App\Repositories;

use App\Contracts\Repositories\AttendanceRepositoryInterface;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Collection;

class AttendanceRepository extends BaseRepository implements AttendanceRepositoryInterface
{
    public function __construct(Attendance $model)
    {
        parent::__construct($model);
    }

    public function findById(int $id, array $relations = []): ?Attendance
    {
        return $this->model
            ->with($relations ?: ['employee', 'branch'])
            ->find($id);
    }

    public function getByEmployee(int $employeeId, string $from, string $to): Collection
    {
        return $this->model
            ->with(['branch'])
            ->where('employee_id', $employeeId)
            ->whereBetween('work_date', [$from, $to])
            ->orderBy('work_date')
            ->get();
    }

    public function getByBranchAndDate(int $branchId, string $date): Collection
    {
        return $this->model
            ->with(['employee.department'])
            ->where('branch_id', $branchId)
            ->where('work_date', $date)
            ->orderBy('time_in')
            ->get();
    }

    public function findTodayRecord(int $employeeId): ?Attendance
    {
        return $this->model
            ->where('employee_id', $employeeId)
            ->where('work_date', now()->toDateString())
            ->first();
    }

    public function clockIn(int $employeeId, int $branchId): Attendance
    {
        $existing = $this->findTodayRecord($employeeId);

        if ($existing) {
            return $existing; // Already clocked in today
        }

        $timeIn  = now();
        $cutoff  = now()->setTime(9, 0, 0);
        $status  = $timeIn->gt($cutoff) ? 'late' : 'present';

        return $this->model->create([
            'employee_id'    => $employeeId,
            'branch_id'      => $branchId,
            'work_date'      => $timeIn->toDateString(),
            'time_in'        => $timeIn->format('H:i:s'),
            'status'         => $status,
            'hours_worked'   => 0,
            'overtime_hours' => 0,
        ]);
    }

    public function clockOut(Attendance $attendance): Attendance
    {
        $timeOut     = now();
        $timeIn      = \Carbon\Carbon::createFromTimeString($attendance->time_in);
        $hoursWorked = round($timeIn->diffInMinutes($timeOut) / 60, 2);
        $regularHrs  = min(8, $hoursWorked);
        $overtime    = max(0, round($hoursWorked - 8, 2));

        $attendance->update([
            'time_out'       => $timeOut->format('H:i:s'),
            'hours_worked'   => $regularHrs,
            'overtime_hours' => $overtime,
        ]);

        return $attendance->fresh(['employee', 'branch']);
    }

    public function getMonthlySummary(int $employeeId, int $year, int $month): array
    {
        $records = $this->model
            ->where('employee_id', $employeeId)
            ->whereYear('work_date', $year)
            ->whereMonth('work_date', $month)
            ->get();

        return [
            'present'        => $records->where('status', 'present')->count(),
            'late'           => $records->where('status', 'late')->count(),
            'absent'         => $records->where('status', 'absent')->count(),
            'half_day'       => $records->where('status', 'half_day')->count(),
            'leave'          => $records->where('status', 'leave')->count(),
            'total_hours'    => round($records->sum('hours_worked'), 2),
            'overtime_hours' => round($records->sum('overtime_hours'), 2),
        ];
    }

    protected function buildQuery(array $filters = [])
    {
        return $this->model
            ->with(['employee', 'branch'])
            ->when(isset($filters['employee_id']), fn ($q) => $q->where('employee_id', $filters['employee_id']))
            ->when(isset($filters['branch_id']),   fn ($q) => $q->where('branch_id', $filters['branch_id']))
            ->when(isset($filters['status']),      fn ($q) => $q->where('status', $filters['status']))
            ->when(isset($filters['date_from']),   fn ($q) => $q->where('work_date', '>=', $filters['date_from']))
            ->when(isset($filters['date_to']),     fn ($q) => $q->where('work_date', '<=', $filters['date_to']))
            ->when(isset($filters['work_date']),   fn ($q) => $q->where('work_date', $filters['work_date']))
            ->orderBy('work_date', 'desc');
    }
}
