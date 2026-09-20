<?php

namespace App\Contracts\Repositories;

use App\Models\StockTransfer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface StockTransferRepositoryInterface extends BaseRepositoryInterface
{
    public function findById(int $id, array $relations = []): ?StockTransfer;

    /**
     * Same as findById(), but locks the transfer row (SELECT ... FOR UPDATE)
     * for the duration of the enclosing transaction — used before mutating
     * status-dependent flows (receive/approve/etc.) to prevent two concurrent
     * requests from both passing the status check and double-processing.
     */
    public function lockForUpdate(int $id, array $relations = []): ?StockTransfer;

    public function findByTransferNumber(string $number): ?StockTransfer;

    public function getByBranch(int $branchId, string $direction = 'both'): Collection;

    public function getPending(): Collection;

    public function approve(StockTransfer $transfer, int $approverId): StockTransfer;

    public function reject(StockTransfer $transfer, string $reason): StockTransfer;

    public function complete(StockTransfer $transfer): StockTransfer;

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;
}
