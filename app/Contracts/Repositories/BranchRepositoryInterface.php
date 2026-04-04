<?php

namespace App\Contracts\Repositories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Collection;

interface BranchRepositoryInterface extends BaseRepositoryInterface
{
    public function findById(int $id, array $relations = []): ?Branch;

    public function getActive(): Collection;

    public function withEmployeeCount(): Collection;

    public function restore(int $id): bool;
}
