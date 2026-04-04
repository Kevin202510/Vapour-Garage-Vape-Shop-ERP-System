<?php

namespace Database\Factories;

use App\Models\ProductCategory;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $products = [
            ['name' => 'Disposable Vape 5000 Puffs', 'cost' => 180,  'price' => 350,  'uom' => 'pcs'],
            ['name' => 'Pod Mod Starter Kit',         'cost' => 650,  'price' => 1200, 'uom' => 'pcs'],
            ['name' => 'Box Mod 80W',                 'cost' => 1200, 'price' => 2200, 'uom' => 'pcs'],
            ['name' => 'Salt Nic 30mg 30ml',          'cost' => 200,  'price' => 420,  'uom' => 'bottle'],
            ['name' => 'Freebase Juice 60ml',         'cost' => 250,  'price' => 500,  'uom' => 'bottle'],
            ['name' => 'Replacement Coil 0.4ohm',     'cost' => 80,   'price' => 160,  'uom' => 'pcs'],
            ['name' => 'Replacement Pod Pack',        'cost' => 120,  'price' => 250,  'uom' => 'pack'],
            ['name' => 'Vape Battery 2500mAh',        'cost' => 350,  'price' => 650,  'uom' => 'pcs'],
            ['name' => 'Drip Tip Mouthpiece',         'cost' => 50,   'price' => 120,  'uom' => 'pcs'],
            ['name' => 'Charging Cable USB-C',        'cost' => 45,   'price' => 99,   'uom' => 'pcs'],
            ['name' => 'Cotton for RDA',              'cost' => 30,   'price' => 80,   'uom' => 'pack'],
            ['name' => 'Kanthal Wire 26G',             'cost' => 60,   'price' => 130,  'uom' => 'spool'],
        ];

        $product = fake()->randomElement($products);

    return [
    'sku' => 'VPH-' . Str::upper(Str::random(10)),

    'name' => $product['name'] . ' - ' . fake()->colorName(),
    'description' => fake()->sentence(),

    'category_id' => ProductCategory::inRandomOrder()->value('id') ?? ProductCategory::factory(),
    'supplier_id' => Supplier::inRandomOrder()->value('id') ?? Supplier::factory(),

    'unit_cost' => $product['cost'],
    'selling_price' => $product['price'],
    'tax_rate' => 12.00,
    'unit_of_measure' => $product['uom'],
    'reorder_level' => fake()->numberBetween(5, 20),

    'requires_age_verification' => true,
    'is_active' => true,
];
    }

    public function accessory(): static
    {
        return $this->state(fn () => [
            'requires_age_verification' => false,
            'unit_cost'                 => fake()->randomFloat(2, 30, 200),
            'selling_price'             => fake()->randomFloat(2, 80, 450),
        ]);
    }
}
