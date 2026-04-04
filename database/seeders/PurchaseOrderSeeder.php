<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PurchaseOrderSeeder extends Seeder
{
    public function run(): void
    {
        $branches   = Branch::all();
        $suppliers  = Supplier::where('is_active', true)->get();
        $products   = Product::where('is_active', true)->get();

        foreach ($branches as $branch) {
            $manager = Employee::where('branch_id', $branch->id)
                                ->where('role', 'branch_manager')
                                ->first();

            if (! $manager) {
                continue;
            }

            for ($i = 0; $i < 3; $i++) {
                DB::transaction(function () use ($branch, $manager, $suppliers, $products, $i) {
                    $supplier  = $suppliers->random();
                    $orderDate = now()->subDays(rand(10, 90));
                    $status    = ['received', 'received', 'approved', 'draft'][$i % 4];

                    $poNumber = 'PO-'
                        . str_pad($branch->id, 2, '0', STR_PAD_LEFT)
                        . '-' . $orderDate->format('Y')
                        . '-' . str_pad(PurchaseOrder::count() + 1, 5, '0', STR_PAD_LEFT);

                    $po = PurchaseOrder::create([
                        'po_number'     => $poNumber,
                        'supplier_id'   => $supplier->id,
                        'branch_id'     => $branch->id,
                        'created_by'    => $manager->id,
                        'approved_by'   => in_array($status, ['approved', 'received']) ? $manager->id : null,
                        'status'        => $status,
                        'total_amount'  => 0,
                        'order_date'    => $orderDate,
                        'expected_date' => (clone $orderDate)->addDays(7),
                        'received_date' => $status === 'received' ? now()->subDays(rand(1, 5)) : null,
                        'notes'         => null,
                    ]);

                    $lineProducts = $products->random(rand(2, 5));
                    $total        = 0;

                    foreach ($lineProducts as $product) {
                        $qty      = rand(10, 50);
                        $cost     = $product->unit_cost;
                        $subtotal = round($qty * $cost, 2);
                        $total   += $subtotal;

                        PurchaseOrderItem::create([
                            'po_id'              => $po->id,
                            'product_id'         => $product->id,
                            'quantity_ordered'   => $qty,
                            'quantity_received'  => $status === 'received' ? $qty : 0,
                            'unit_cost'          => $cost,
                            'subtotal'           => $subtotal,
                        ]);
                    }

                    $po->update(['total_amount' => round($total, 2)]);
                });
            }
        }

        $this->command->info('✔ ' . PurchaseOrder::count() . ' purchase orders seeded.');
    }
}
