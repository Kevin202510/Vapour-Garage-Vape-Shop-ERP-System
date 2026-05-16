<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\OnlineOrder;
use App\Models\OnlineOrderItem;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OnlineOrderSeeder extends Seeder
{
    private array $firstNames = [
        'Juan', 'Maria', 'Jose', 'Ana', 'Carlo', 'Jessa', 'Mark', 'Lovely',
        'Paolo', 'Rhea', 'Kevin', 'Kristine', 'Angelo', 'Sheila', 'Renz',
        'Patricia', 'Joshua', 'Maricel', 'John', 'Camille', 'Alden', 'Trisha',
        'Bryan', 'Rachel', 'Aaron', 'Christine', 'Kenneth', 'Donna',
    ];

    private array $lastNames = [
        'Santos', 'Reyes', 'Cruz', 'Garcia', 'Dela Cruz', 'Gonzales', 'Torres',
        'Lopez', 'Flores', 'Ramos', 'Bautista', 'Villanueva', 'Mendoza',
        'Castillo', 'Aquino', 'Navarro', 'Diaz', 'Lim', 'Tan', 'Soriano',
        'Padilla', 'Tolentino', 'Miranda', 'Magsaysay', 'Pascual',
    ];

    private array $streets = [
        '123 Rizal St.', '45 Bonifacio Ave.', '78 Mabini Rd.', '12 Luna St.',
        '9 Del Pilar Blvd.', '34 Aguinaldo Ext.', '56 Quezon St.', '21 Laurel Ave.',
        'Block 4 Lot 7 Phase 2', 'Unit 3B Sunshine Bldg.', '88 Magsaysay St.',
        '67 San Pedro Ave.', '15 Lapu-Lapu Rd.', 'Purok 3 Sampaguita St.',
    ];

    private array $cities = [
        'General Tinio', 'Cabanatuan City', 'Gapan City', 'San Jose City',
        'Palayan City', 'Talavera', 'Guimba', 'Munoz', 'Zaragoza', 'Aliaga',
        'Bongabon', 'Cuyapo', 'Laur', 'Licab', 'Llanera',
    ];

    private array $notes = [
        null, null, null,
        'Please call before delivery.',
        'Leave at the gate if no one is home.',
        'For store pickup only, I will come in the afternoon.',
        'Gift wrap if possible.',
        'I already sent payment, please confirm.',
        'COD — please have exact change.',
    ];

    public function run(): void
    {
        $branches  = Branch::where('is_active', true)->get();
        $products  = Product::where('is_active', true)->get();
        $employees = Employee::where('is_active', true)->get();

        if ($branches->isEmpty() || $products->isEmpty()) {
            $this->command->warn('⚠ Skipping OnlineOrderSeeder — no branches or products found.');
            return;
        }

        $statuses = ['pending', 'confirmed', 'processing', 'ready', 'completed', 'completed', 'completed', 'cancelled'];
        $payments = ['gcash', 'gcash', 'maya', 'cod', 'bank_transfer'];
        $count    = 0;

        // Spread 60 orders across the last 90 days
        for ($i = 0; $i < 60; $i++) {
            $status    = fake()->randomElement($statuses);
            $orderDate = fake()->dateTimeBetween('-90 days', 'now');
            $branch    = fake()->boolean(80) ? $branches->random() : null; // 20% home delivery (no branch)
            $payment   = fake()->randomElement($payments);

            $firstName = fake()->randomElement($this->firstNames);
            $lastName  = fake()->randomElement($this->lastNames);

            // Pick 1–4 products
            $pickedProducts = $products->random(rand(1, 4));
            $subtotal = 0;
            $items    = [];

            foreach ($pickedProducts as $product) {
                $qty      = rand(1, 3);
                $price    = (float) $product->selling_price;
                $lineSub  = round($price * $qty, 2);
                $subtotal += $lineSub;

                $items[] = [
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'sku'          => $product->sku,
                    'quantity'     => $qty,
                    'unit_price'   => $price,
                    'subtotal'     => $lineSub,
                ];
            }

            $tax   = round($subtotal * 0.12, 2);
            $total = round($subtotal + $tax, 2);

            $fulfilledBy = null;
            $fulfilledAt = null;

            if ($status === 'completed' && $employees->isNotEmpty()) {
                $fulfilledBy = $employees->random()->id;
                $fulfilledAt = (clone $orderDate)->modify('+' . rand(1, 48) . ' hours');
            }

            // completed orders must have a branch (required for stock decrement)
            if ($status === 'completed' && $branch === null) {
                $branch = $branches->random();
            }

            $order = OnlineOrder::create([
                'order_number'     => 'VGM-' . strtoupper(Str::random(8)),
                'first_name'       => $firstName,
                'last_name'        => $lastName,
                'email'            => strtolower($firstName . '.' . $lastName . rand(10, 99) . '@gmail.com'),
                'phone'            => '09' . fake()->numerify('#########'),
                'address'          => fake()->randomElement($this->streets),
                'city'             => fake()->randomElement($this->cities),
                'pickup_branch_id' => $branch?->id,
                'payment_method'   => $payment,
                'notes'            => fake()->randomElement($this->notes),
                'subtotal'         => $subtotal,
                'tax_amount'       => $tax,
                'total_amount'     => $total,
                'status'           => $status,
                'payment_reference'=> in_array($payment, ['gcash', 'maya', 'bank_transfer'])
                    ? strtoupper(Str::random(12))
                    : null,
                'fulfilled_by'     => $fulfilledBy,
                'fulfilled_at'     => $fulfilledAt,
                'created_at'       => $orderDate,
                'updated_at'       => $orderDate,
            ]);

            foreach ($items as $item) {
                OnlineOrderItem::create(array_merge(
                    ['online_order_id' => $order->id],
                    $item
                ));
            }

            $count++;
        }

        $this->command->info("✔ {$count} online orders seeded.");
    }
}
