<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BranchFactory extends Factory
{
    public function definition(): array
    {
        $cities = ['Quezon City', 'Makati', 'Pasig', 'Mandaluyong', 'Taguig', 'Pasay', 'Manila', 'Parañaque'];

        return [
            'name'           => 'Branch ' . fake()->unique()->bothify('??##'),
            'address'        => fake()->streetAddress(),
            'city'           => fake()->randomElement($cities),
            'contact_number' => '09' . fake()->numerify('#########'),
            'is_active'      => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
