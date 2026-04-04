<?php

namespace App\Contracts\Repositories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;

interface DepartmentRepositoryInterface extends BaseRepositoryInterface
{
    public function findById(int $id, array $relations = []): ?Department;

    public function allWithEmployeeCount(): Collection;
}
