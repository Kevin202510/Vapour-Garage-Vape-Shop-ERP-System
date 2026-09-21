<?php

namespace App\Services;

use App\Contracts\Repositories\AttendanceRepositoryInterface;
use App\Contracts\Repositories\AuditLogRepositoryInterface;
use App\Contracts\Repositories\EmployeeRepositoryInterface;
use App\Models\Attendance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    public function __construct(
        protected AttendanceRepositoryInterface $repo,
        protected EmployeeRepositoryInterface   $employeeRepo,
        protected AuditLogRepositoryInterface   $auditRepo,
    ) {}

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repo->paginate($perPage, $filters);
    }

    public function findOrFail(int $id): Attendance
    {
        return $this->repo->findById($id)
            ?? throw new ModelNotFoundException("Attendance record [{$id}] not found.");
    }

    public function getByEmployee(int $employeeId, string $from, string $to): Collection
    {
        return $this->repo->getByEmployee($employeeId, $from, $to);
    }

    public function getByBranchAndDate(int $branchId, string $date): Collection
    {
        return $this->repo->getByBranchAndDate($branchId, $date);
    }

    public function clockIn(int $employeeId, int $branchId): Attendance
    {
        $employee = $this->employeeRepo->findById($employeeId)
            ?? throw new ModelNotFoundException("Employee [{$employeeId}] not found.");

        if (! $employee->is_active) {
            throw ValidationException::withMessages([
                'employee_id' => ['Inactive employees cannot clock in.'],
            ]);
        }

        return $this->repo->clockIn($employeeId, $branchId);
    }

    public function clockOut(int $employeeId): Attendance
    {
        $record = $this->repo->findOpenRecord($employeeId)
            ?? throw new ModelNotFoundException("No open clock-in record found.");

        if ($record->time_out !== null) {
            throw ValidationException::withMessages([
                'employee_id' => ['Employee has already clocked out today.'],
            ]);
        }

        return $this->repo->clockOut($record);
    }

    public function manualEntry(array $data): Attendance
    {
        $existing = $this->repo->getByEmployee(
            $data['employee_id'],
            $data['work_date'],
            $data['work_date']
        )->first();

        if ($existing) {
            return $this->repo->update($existing, $data);
        }

        return $this->repo->create($data);
    }

    public function update(int $id, array $data): Attendance
    {
        return $this->repo->update($this->findOrFail($id), $data);
    }

    public function delete(int $id): bool
    {
        return $this->repo->delete($this->findOrFail($id));
    }

    public function getMonthlySummary(int $employeeId, int $year, int $month): array
    {
        return $this->repo->getMonthlySummary($employeeId, $year, $month);
    }

    /**
     * Force-close any shift still open from before today — an employee who
     * forgot to clock out. Anchors hours worked to a fixed end-of-shift time
     * on the shift's own work_date, not to "now".
     *
     * @return array{closed: int, employee_names: array<string>}
     */
    public function autoCloseForgottenShifts(string $defaultTimeOut = '23:59:59'): array
    {
        $open  = $this->repo->getOpenRecordsBefore(now()->toDateString());
        $names = [];

        DB::transaction(function () use ($open, $defaultTimeOut, &$names) {
            foreach ($open as $record) {
                $closed = $this->repo->autoCloseRecord($record, $defaultTimeOut);

                $this->auditRepo->log(
                    tableName: 'attendances',
                    recordId:  $record->id,
                    action:    'update',
                    oldValues: ['time_out' => null],
                    newValues: ['time_out' => $defaultTimeOut, 'hours_worked' => (string) $closed->hours_worked],
                    branchId:  $record->branch_id,
                );

                $names[] = $record->employee?->full_name ?? "Employee #{$record->employee_id}";
            }
        });

        return [
            'closed'         => count($names),
            'employee_names' => $names,
        ];
    }
}
