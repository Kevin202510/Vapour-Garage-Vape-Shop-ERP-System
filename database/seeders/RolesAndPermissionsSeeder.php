<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // -------------------------------------------------------------------------
        // Define all permissions grouped by module
        // -------------------------------------------------------------------------
        $permissions = [

            // Users & Access
            'view users',
            'create users',
            'edit users',
            'delete users',
            'manage users',       // activate / deactivate
            'manage roles',
            'manage permissions',

            // Branches
            'view branches',
            'create branches',
            'edit branches',
            'delete branches',

            // Products & Categories
            'view products',
            'create products',
            'edit products',
            'delete products',
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',

            // Inventory
            'view inventory',
            'adjust inventory',

            // Stock Transfers
            'view stock transfers',
            'create stock transfers',
            'approve stock transfers',
            'receive stock transfers',

            // Suppliers
            'view suppliers',
            'create suppliers',
            'edit suppliers',
            'delete suppliers',

            // Purchase Orders
            'view purchase orders',
            'create purchase orders',
            'approve purchase orders',
            'receive purchase orders',
            'cancel purchase orders',

            // Sales & POS
            'view sales',
            'create sales',
            'void sales',
            'apply discounts',
            'view sales reports',
            'close sales periods',

            // Customers
            'view customers',
            'create customers',
            'edit customers',
            'delete customers',

            // Discounts
            'view discounts',
            'create discounts',
            'edit discounts',
            'delete discounts',

            // HR & Payroll
            'view employees',
            'create employees',
            'edit employees',
            'delete employees',
            'view attendance',
            'manage attendance',
            'view payroll',
            'process payroll',
            'approve payroll',
            'release payroll',

            // Reports & Analytics
            'view reports',
            'export reports',
            'view audit logs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'sanctum']);
        }

        // -------------------------------------------------------------------------
        // Define roles and assign permissions
        // -------------------------------------------------------------------------

        // SUPER ADMIN — all permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'sanctum']);
        $superAdmin->syncPermissions($permissions);

        // ADMIN — full permissions across all branches (mirrors super_admin)
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'sanctum']);
        $admin->syncPermissions($permissions);

        // BRANCH MANAGER — full access within their branch
        $branchManager = Role::firstOrCreate(['name' => 'branch_manager', 'guard_name' => 'sanctum']);
        $branchManager->syncPermissions([
            'view users', 'create users', 'edit users', 'manage users',

            'view branches',

            'view products', 'create products', 'edit products',
            'view categories',

            'view inventory', 'adjust inventory',

            'view stock transfers', 'create stock transfers',
            'approve stock transfers', 'receive stock transfers',

            'view suppliers', 'create suppliers', 'edit suppliers',

            'view purchase orders', 'create purchase orders',
            'approve purchase orders', 'receive purchase orders',

            'view sales', 'create sales', 'void sales', 'apply discounts',
            'view sales reports',

            'view customers', 'create customers', 'edit customers',

            'view discounts', 'create discounts', 'edit discounts',

            // 'view employees', 'create employees', 'edit employees',
            // 'view attendance', 'manage attendance',
            // 'view payroll', 'process payroll',

            'view reports', 'export reports',
            'view audit logs',
        ]);

        // SUPERVISOR — operational access, no sensitive admin or HR financials
        $supervisor = Role::firstOrCreate(['name' => 'supervisor', 'guard_name' => 'sanctum']);
        $supervisor->syncPermissions([
            'view products', 'edit products',
            'view categories',

            'view inventory', 'adjust inventory',

            'view stock transfers', 'create stock transfers', 'receive stock transfers',

            'view suppliers',

            'view purchase orders', 'create purchase orders', 'receive purchase orders',

            'view sales', 'create sales', 'void sales', 'apply discounts',
            'view sales reports',

            'view customers', 'create customers', 'edit customers',

            'view discounts',

            // 'view employees',
            // 'view attendance', 'manage attendance',

            'view reports',
        ]);

        // CASHIER — POS only
        $cashier = Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'sanctum']);
        $cashier->syncPermissions([
            'view products',
            'view inventory',
            'view sales', 'create sales', 'apply discounts',
            'view customers', 'create customers',
            'view discounts',
        ]);

        $inventory_clerk = Role::firstOrCreate(['name' => 'inventory_clerk', 'guard_name' => 'sanctum']);
        $inventory_clerk->syncPermissions([
            'view products', 'edit products', 'create products',
            'view categories',

            'view inventory', 'adjust inventory',

            'view stock transfers', 'create stock transfers', 'receive stock transfers',

            'view suppliers',

            'view purchase orders', 'create purchase orders', 'receive purchase orders',

            'view sales', 'create sales', 'void sales', 'apply discounts',
            'view sales reports',
        ]);

        $this->command->info('✔ Roles and permissions seeded:');
        $this->command->info('  super_admin     — ' . $superAdmin->permissions->count() . ' permissions');
        $this->command->info('  admin           — ' . $admin->permissions->count() . ' permissions');
        $this->command->info('  branch_manager  — ' . $branchManager->permissions->count() . ' permissions');
        $this->command->info('  supervisor      — ' . $supervisor->permissions->count() . ' permissions');
        $this->command->info('  cashier         — ' . $cashier->permissions->count() . ' permissions');
        $this->command->info('  inventory_clerk — ' . $inventory_clerk->permissions->count() . ' permissions');
    }
}
