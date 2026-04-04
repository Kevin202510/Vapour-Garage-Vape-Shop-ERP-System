<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesTransactionFactory extends Factory
{
    public function definition(): array
    {
        $subtotal  = fake()->randomFloat(2, 100, 5000);
        $discount  = fake()->randomElement([0, 0, 0, 50, 100, $subtotal * 0.10]);
        $taxable   = max(0, $subtotal - $discount);
        $tax       = round($taxable * 0.12, 2);
        $total     = round($taxable + $tax, 2);
        $tendered  = $total + fake()->randomElement([0, 0, 0.50, 1, 5, 10, 20, 50, 100]);
        $branchId  = Branch::factory()->create()->id;

        return [
            'transaction_number' => 'TXN-' . str_pad($branchId, 2, '0', STR_PAD_LEFT)
                                    . '-' . now()->format('Ymd')
                                    . '-' . fake()->unique()->numerify('#####'),
            'branch_id'          => $branchId,
            'cashier_id'         => Employee::factory()->cashier()->create(['branch_id' => $branchId])->id,
            'customer_id'        => fake()->boolean(60) ? Customer::factory() : null,
            'subtotal'           => $subtotal,
            'discount_amount'    => $discount,
            'tax_amount'         => $tax,
            'total_amount'       => $total,
            'amount_tendered'    => $tendered,
            'change_amount'      => round($tendered - $total, 2),
            'payment_method'     => fake()->randomElement(['cash', 'cash', 'gcash', 'maya', 'card']),
            'status'             => 'completed',
            'void_reason'        => null,
            'transaction_date'   => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }

    public function voided(): static
    {
        return $this->state(fn () => [
            'status'      => 'voided',
            'void_reason' => fake()->randomElement([
                'Customer changed mind',
                'Wrong item scanned',
                'Duplicate transaction',
                'Payment failed',
            ]),
        ]);
    }

    public function forBranch(int $branchId, int $cashierId): static
    {
        return $this->state(fn () => [
            'branch_id'  => $branchId,
            'cashier_id' => $cashierId,
        ]);
    }
}
