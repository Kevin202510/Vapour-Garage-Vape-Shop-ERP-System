<?php

namespace App\Services;

use App\Contracts\Repositories\EmployeeRepositoryInterface;
use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class EmployeeService
{
    public function __construct(protected EmployeeRepositoryInterface $repo) {}

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repo->paginate($perPage, $filters);
    }

    public function findOrFail(int $id): Employee
    {
        return $this->repo->findById($id) ?? throw new ModelNotFoundException("Employee [{$id}] not found.");
    }

    public function create(array $data): Employee
    {
        $this->guardDuplicateCode($data['employee_code'] ?? null);
        return $this->repo->create($data);
    }

    public function update(int $id, array $data): Employee
    {
        $employee = $this->findOrFail($id);

        if (isset($data['employee_code']) && $data['employee_code'] !== $employee->employee_code) {
            $this->guardDuplicateCode($data['employee_code'], $id);
        }

        return $this->repo->update($employee, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repo->delete($this->findOrFail($id));
    }

    public function restore(int $id): bool
    {
        return $this->repo->restore($id);
    }

    public function getByBranch(int $branchId, bool $activeOnly = true): Collection
    {
        return $this->repo->getByBranch($branchId, $activeOnly);
    }

    public function getByRole(string $role, ?int $branchId = null): Collection
    {
        return $this->repo->getByRole($role, $branchId);
    }

    private function guardDuplicateCode(?string $code, ?int $excludeId = null): void
    {
        if (! $code) return;

        $exists = Employee::where('employee_code', $code)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->withTrashed()
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages(['employee_code' => ['Employee code already exists.']]);
        }
    }
}
