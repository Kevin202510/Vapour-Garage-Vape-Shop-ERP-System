<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Permission;

interface PermissionRepositoryInterface
{
    public function all(): Collection;

    public function findByName(string $name): ?Permission;
}
