<?php

namespace App\Repositories;

use App\Contracts\Repositories\ProductCategoryRepositoryInterface;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Collection;

class ProductCategoryRepository extends BaseRepository implements ProductCategoryRepositoryInterface
{
    public function __construct(ProductCategory $model)
    {
        parent::__construct($model);
    }

    public function findById(int $id, array $relations = []): ?ProductCategory
    {
        return $this->model
            ->with($relations ?: ['parent', 'children'])
            ->find($id);
    }

    public function getRoots(): Collection
    {
        return $this->model
            ->whereNull('parent_category_id')
            ->orderBy('name')
            ->get();
    }

    public function getWithChildren(): Collection
    {
        return $this->model
            ->with(['children.children']) // 2 levels deep
            ->whereNull('parent_category_id')
            ->orderBy('name')
            ->get();
    }

    protected function buildQuery(array $filters = [])
    {
        return $this->model
            ->with(['parent', 'children'])
            ->when(isset($filters['parent_id']), fn ($q) => $q->where('parent_category_id', $filters['parent_id']))
            ->when(isset($filters['root_only']), fn ($q) => $q->whereNull('parent_category_id'))
            ->when(isset($filters['search']),    fn ($q) => $q->where('name', 'like', '%' . $filters['search'] . '%'))
            ->orderBy('name');
    }
}
