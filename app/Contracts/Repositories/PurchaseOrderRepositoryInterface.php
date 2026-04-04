<?php

namespace App\Contracts\Repositories;

use App\Models\PurchaseOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PurchaseOrderRepositoryInterface extends BaseRepositoryInterface
{
    public function findById(int $id, array $relations = []): ?PurchaseOrder;

    public function findByPoNumber(string $poNumber): ?PurchaseOrder;

    public function getByBranch(int $branchId, array $filters = []): Collection;

    public function getBySupplier(int $supplierId): Collection;

    public function getPending(): Collection;

    public function approve(PurchaseOrder $po, int $approverId): PurchaseOrder;

    public function receive(PurchaseOrder $po, array $receivedItems): PurchaseOrder;

    public function cancel(PurchaseOrder $po): PurchaseOrder;

    public function restore(int $id): bool;

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;
}
