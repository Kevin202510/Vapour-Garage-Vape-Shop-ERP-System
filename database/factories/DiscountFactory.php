<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DiscountFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'        => fake()->randomElement([
                'Senior Citizen Discount',
                'PWD Discount',
                'Loyalty Member Discount',
                'Bundle Deal',
                'Clearance Sale',
                'Opening Promo',
            ]),
            'type'        => fake()->randomElement(['percentage', 'fixed_amount']),
            'value'       => fake()->randomElement([5, 10, 15, 20, 50, 100]),
            'valid_from'  => now()->subDays(5),
            'valid_until' => now()->addDays(30),
            'is_active'   => true,
        ];
    }

    public function percentage(float $value = 10): static
    {
        return $this->state(fn () => [
            'type'  => 'percentage',
            'value' => $value,
        ]);
    }

    public function fixed(float $value = 50): static
    {
        return $this->state(fn () => [
            'type'  => 'fixed_amount',
            'value' => $value,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'valid_from'  => now()->subDays(60),
            'valid_until' => now()->subDays(1),
            'is_active'   => false,
        ]);
    }
}
