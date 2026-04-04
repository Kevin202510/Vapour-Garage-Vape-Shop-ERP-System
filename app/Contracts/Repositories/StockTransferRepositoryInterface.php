<?php

namespace App\Contracts\Repositories;

use App\Models\StockTransfer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface StockTransferRepositoryInterface extends BaseRepositoryInterface
{
    public function findById(int $id, array $relations = []): ?StockTransfer;

    public function findByTransferNumber(string $number): ?StockTransfer;

    public function getByBranch(int $branchId, string $direction = 'both'): Collection;

    public function getPending(): Collection;

    public function approve(StockTransfer $transfer, int $approverId): StockTransfer;

    public function reject(StockTransfer $transfer, string $reason): StockTransfer;

    public function complete(StockTransfer $transfer): StockTransfer;

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;
}
