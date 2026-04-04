<?php

namespace App\Contracts\Repositories;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Collection;

interface ProductCategoryRepositoryInterface extends BaseRepositoryInterface
{
    public function findById(int $id, array $relations = []): ?ProductCategory;

    public function getRoots(): Collection;

    public function getWithChildren(): Collection;
}
