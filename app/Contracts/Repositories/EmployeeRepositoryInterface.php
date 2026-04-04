<?php

namespace App\Contracts\Repositories;

use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface EmployeeRepositoryInterface extends BaseRepositoryInterface
{
    public function findById(int $id, array $relations = []): ?Employee;

    public function findByCode(string $code): ?Employee;

    public function getByBranch(int $branchId, bool $activeOnly = true): Collection;

    public function getByRole(string $role, ?int $branchId = null): Collection;

    public function restore(int $id): bool;

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;
}
