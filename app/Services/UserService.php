<?php

namespace App\Services;

use App\Contracts\Repositories\AuditLogRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected AuditLogRepositoryInterface $auditRepository,
    ) {}

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->paginate($perPage, $filters);
    }

    public function findOrFail(int $id): User
    {
        $user = $this->userRepository->findById($id, ['roles', 'permissions', 'branch', 'employee']);

        if (! $user) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("User [{$id}] not found.");
        }

        return $user;
    }

    /**
     * Create a new user with optional roles and permissions.
     *
     * @throws ValidationException
     */
    public function create(array $data): User
    {
        $this->guardDuplicateEmail($data['email']);

        $data['password'] = Hash::make($data['password']);

        return $this->userRepository->create($data);
    }

    /**
     * Update user data. Password is only changed if explicitly provided.
     */
    public function update(int $id, array $data): User
    {
        $user = $this->findOrFail($id);

        if (isset($data['email']) && $data['email'] !== $user->email) {
            $this->guardDuplicateEmail($data['email'], $id);
        }

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->userRepository->update($user, $data);
    }

    public function delete(int $id): bool
    {
        $user = $this->findOrFail($id);
        return $this->userRepository->delete($user);
    }

    public function restore(int $id): bool
    {
        return $this->userRepository->restore($id);
    }

    public function assignRole(int $id, string|array $roles): User
    {
        $user = $this->findOrFail($id);
        return $this->userRepository->assignRole($user, $roles);
    }

    public function syncRoles(int $id, string|array $roles, ?int $actingEmployeeId = null, ?string $ipAddress = null): User
    {
        $user       = $this->findOrFail($id);
        $oldRoles   = $user->roles->pluck('name')->toArray();
        $updated    = $this->userRepository->syncRoles($user, $roles);

        $this->auditRepository->log(
            tableName:  'users',
            recordId:   $user->id,
            action:     'update',
            oldValues:  ['roles' => $oldRoles],
            newValues:  ['roles' => $updated->roles->pluck('name')->toArray()],
            employeeId: $actingEmployeeId,
            branchId:   $user->branch_id,
            ipAddress:  $ipAddress,
        );

        return $updated;
    }

    public function syncPermissions(int $id, string|array $permissions, ?int $actingEmployeeId = null, ?string $ipAddress = null): User
    {
        $user        = $this->findOrFail($id);
        $oldPermissions = $user->permissions->pluck('name')->toArray();
        $updated     = $this->userRepository->syncPermissions($user, $permissions);

        $this->auditRepository->log(
            tableName:  'users',
            recordId:   $user->id,
            action:     'update',
            oldValues:  ['permissions' => $oldPermissions],
            newValues:  ['permissions' => $updated->permissions->pluck('name')->toArray()],
            employeeId: $actingEmployeeId,
            branchId:   $user->branch_id,
            ipAddress:  $ipAddress,
        );

        return $updated;
    }

    public function deactivate(int $id): bool
    {
        $user = $this->findOrFail($id);
        return $this->userRepository->deactivate($user);
    }

    public function activate(int $id): bool
    {
        $user = $this->findOrFail($id);
        return $this->userRepository->activate($user);
    }

    public function getUsersByRole(string $role, ?int $branchId = null): Collection
    {
        return $this->userRepository->getUsersByRole($role, $branchId);
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    /**
     * @throws ValidationException
     */
    private function guardDuplicateEmail(string $email, ?int $excludeId = null): void
    {
        $exists = User::where('email', $email)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->withTrashed()
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'email' => ['This email address is already taken.'],
            ]);
        }
    }
}
