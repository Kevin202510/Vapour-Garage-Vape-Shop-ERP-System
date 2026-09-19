<?php

namespace App\Services;

use App\Contracts\Repositories\PermissionRepositoryInterface;
use Illuminate\Support\Collection;

class PermissionService
{
    public function __construct(
        protected PermissionRepositoryInterface $permissionRepository,
        protected RoleService $roleService,
    ) {}

    /**
     * All permissions grouped by module, each annotated with the role names
     * that currently include it — so the page can answer "who can do this?"
     * at a glance.
     */
    public function groupedWithRoles(): Collection
    {
        return $this->roleService->permissionsGroupedByModule()->map(
            fn (Collection $permissions) => $permissions->map(function ($permission) {
                $permission->role_names = $permission->roles->pluck('name');

                return $permission;
            })
        );
    }
}
