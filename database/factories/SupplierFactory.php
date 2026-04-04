<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    public function definition(): array
    {
        $vapeSuppliers = [
            'VapePH Distribution Inc.',
            'CloudNine Trading Co.',
            'NicVault Wholesale',
            'PuffPro Supply Chain',
            'SteamRidge Imports',
            'VoltCoil Distributors',
            'AquaVape Trading',
            'NebulaTech Supply',
        ];

        return [
            'company_name'   => fake()->randomElement($vapeSuppliers) . ' ' . fake()->randomLetter(),
            'contact_person' => fake()->name(),
            'email'          => fake()->unique()->companyEmail(),
            'phone'          => '02' . fake()->numerify('########'),
            'address'        => fake()->address(),
            'tin'            => fake()->numerify('###-###-###-000'),
            'is_active'      => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
