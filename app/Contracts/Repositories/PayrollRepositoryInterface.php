<?php

namespace App\Contracts\Repositories;

use App\Models\Payroll;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PayrollRepositoryInterface extends BaseRepositoryInterface
{
    public function findById(int $id, array $relations = []): ?Payroll;

    public function getByEmployee(int $employeeId): Collection;

    public function getByBranchAndPeriod(int $branchId, string $periodStart, string $periodEnd): Collection;

    public function approve(Payroll $payroll, int $processedBy): Payroll;

    public function release(Payroll $payroll): Payroll;

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;
}
