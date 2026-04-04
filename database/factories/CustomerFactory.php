<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'registered_branch_id' => Branch::factory(),
            'first_name'           => fake()->firstName(),
            'last_name'            => fake()->lastName(),
            'email'                => fake()->unique()->safeEmail(),
            'phone'                => '09' . fake()->numerify('#########'),
            'birth_date'           => fake()->dateTimeBetween('-50 years', '-18 years'),
            'loyalty_points'       => fake()->numberBetween(0, 5000),
            'tier'                 => fake()->randomElement(['bronze', 'silver', 'gold', 'platinum']),
        ];
    }

    public function walkIn(): static
    {
        return $this->state(fn () => [
            'email'          => null,
            'phone'          => null,
            'loyalty_points' => 0,
            'tier'           => 'bronze',
        ]);
    }

    public function loyal(): static
    {
        return $this->state(fn () => [
            'loyalty_points' => fake()->numberBetween(3000, 10000),
            'tier'           => fake()->randomElement(['gold', 'platinum']),
        ]);
    }
}
