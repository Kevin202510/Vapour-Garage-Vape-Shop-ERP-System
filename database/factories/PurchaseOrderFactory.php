<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderFactory extends Factory
{
    public function definition(): array
    {
        $branchId  = Branch::factory()->create()->id;
        $creatorId = Employee::factory()->create(['branch_id' => $branchId])->id;
        $orderDate = fake()->dateTimeBetween('-3 months', 'now');

        return [
            'po_number'     => 'PO-' . str_pad($branchId, 2, '0', STR_PAD_LEFT)
                               . '-' . date('Y', $orderDate->getTimestamp())
                               . '-' . fake()->unique()->numerify('#####'),
            'supplier_id'   => Supplier::factory(),
            'branch_id'     => $branchId,
            'created_by'    => $creatorId,
            'approved_by'   => null,
            'status'        => 'draft',
            'total_amount'  => fake()->randomFloat(2, 1000, 50000),
            'order_date'    => $orderDate,
            'expected_date' => fake()->dateTimeBetween($orderDate, '+1 month'),
            'received_date' => null,
            'notes'         => fake()->optional(0.3)->sentence(),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status'      => 'approved',
            'approved_by' => Employee::factory()->manager(),
        ]);
    }

    public function received(): static
    {
        return $this->state(fn () => [
            'status'        => 'received',
            'received_date' => now(),
        ]);
    }
}
