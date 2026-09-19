<?php

namespace App\Services;

use App\Contracts\Repositories\AuditLogRepositoryInterface;
use App\Contracts\Repositories\PermissionRepositoryInterface;
use App\Contracts\Repositories\RoleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class RoleService
{
    /**
     * Roles that are wired into the app by name — User::isSuperAdmin() checks
     * 'super_admin'/'admin' directly, and EmployeeService::ROLE_MAP assigns
     * the other four by name when an employee gets a login account. Renaming
     * or deleting any of these would silently break that logic elsewhere.
     */
    public const PROTECTED_ROLES = [
        'super_admin', 'admin', 'branch_manager', 'supervisor', 'cashier', 'inventory_clerk',
    ];

    /**
     * Mirrors the module grouping in RolesAndPermissionsSeeder so the
     * permission matrix reads the same way the seeder was authored.
     */
    private const MODULE_MAP = [
        'Users & Access'       => ['view users', 'create users', 'edit users', 'delete users', 'manage users', 'manage roles', 'manage permissions'],
        'Branches'             => ['view branches', 'create branches', 'edit branches', 'delete branches'],
        'Products & Categories'=> ['view products', 'create products', 'edit products', 'delete products', 'view categories', 'create categories', 'edit categories', 'delete categories'],
        'Inventory'            => ['view inventory', 'adjust inventory'],
        'Stock Transfers'      => ['view stock transfers', 'create stock transfers', 'approve stock transfers', 'receive stock transfers'],
        'Suppliers'            => ['view suppliers', 'create suppliers', 'edit suppliers', 'delete suppliers'],
        'Purchase Orders'      => ['view purchase orders', 'create purchase orders', 'approve purchase orders', 'receive purchase orders', 'cancel purchase orders'],
        'Sales & POS'          => ['view sales', 'create sales', 'void sales', 'apply discounts', 'view sales reports', 'close sales periods'],
        'Customers'            => ['view customers', 'create customers', 'edit customers', 'delete customers'],
        'Discounts'            => ['view discounts', 'create discounts', 'edit discounts', 'delete discounts'],
        'HR & Payroll'         => ['view employees', 'create employees', 'edit employees', 'delete employees', 'view attendance', 'manage attendance', 'view payroll', 'process payroll', 'approve payroll', 'release payroll'],
        'Reports & Analytics'  => ['view reports', 'export reports', 'view audit logs'],
    ];

    public function __construct(
        protected RoleRepositoryInterface $roleRepository,
        protected PermissionRepositoryInterface $permissionRepository,
        protected AuditLogRepositoryInterface $auditRepository,
    ) {}

    public function list(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $paginator = $this->roleRepository->paginate($perPage, $filters);

        $paginator->getCollection()->each(function (Role $role) {
            $role->user_count = $this->roleRepository->userCount($role);
        });

        return $paginator;
    }

    public function listAll(): Collection
    {
        return $this->roleRepository->all();
    }

    public function findOrFail(int $id): Role
    {
        $role = $this->roleRepository->findById($id);

        if (! $role) {
            throw new ModelNotFoundException("Role [{$id}] not found.");
        }

        $role->user_count = $this->roleRepository->userCount($role);

        return $role;
    }

    public function create(array $data, ?int $actingEmployeeId = null, ?string $ipAddress = null): Role
    {
        if ($this->roleRepository->findByName($data['name'])) {
            throw ValidationException::withMessages(['name' => ['A role with this name already exists.']]);
        }

        $role = $this->roleRepository->create($data);

        $this->auditRepository->log(
            tableName:  'roles',
            recordId:   $role->id,
            action:     'create',
            newValues:  ['name' => $role->name, 'permissions' => $role->permissions->pluck('name')->toArray()],
            employeeId: $actingEmployeeId,
            ipAddress:  $ipAddress,
        );

        return $role;
    }

    public function update(int $id, array $data, ?int $actingEmployeeId = null, ?string $ipAddress = null): Role
    {
        $role = $this->findOrFail($id);

        if (isset($data['name']) && $data['name'] !== $role->name) {
            if (in_array($role->name, self::PROTECTED_ROLES, true)) {
                throw ValidationException::withMessages(['name' => ['This role is used by name elsewhere in the app and cannot be renamed.']]);
            }
            if ($this->roleRepository->findByName($data['name'])) {
                throw ValidationException::withMessages(['name' => ['A role with this name already exists.']]);
            }
        }

        $oldPermissions = $role->permissions->pluck('name')->toArray();
        $oldName        = $role->name;

        $updated = $this->roleRepository->update($role, $data);

        $this->auditRepository->log(
            tableName:  'roles',
            recordId:   $updated->id,
            action:     'update',
            oldValues:  ['name' => $oldName, 'permissions' => $oldPermissions],
            newValues:  ['name' => $updated->name, 'permissions' => $updated->permissions->pluck('name')->toArray()],
            employeeId: $actingEmployeeId,
            ipAddress:  $ipAddress,
        );

        return $updated;
    }

    public function delete(int $id, ?int $actingEmployeeId = null, ?string $ipAddress = null): bool
    {
        $role = $this->findOrFail($id);

        if (in_array($role->name, self::PROTECTED_ROLES, true)) {
            throw ValidationException::withMessages([
                'name' => ['This is a built-in system role and cannot be deleted.'],
            ]);
        }

        if ($role->user_count > 0) {
            throw ValidationException::withMessages([
                'name' => ["This role is still assigned to {$role->user_count} user(s). Reassign them before deleting it."],
            ]);
        }

        $deleted = $this->roleRepository->delete($role);

        $this->auditRepository->log(
            tableName:  'roles',
            recordId:   $role->id,
            action:     'delete',
            oldValues:  ['name' => $role->name],
            employeeId: $actingEmployeeId,
            ipAddress:  $ipAddress,
        );

        return $deleted;
    }

    public function isProtected(string $roleName): bool
    {
        return in_array($roleName, self::PROTECTED_ROLES, true);
    }

    /**
     * All permissions bucketed by module, in the same order/grouping as the
     * seeder. Any permission not present in the map (e.g. added later without
     * updating this list) falls into a trailing "Other" bucket.
     */
    public function permissionsGroupedByModule(): \Illuminate\Support\Collection
    {
        $all     = $this->permissionRepository->all()->keyBy('name');
        $grouped = collect();

        foreach (self::MODULE_MAP as $module => $names) {
            $inModule = collect($names)->map(fn ($name) => $all->get($name))->filter()->values();
            if ($inModule->isNotEmpty()) {
                $grouped->put($module, $inModule);
            }
            $all->forget($names);
        }

        if ($all->isNotEmpty()) {
            $grouped->put('Other', $all->values());
        }

        return $grouped;
    }
}
