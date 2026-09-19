<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Product;
use App\Models\SalesTransaction;
use App\Models\SalesTransactionItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalesTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $branches  = Branch::with('employees')->get();
        $products  = Product::where('is_active', true)->get();
        $customers = Customer::all();
        // Derived from existing count (like PurchaseOrderSeeder/StockTransferSeeder)
        // so re-running this seeder doesn't regenerate transaction_numbers that
        // already exist and violate the unique constraint.
        $total     = SalesTransaction::count();

        if ($products->isEmpty()) {
            $this->command->warn('⚠ Skipping SalesTransactionSeeder — no active products found.');
            return;
        }

        foreach ($branches as $branch) {
            $cashiers = $branch->employees->where('role', 'cashier');

            if ($cashiers->isEmpty()) {
                continue;
            }

            // Seed 30 transactions per branch
            for ($i = 0; $i < 30; $i++) {
                DB::transaction(function () use ($branch, $cashiers, $products, $customers, &$total) {
                    $cashier  = $cashiers->random();
                    $customer = fake()->boolean(60) ? $customers->random() : null;
                    $txDate   = fake()->dateTimeBetween('-3 months', 'now');

                    // Pick 1–4 random products for the transaction
                    $lineItems    = $products->random(rand(1, 4));
                    $subtotal     = 0;
                    $itemsPayload = [];

                    foreach ($lineItems as $product) {
                        $qty      = rand(1, 3);
                        $price    = $product->selling_price;
                        $taxAmt   = round($price * $qty * ($product->tax_rate / 100), 2);
                        $lineTotal= round($price * $qty, 2);
                        $subtotal += $lineTotal;

                        $itemsPayload[] = [
                            'product_id'      => $product->id,
                            'discount_id'     => null,
                            'quantity'        => $qty,
                            'unit_price'      => $price,
                            'discount_amount' => 0,
                            'tax_amount'      => $taxAmt,
                            'subtotal'        => $lineTotal,
                        ];
                    }

                    $tax      = round($subtotal * 0.12, 2);
                    $totalAmt = round($subtotal + $tax, 2);
                    $tendered = $totalAmt + fake()->randomElement([0, 0, 5, 10, 50, 100]);

                    $txnNumber = 'TXN-'
                        . str_pad($branch->id, 2, '0', STR_PAD_LEFT)
                        . '-' . date('Ymd', $txDate->getTimestamp())
                        . '-' . str_pad(++$total, 5, '0', STR_PAD_LEFT);

                    $txn = SalesTransaction::create([
                        'transaction_number' => $txnNumber,
                        'branch_id'          => $branch->id,
                        'cashier_id'         => $cashier->id,
                        'customer_id'        => $customer?->id,
                        'subtotal'           => $subtotal,
                        'discount_amount'    => 0,
                        'tax_amount'         => $tax,
                        'total_amount'       => $totalAmt,
                        'amount_tendered'    => $tendered,
                        'change_amount'      => round($tendered - $totalAmt, 2),
                        'payment_method'     => fake()->randomElement(['cash', 'cash', 'gcash', 'maya', 'card']),
                        'status'             => 'completed',
                        'transaction_date'   => $txDate,
                    ]);

                    foreach ($itemsPayload as $item) {
                        SalesTransactionItem::create(array_merge(
                            ['transaction_id' => $txn->id],
                            $item
                        ));
                    }
                });
            }
        }

        $this->command->info('✔ ' . SalesTransaction::count() . ' sales transactions seeded.');
    }
}
