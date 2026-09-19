<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $branches  = Branch::all();
        $deptSales = Department::where('name', 'Sales')->first();
        $deptMgmt  = Department::where('name', 'Management')->first();
        $deptOps   = Department::where('name', 'Operations')->first();
        $deptInv   = Department::where('name', 'Inventory')->first();

        // Truncating employees resets their auto-increment IDs, so any
        // re-seed reuses IDs 1..N. attendance/payroll have unique constraints
        // on (employee_id, work_date) / (employee_id, period_start, period_end)
        // and are regenerated fresh by AttendanceAndPayrollSeeder every run —
        // truncate them here too or a second `db:seed` crashes on those
        // constraints against leftover rows from the previous run.
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Attendance::truncate();
        Payroll::truncate();
        Employee::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $seq = 1;

        $make = function (array $attrs) use (&$seq) {
            return Employee::create(array_merge([
                'employee_code'   => 'EMP-' . str_pad($seq++, 5, '0', STR_PAD_LEFT),
                'hire_date'       => now()->subMonths(rand(6, 36)),
                'employment_type' => 'full_time',
                'is_active'       => true,
            ], $attrs));
        };

        foreach ($branches as $branch) {
            $make([
                'branch_id'     => $branch->id,
                'department_id' => $deptMgmt->id,
                'first_name'    => fake()->firstName(),
                'last_name'     => fake()->lastName(),
                'email'         => fake()->unique()->safeEmail(),
                'phone'         => '09' . fake()->numerify('#########'),
                'role'          => 'branch_manager',
                'base_salary'   => 40000,
            ]);

            $make([
                'branch_id'     => $branch->id,
                'department_id' => $deptOps->id,
                'first_name'    => fake()->firstName(),
                'last_name'     => fake()->lastName(),
                'email'         => fake()->unique()->safeEmail(),
                'phone'         => '09' . fake()->numerify('#########'),
                'role'          => 'supervisor',
                'base_salary'   => 28000,
            ]);

            $make([
                'branch_id'     => $branch->id,
                'department_id' => $deptInv->id,
                'first_name'    => fake()->firstName(),
                'last_name'     => fake()->lastName(),
                'email'         => fake()->unique()->safeEmail(),
                'phone'         => '09' . fake()->numerify('#########'),
                'role'          => 'inventory_clerk',
                'base_salary'   => 18000,
            ]);

            for ($i = 0; $i < 3; $i++) {
                $make([
                    'branch_id'     => $branch->id,
                    'department_id' => $deptSales->id,
                    'first_name'    => fake()->firstName(),
                    'last_name'     => fake()->lastName(),
                    'email'         => fake()->unique()->safeEmail(),
                    'phone'         => '09' . fake()->numerify('#########'),
                    'role'          => 'cashier',
                    'base_salary'   => 16000,
                ]);
            }
        }

        $this->command->info('✔ ' . Employee::count() . ' employees seeded across ' . $branches->count() . ' branches.');
    }
}