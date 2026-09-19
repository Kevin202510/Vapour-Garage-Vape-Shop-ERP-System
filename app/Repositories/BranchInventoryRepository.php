<?php

namespace App\Repositories;

use App\Contracts\Repositories\BranchInventoryRepositoryInterface;
use App\Models\BranchInventory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BranchInventoryRepository extends BaseRepository implements BranchInventoryRepositoryInterface
{
    public function __construct(BranchInventory $model)
    {
        parent::__construct($model);
    }

    public function findByBranchAndProduct(int $branchId, int $productId): ?BranchInventory
    {
        return $this->model
            ->with(['branch', 'product'])
            ->where('branch_id', $branchId)
            ->where('product_id', $productId)
            ->first();
    }

    public function getByBranch(int $branchId): Collection
    {
        return $this->model
            ->with(['product.category', 'product.supplier'])
            ->where('branch_id', $branchId)
            ->orderBy('product_id')
            ->get();
    }

    public function getLowStockByBranch(int $branchId): Collection
    {
        return $this->model
            ->with(['product'])
            ->where('branch_id', $branchId)
            ->whereHas('product', fn ($q) => $q->where('is_active', true))
            ->whereRaw('(quantity_on_hand - quantity_reserved) <= (SELECT reorder_level FROM products WHERE id = branch_inventory.product_id)')
            ->get();
    }

    public function incrementStock(int $branchId, int $productId, int $qty): BranchInventory
    {
        $record = $this->upsert($branchId, $productId, 0);

        $record->increment('quantity_on_hand', $qty);
        $record->touch('last_updated');

        return $record->fresh(['product', 'branch']);
    }

    public function decrementStock(int $branchId, int $productId, int $qty): BranchInventory
    {
        // Locked read: must run inside a DB::transaction() so the row lock is
        // held until commit, preventing two concurrent sales/transfers from
        // both reading stale availability and overselling.
        $record = $this->lockForUpdate($branchId, $productId);

        if (! $record || $record->available_quantity < $qty) {
            throw new RuntimeException(
                "Insufficient stock for product #{$productId} at branch #{$branchId}. " .
                "Available: " . ($record?->available_quantity ?? 0) . ", Requested: {$qty}."
            );
        }

        $record->decrement('quantity_on_hand', $qty);
        $record->touch('last_updated');

        return $record->fresh(['product', 'branch']);
    }

    public function reserveStock(int $branchId, int $productId, int $qty): BranchInventory
    {
        $record = $this->lockForUpdate($branchId, $productId);

        if (! $record || $record->available_quantity < $qty) {
            throw new RuntimeException(
                "Cannot reserve {$qty} units for product #{$productId} at branch #{$branchId}. " .
                "Available: " . ($record?->available_quantity ?? 0) . "."
            );
        }

        $record->increment('quantity_reserved', $qty);
        $record->touch('last_updated');

        return $record->fresh();
    }

    public function releaseReserved(int $branchId, int $productId, int $qty): BranchInventory
    {
        $record = $this->lockForUpdate($branchId, $productId);

        if (! $record) {
            throw new RuntimeException("Inventory record not found for product #{$productId} at branch #{$branchId}.");
        }

        $release = min($qty, $record->quantity_reserved);
        $record->decrement('quantity_reserved', $release);
        $record->touch('last_updated');

        return $record->fresh();
    }

    /**
     * Read the inventory row with a row-level lock. Must be called from
     * inside a DB::transaction() so the lock is actually held.
     */
    protected function lockForUpdate(int $branchId, int $productId): ?BranchInventory
    {
        return $this->model
            ->where('branch_id', $branchId)
            ->where('product_id', $productId)
            ->lockForUpdate()
            ->first();
    }

    public function upsert(int $branchId, int $productId, int $qty): BranchInventory
    {
        return $this->model->firstOrCreate(
            ['branch_id' => $branchId, 'product_id' => $productId],
            ['quantity_on_hand' => $qty, 'quantity_reserved' => 0]
        );
    }

    protected function buildQuery(array $filters = [])
    {
        return $this->model
            ->with(['product.category', 'branch'])
            ->when(isset($filters['branch_id']),  fn ($q) => $q->where('branch_id', $filters['branch_id']))
            ->when(isset($filters['product_id']), fn ($q) => $q->where('product_id', $filters['product_id']))
            ->when(isset($filters['low_stock']),  fn ($q) =>
                $q->whereRaw('(quantity_on_hand - quantity_reserved) <= (SELECT reorder_level FROM products WHERE id = branch_inventory.product_id)')
            );
    }
}
