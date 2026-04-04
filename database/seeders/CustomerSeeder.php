<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all();

        // Seed ~30 customers spread across branches
        foreach ($branches as $branch) {
            Customer::factory()
                ->count(8)
                ->create(['registered_branch_id' => $branch->id]);

            // A couple of loyal customers per branch
            Customer::factory()
                ->count(2)
                ->loyal()
                ->create(['registered_branch_id' => $branch->id]);
        }

        $this->command->info('✔ ' . Customer::count() . ' customers seeded.');
    }
}
