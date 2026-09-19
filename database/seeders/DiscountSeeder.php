<?php

namespace Database\Seeders;

use App\Models\Discount;
use Illuminate\Database\Seeder;

class DiscountSeeder extends Seeder
{
    public function run(): void
    {
        $discounts = [
            [
                'name'        => 'Senior Citizen Discount',
                'type'        => 'percentage',
                'value'       => 20.00,
                'valid_from'  => null,
                'valid_until' => null,
                'is_active'   => true,
            ],
            [
                'name'        => 'PWD Discount',
                'type'        => 'percentage',
                'value'       => 20.00,
                'valid_from'  => null,
                'valid_until' => null,
                'is_active'   => true,
            ],
            [
                'name'        => 'Loyalty Silver - 5% Off',
                'type'        => 'percentage',
                'value'       => 5.00,
                'valid_from'  => now()->startOfYear(),
                'valid_until' => now()->endOfYear(),
                'is_active'   => true,
            ],
            [
                'name'        => 'Loyalty Gold - 10% Off',
                'type'        => 'percentage',
                'value'       => 10.00,
                'valid_from'  => now()->startOfYear(),
                'valid_until' => now()->endOfYear(),
                'is_active'   => true,
            ],
            [
                'name'        => 'Loyalty Platinum - 15% Off',
                'type'        => 'percentage',
                'value'       => 15.00,
                'valid_from'  => now()->startOfYear(),
                'valid_until' => now()->endOfYear(),
                'is_active'   => true,
            ],
            [
                'name'        => 'Welcome Promo - ₱50 Off',
                'type'        => 'fixed_amount',
                'value'       => 50.00,
                'valid_from'  => now(),
                'valid_until' => now()->addMonths(3),
                'is_active'   => true,
            ],
            [
                'name'        => 'Birthday Month Discount',
                'type'        => 'percentage',
                'value'       => 10.00,
                'valid_from'  => null,
                'valid_until' => null,
                'is_active'   => true,
            ],
        ];

        foreach ($discounts as $d) {
            Discount::updateOrCreate(['name' => $d['name']], $d);
        }

        $this->command->info('✔ ' . count($discounts) . ' discounts seeded.');
    }
}
