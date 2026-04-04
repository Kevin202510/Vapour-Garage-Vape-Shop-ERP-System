<?php

namespace App\Repositories;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Models\BranchInventory;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function findById(int $id, array $relations = []): ?Product
    {
        return $this->model
            ->with($relations ?: ['category', 'supplier', 'inventory.branch'])
            ->withTrashed()
            ->find($id);
    }

    public function findBySku(string $sku): ?Product
    {
        return $this->model
            ->with(['category', 'supplier'])
            ->where('sku', $sku)
            ->first();
    }

    public function getByCategory(int $categoryId): Collection
    {
        return $this->model
            ->with(['supplier'])
            ->where('category_id', $categoryId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getBySupplier(int $supplierId): Collection
    {
        return $this->model
            ->with(['category'])
            ->where('supplier_id', $supplierId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getLowStock(int $branchId): Collection
    {
        return $this->model
            ->with(['inventory' => fn ($q) => $q->where('branch_id', $branchId)])
            ->whereHas('inventory', fn ($q) =>
                $q->where('branch_id', $branchId)
                  ->whereRaw('(quantity_on_hand - quantity_reserved) <= products.reorder_level')
            )
            ->where('is_active', true)
            ->get();
    }

    public function restore(int $id): bool
    {
        return (bool) $this->model->withTrashed()->find($id)?->restore();
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->buildQuery($filters)->paginate($perPage);
    }

    protected function buildQuery(array $filters = [])
    {
        return $this->model
            ->with(['category', 'supplier'])
            ->when(isset($filters['category_id']),  fn ($q) => $q->where('category_id', $filters['category_id']))
            ->when(isset($filters['supplier_id']),  fn ($q) => $q->where('supplier_id', $filters['supplier_id']))
            ->when(isset($filters['is_active']),    fn ($q) => $q->where('is_active', $filters['is_active']))
            ->when(isset($filters['with_trashed']), fn ($q) => $q->withTrashed())
            ->when(isset($filters['requires_age_verification']), fn ($q) =>
                $q->where('requires_age_verification', $filters['requires_age_verification'])
            )
            ->when(isset($filters['search']), fn ($q) => $q->where(fn ($s) =>
                $s->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('sku',  'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%')
            ))
            ->orderBy($filters['sort_by'] ?? 'name', $filters['sort_order'] ?? 'asc');
    }
}
