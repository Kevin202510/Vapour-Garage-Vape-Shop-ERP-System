<?php

namespace App\Repositories;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(protected User $model) {}

    // -------------------------------------------------------------------------
    // Read
    // -------------------------------------------------------------------------

    public function all(array $filters = []): Collection
    {
        return $this->buildQuery($filters)->get();
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->buildQuery($filters)->paginate($perPage);
    }

    public function findById(int $id, array $relations = []): ?User
    {
        return $this->model
            ->with($relations)
            ->withTrashed()
            ->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function getUsersByRole(string $role, ?int $branchId = null): Collection
    {
        return $this->model
            ->with(['roles', 'permissions', 'branch', 'employee'])
            ->role($role)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->active()
            ->get();
    }

    // -------------------------------------------------------------------------
    // Write
    // -------------------------------------------------------------------------

    public function create(array $data): User
    {
        // Role/permission keys are deliberately ignored here — see
        // syncRoles()/syncPermissions() below, the only sanctioned path for
        // granting access, gated separately from basic profile create/update.
        $user = $this->model->create(
            collect($data)->except(['roles', 'permissions'])->toArray()
        );

        return $user->load(['roles', 'permissions', 'branch', 'employee']);
    }

    public function update(User $user, array $data): User
    {
        $user->update(
            collect($data)->except(['roles', 'permissions'])->toArray()
        );

        return $user->fresh(['roles', 'permissions', 'branch', 'employee']);
    }

    public function delete(User $user): bool
    {
        return (bool) $user->delete();
    }

    public function restore(int $id): bool
    {
        return (bool) $this->model->withTrashed()->find($id)?->restore();
    }

    // -------------------------------------------------------------------------
    // Role / Permission management
    // -------------------------------------------------------------------------

    public function assignRole(User $user, string|array $roles): User
    {
        $user->assignRole($roles);
        return $user->fresh(['roles', 'permissions']);
    }

    public function syncRoles(User $user, string|array $roles): User
    {
        $user->syncRoles($roles);
        return $user->fresh(['roles', 'permissions']);
    }

    public function syncPermissions(User $user, string|array $permissions): User
    {
        $user->syncPermissions($permissions);
        return $user->fresh(['roles', 'permissions']);
    }

    // -------------------------------------------------------------------------
    // Status helpers
    // -------------------------------------------------------------------------

    public function updateLastLogin(User $user): void
    {
        $user->update(['last_login_at' => now()]);
    }

    public function deactivate(User $user): bool
    {
        // Revoke all Sanctum tokens on deactivation
        $user->tokens()->delete();
        return (bool) $user->update(['is_active' => false]);
    }

    public function activate(User $user): bool
    {
        return (bool) $user->update(['is_active' => true]);
    }

    // -------------------------------------------------------------------------
    // Internal query builder
    // -------------------------------------------------------------------------

    private function buildQuery(array $filters = [])
    {
        $query = $this->model
            ->with(['roles', 'permissions', 'branch', 'employee'])
            ->when(isset($filters['branch_id']),    fn ($q) => $q->where('branch_id', $filters['branch_id']))
            ->when(isset($filters['role']),         fn ($q) => $q->role($filters['role']))
            ->when(isset($filters['is_active']),    fn ($q) => $q->where('is_active', $filters['is_active']))
            ->when(isset($filters['with_trashed']), fn ($q) => $q->withTrashed())
            ->when(isset($filters['search']), function ($q) use ($filters) {
                $term = '%' . $filters['search'] . '%';
                $q->where(fn ($s) => $s->where('name', 'like', $term)
                                       ->orWhere('email', 'like', $term));
            });

        $sortField     = $filters['sort_by']    ?? 'created_at';
        $sortDirection = $filters['sort_order'] ?? 'desc';
        $allowedSorts  = ['name', 'email', 'created_at', 'last_login_at'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        }

        return $query;
    }
}
