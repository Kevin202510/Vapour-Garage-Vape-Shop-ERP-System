<?php

namespace App\Contracts\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function findById(int $id, array $relations = []): ?User;

    public function findByEmail(string $email): ?User;

    public function create(array $data): User;

    public function update(User $user, array $data): User;

    public function delete(User $user): bool;

    public function restore(int $id): bool;

    public function assignRole(User $user, string|array $roles): User;

    public function syncRoles(User $user, string|array $roles): User;

    public function syncPermissions(User $user, string|array $permissions): User;

    public function getUsersByRole(string $role, ?int $branchId = null): Collection;

    public function updateLastLogin(User $user): void;

    public function deactivate(User $user): bool;

    public function activate(User $user): bool;
}
