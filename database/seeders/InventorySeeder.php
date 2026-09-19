<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Product;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all();
        $products = Product::all();

        foreach ($branches as $branch) {
            foreach ($products as $product) {
                BranchInventory::updateOrCreate(
                    ['branch_id' => $branch->id, 'product_id' => $product->id],
                    [
                        'quantity_on_hand'   => fake()->numberBetween(0, 100),
                        'quantity_reserved'  => 0,
                    ]
                );
            }
        }

        $this->command->info('✔ Inventory seeded for ' . $branches->count() . ' branches × ' . $products->count() . ' products.');
    }
}
