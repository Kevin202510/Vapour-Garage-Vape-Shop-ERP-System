<?php

namespace App\Repositories;

use App\Contracts\Repositories\PayrollRepositoryInterface;
use App\Models\Payroll;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PayrollRepository extends BaseRepository implements PayrollRepositoryInterface
{
    public function __construct(Payroll $model)
    {
        parent::__construct($model);
    }

    public function findById(int $id, array $relations = []): ?Payroll
    {
        return $this->model
            ->with($relations ?: ['employee.branch', 'processor'])
            ->find($id);
    }

    public function lockForUpdate(int $id, array $relations = []): ?Payroll
    {
        return $this->model
            ->with($relations ?: ['employee.branch', 'processor'])
            ->lockForUpdate()
            ->find($id);
    }

    public function getByEmployee(int $employeeId): Collection
    {
        return $this->model
            ->where('employee_id', $employeeId)
            ->orderBy('period_start', 'desc')
            ->get();
    }

    public function getByBranchAndPeriod(int $branchId, string $periodStart, string $periodEnd): Collection
    {
        return $this->model
            ->with(['employee'])
            ->whereHas('employee', fn ($q) => $q->where('branch_id', $branchId))
            ->where('period_start', $periodStart)
            ->where('period_end', $periodEnd)
            ->orderBy('employee_id')
            ->get();
    }

    public function approve(Payroll $payroll, int $processedBy): Payroll
    {
        $payroll->update([
            'status'       => 'approved',
            'processed_by' => $processedBy,
            'processed_at' => now(),
        ]);

        return $payroll->fresh(['employee', 'processor']);
    }

    public function release(Payroll $payroll): Payroll
    {
        $payroll->update(['status' => 'released']);

        return $payroll->fresh(['employee']);
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->buildQuery($filters)->paginate($perPage);
    }

    protected function buildQuery(array $filters = [])
    {
        return $this->model
            ->with(['employee.branch'])
            ->when(isset($filters['employee_id']),  fn ($q) => $q->where('employee_id', $filters['employee_id']))
            ->when(isset($filters['branch_id']),    fn ($q) =>
                $q->whereHas('employee', fn ($e) => $e->where('branch_id', $filters['branch_id']))
            )
            ->when(isset($filters['status']),       fn ($q) => $q->where('status', $filters['status']))
            ->when(isset($filters['period_start']), fn ($q) => $q->where('period_start', $filters['period_start']))
            ->when(isset($filters['period_end']),   fn ($q) => $q->where('period_end', $filters['period_end']))
            ->when(isset($filters['search']),       fn ($q) => $q->whereHas('employee', fn ($e) =>
                $e->where('first_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('last_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('employee_code', 'like', '%' . $filters['search'] . '%')
            ))
            ->orderBy($filters['sort_by'] ?? 'period_start', $filters['sort_order'] ?? 'desc');
    }
}
