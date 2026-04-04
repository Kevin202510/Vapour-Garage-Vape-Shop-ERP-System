<?php

namespace App\Contracts\Repositories;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    public function findById(int $id, array $relations = []): ?Product;

    public function findBySku(string $sku): ?Product;

    public function getByCategory(int $categoryId): Collection;

    public function getBySupplier(int $supplierId): Collection;

    public function getLowStock(int $branchId): Collection;

    public function restore(int $id): bool;

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;
}
