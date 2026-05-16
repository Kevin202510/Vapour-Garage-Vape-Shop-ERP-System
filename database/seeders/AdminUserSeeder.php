<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure roles exist before assigning
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'sanctum']);
        Role::firstOrCreate(['name' => 'admin',       'guard_name' => 'sanctum']);

        // Admin needs a branch so POS can resolve stock and sales_transactions.branch_id
        $branch     = Branch::where('is_active', true)->first();
        $department = Department::first();

        if (! $branch) {
            $this->command->warn('⚠ No active branch found. Run BranchSeeder first.');
            return;
        }

        // Create or update the employee record so cashier_id / fulfilled_by are non-null
        $employee = Employee::updateOrCreate(
            ['employee_code' => 'ADMIN-001'],
            [
                'branch_id'       => $branch->id,
                'department_id'   => $department?->id,
                'first_name'      => 'System',
                'last_name'       => 'Admin',
                'email'           => 'admin@vapourgarage.ph',
                'phone'           => '09000000000',
                'role'            => 'branch_manager',
                'employment_type' => 'full_time',
                'base_salary'     => 0,
                'hire_date'       => now()->toDateString(),
                'is_active'       => true,
            ]
        );

        // Create or update the user, linked to the employee and branch
        $admin = User::updateOrCreate(
            ['email' => 'admin@vapourgarage.ph'],
            [
                'name'              => 'System Admin',
                'password'          => Hash::make('Admin@1234'),
                'branch_id'         => $branch->id,
                'employee_id'       => $employee->id,
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );

        $admin->syncRoles(['admin']);

        $this->command->info('✔ Admin user ready:');
        $this->command->table(
            ['Field', 'Value'],
            [
                ['Email',       'admin@vapourgarage.ph'],
                ['Password',    'Admin@1234'],
                ['Role',        'admin (all permissions — full branch access)'],
                ['Branch',      $branch->name . ' (POS default; reports show all branches)'],
                ['Employee ID', $employee->employee_code],
            ]
        );
    }
}
