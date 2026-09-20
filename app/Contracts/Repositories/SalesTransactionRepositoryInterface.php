<?php

namespace App\Contracts\Repositories;

use App\Models\SalesTransaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface SalesTransactionRepositoryInterface extends BaseRepositoryInterface
{
    public function findById(int $id, array $relations = []): ?SalesTransaction;

    /**
     * Same as findById(), but locks the row (SELECT ... FOR UPDATE) for the
     * duration of the enclosing transaction — used before void() so two
     * concurrent void requests can't both pass the status check.
     */
    public function lockForUpdate(int $id, array $relations = []): ?SalesTransaction;

    public function findByTransactionNumber(string $number): ?SalesTransaction;

    public function getByBranch(int $branchId, array $filters = []): Collection;

    public function getByDateRange(string $from, string $to, ?int $branchId = null): Collection;

    public function void(SalesTransaction $transaction, string $reason): SalesTransaction;

    public function getDailySummary(string $date, int $branchId): array;

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;
}
