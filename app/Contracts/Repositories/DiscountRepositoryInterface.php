<?php

namespace App\Contracts\Repositories;

use App\Models\Discount;
use Illuminate\Database\Eloquent\Collection;

interface DiscountRepositoryInterface extends BaseRepositoryInterface
{
    public function findById(int $id, array $relations = []): ?Discount;

    public function getActive(): Collection;

    public function getValidNow(): Collection;
}
