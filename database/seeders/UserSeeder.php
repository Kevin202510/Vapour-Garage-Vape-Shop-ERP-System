<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all();

        // ── Super Admin (no branch, no employee) ──────────────────────────────
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@vapehub.ph'],
            [
                'name'      => 'Super Admin',
                'password'  => Hash::make('Password@1'),
                'branch_id' => null,
                'is_active' => true,
            ]
        );
        $superAdmin->syncRoles(['super_admin']);

        // ── One Branch Manager per branch ─────────────────────────────────────
        // foreach ($branches as $branch) {
        //     $employee = Employee::where('branch_id', $branch->id)
        //                         ->where('role', 'branch_manager')
        //                         ->first();

        //     $slug = Str::of($branch->city)
        //             ->ascii()
        //             ->lower()
        //             ->replace([' ', '-'], '')
        //             ->toString();

        //     $manager = User::updateOrCreate(
        //         ['email' => "manager.{$slug}@vapehub.ph"],
        //         [
        //             'name'        => $employee?->full_name ?? "Manager {$branch->name}",
        //             'password'    => Hash::make('Password@1'),
        //             'branch_id'   => $branch->id,
        //             'employee_id' => $employee?->id,
        //             'is_active'   => true,
        //         ]
        //     );
        //     $manager->syncRoles(['branch_manager']);

        //     // ── One Supervisor per branch ─────────────────────────────────────
        //     $supervisor = Employee::where('branch_id', $branch->id)
        //                           ->where('role', 'supervisor')
        //                           ->first();

        //     $sup = User::updateOrCreate(
        //         ['email' => "supervisor.{$slug}@vapehub.ph"],
        //         [
        //             'name'        => $supervisor?->full_name ?? "Supervisor {$branch->name}",
        //             'password'    => Hash::make('Password@1'),
        //             'branch_id'   => $branch->id,
        //             'employee_id' => $supervisor?->id,
        //             'is_active'   => true,
        //         ]
        //     );
        //     $sup->syncRoles(['supervisor']);

        //     // ── One Cashier per branch ────────────────────────────────────────
        //     $cashierEmployee = Employee::where('branch_id', $branch->id)
        //                                ->where('role', 'cashier')
        //                                ->first();

        //     $cashier = User::updateOrCreate(
        //         ['email' => "cashier.{$slug}@vapehub.ph"],
        //         [
        //             'name'        => $cashierEmployee?->full_name ?? "Cashier {$branch->name}",
        //             'password'    => Hash::make('Password@1'),
        //             'branch_id'   => $branch->id,
        //             'employee_id' => $cashierEmployee?->id,
        //             'is_active'   => true,
        //         ]
        //     );
        //     $cashier->syncRoles(['cashier']);

        //     // ── One Inventory Clerk per branch ────────────────────────────────────────
        //     $inventoryClerkEmployee = Employee::where('branch_id', $branch->id)
        //                                ->where('role', 'inventory_clerk')
        //                                ->first();

        //     $inventory_clerk = User::updateOrCreate(
        //         ['email' => "inventoryclerk.{$slug}@vapehub.ph"],
        //         [
        //             'name'        => $inventoryClerkEmployee?->full_name ?? "Inventory Clerk {$branch->name}",
        //             'password'    => Hash::make('Password@1'),
        //             'branch_id'   => $branch->id,
        //             'employee_id' => $inventoryClerkEmployee?->id,
        //             'is_active'   => true,
        //         ]
        //     );
        //     $inventory_clerk->syncRoles(['inventory_clerk']);
        // }

        $this->command->info('✔ ' . User::count() . ' users seeded.');
        $this->command->newLine();
        $this->command->table(
            ['Email', 'Role', 'Password'],
            User::with('roles')->get()->map(fn ($u) => [
                $u->email,
                $u->roles->pluck('name')->implode(', '),
                'Password@1',
            ])->toArray()
        );
    }
}
