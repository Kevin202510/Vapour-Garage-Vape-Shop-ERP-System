<?php

namespace App\Contracts\Repositories;

use App\Models\BranchInventory;
use Illuminate\Database\Eloquent\Collection;

interface BranchInventoryRepositoryInterface extends BaseRepositoryInterface
{
    public function findByBranchAndProduct(int $branchId, int $productId): ?BranchInventory;

    public function getByBranch(int $branchId): Collection;

    public function getLowStockByBranch(int $branchId): Collection;

    public function incrementStock(int $branchId, int $productId, int $qty): BranchInventory;

    public function decrementStock(int $branchId, int $productId, int $qty): BranchInventory;

    public function reserveStock(int $branchId, int $productId, int $qty): BranchInventory;

    public function releaseReserved(int $branchId, int $productId, int $qty): BranchInventory;

    public function upsert(int $branchId, int $productId, int $qty): BranchInventory;
}
