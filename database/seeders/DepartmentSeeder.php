<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Sales',          'description' => 'POS cashiers and floor sales staff'],
            ['name' => 'Operations',     'description' => 'Branch operations and logistics'],
            ['name' => 'Inventory',      'description' => 'Stock management and receiving'],
            ['name' => 'HR & Payroll',   'description' => 'Human resources and employee compensation'],
            ['name' => 'Finance',        'description' => 'Accounting, billing, and reporting'],
            ['name' => 'Management',     'description' => 'Branch managers and supervisors'],
        ];

        foreach ($departments as $dept) {
            Department::create($dept);
        }

        $this->command->info('✔ Departments seeded.');
    }
}
