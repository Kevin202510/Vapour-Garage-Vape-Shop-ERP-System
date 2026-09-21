<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    public function definition(): array
    {
        static $counter = 1;

        return [
            'name'        => 'Department ' . $counter++,
            'description' => fake()->sentence(),
        ];
    }
}
