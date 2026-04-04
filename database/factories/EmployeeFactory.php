<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        static $counter = 1;

        return [
            'branch_id'       => Branch::factory(),
            'department_id'   => Department::factory(),
            'employee_code'   => 'EMP-' . str_pad($counter++, 5, '0', STR_PAD_LEFT),
            'first_name'      => fake()->firstName(),
            'last_name'       => fake()->lastName(),
            'email'           => fake()->unique()->safeEmail(),
            'phone'           => '09' . fake()->numerify('#########'),
            'role'            => fake()->randomElement(['cashier', 'supervisor', 'branch_manager']),
            'employment_type' => fake()->randomElement(['full_time', 'part_time', 'contractual']),
            'base_salary'     => fake()->randomElement([15000, 18000, 20000, 25000, 30000, 35000]),
            'hire_date'       => fake()->dateTimeBetween('-5 years', '-1 month'),
            'termination_date'=> null,
            'is_active'       => true,
        ];
    }

    public function cashier(): static
    {
        return $this->state(fn () => ['role' => 'cashier']);
    }

    public function manager(): static
    {
        return $this->state(fn () => [
            'role'        => 'branch_manager',
            'base_salary' => fake()->randomElement([35000, 40000, 45000]),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'is_active'        => false,
            'termination_date' => fake()->dateTimeBetween('-6 months', 'now'),
        ]);
    }
}
