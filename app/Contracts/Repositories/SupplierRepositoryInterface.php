<?php

namespace App\Contracts\Repositories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;

interface SupplierRepositoryInterface extends BaseRepositoryInterface
{
    public function findById(int $id, array $relations = []): ?Supplier;

    public function getActive(): Collection;

    public function restore(int $id): bool;
}
