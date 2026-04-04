<?php

namespace App\Repositories;

use App\Contracts\Repositories\EmployeeRepositoryInterface;
use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EmployeeRepository extends BaseRepository implements EmployeeRepositoryInterface
{
    public function __construct(Employee $model)
    {
        parent::__construct($model);
    }

    public function findById(int $id, array $relations = []): ?Employee
    {
        return $this->model
            ->with($relations ?: ['branch', 'department'])
            ->withTrashed()
            ->find($id);
    }

    public function findByCode(string $code): ?Employee
    {
        return $this->model
            ->with(['branch', 'department'])
            ->where('employee_code', $code)
            ->first();
    }

    public function getByBranch(int $branchId, bool $activeOnly = true): Collection
    {
        return $this->model
            ->with(['department'])
            ->where('branch_id', $branchId)
            ->when($activeOnly, fn ($q) => $q->where('is_active', true))
            ->orderBy('last_name')
            ->get();
    }

    public function getByRole(string $role, ?int $branchId = null): Collection
    {
        return $this->model
            ->with(['branch', 'department'])
            ->where('role', $role)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('is_active', true)
            ->orderBy('last_name')
            ->get();
    }

    public function restore(int $id): bool
    {
        return (bool) $this->model->withTrashed()->find($id)?->restore();
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->buildQuery($filters)->paginate($perPage);
    }

    protected function buildQuery(array $filters = [])
    {
        return $this->model
            ->with(['branch', 'department'])
            ->when(isset($filters['branch_id']),      fn ($q) => $q->where('branch_id', $filters['branch_id']))
            ->when(isset($filters['department_id']),  fn ($q) => $q->where('department_id', $filters['department_id']))
            ->when(isset($filters['role']),           fn ($q) => $q->where('role', $filters['role']))
            ->when(isset($filters['employment_type']),fn ($q) => $q->where('employment_type', $filters['employment_type']))
            ->when(isset($filters['is_active']),      fn ($q) => $q->where('is_active', $filters['is_active']))
            ->when(isset($filters['with_trashed']),   fn ($q) => $q->withTrashed())
            ->when(isset($filters['search']), fn ($q) => $q->where(fn ($s) =>
                $s->where('first_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('last_name',  'like', '%' . $filters['search'] . '%')
                  ->orWhere('employee_code', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%')
            ))
            ->orderBy($filters['sort_by'] ?? 'last_name', $filters['sort_order'] ?? 'asc');
    }
}
