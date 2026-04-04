<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'               => fake()->unique()->word(),
            'description'        => fake()->sentence(),
            'parent_category_id' => null,
        ];
    }

    public function child(int $parentId): static
    {
        return $this->state(fn () => ['parent_category_id' => $parentId]);
    }
}
