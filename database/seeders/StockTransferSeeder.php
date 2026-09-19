<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockTransferSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all();
        $products = Product::where('is_active', true)->get();

        // Create 2 transfers between different branch pairs
        $pairs = [
            [0, 1], [1, 2], [2, 3], [0, 3], [1, 3],
        ];

        if ($branches->count() < 4 || $products->isEmpty()) {
            $this->command->warn('⚠ Skipping StockTransferSeeder — fewer than 4 branches, or no products, found.');
            return;
        }

        foreach ($pairs as $index => [$fromIdx, $toIdx]) {
            DB::transaction(function () use ($branches, $products, $fromIdx, $toIdx, $index) {
                $fromBranch = $branches[$fromIdx];
                $toBranch   = $branches[$toIdx];

                $requester = Employee::where('branch_id', $fromBranch->id)
                                     ->whereIn('role', ['supervisor', 'branch_manager'])
                                     ->first();

                $approver  = Employee::where('branch_id', $fromBranch->id)
                                     ->where('role', 'branch_manager')
                                     ->first();

                if (! $requester) {
                    return;
                }

                $requestedAt = now()->subDays(rand(1, 30));
                $statuses    = ['received', 'in_transit', 'approved', 'pending', 'received'];
                $status      = $statuses[$index % count($statuses)];

                $transferNumber = 'ST-'
                    . $requestedAt->format('Ymd')
                    . '-' . str_pad(StockTransfer::count() + 1, 4, '0', STR_PAD_LEFT);

                $transfer = StockTransfer::create([
                    'transfer_number' => $transferNumber,
                    'from_branch_id'  => $fromBranch->id,
                    'to_branch_id'    => $toBranch->id,
                    'requested_by'    => $requester->id,
                    'approved_by'     => in_array($status, ['approved', 'in_transit', 'received']) ? $approver?->id : null,
                    'status'          => $status,
                    'notes'           => 'Routine stock balancing transfer.',
                    'rejection_reason'=> null,
                    'requested_at'    => $requestedAt,
                    'approved_at'     => in_array($status, ['approved', 'in_transit', 'received']) ? $requestedAt->addHours(2) : null,
                    'completed_at'    => $status === 'received' ? now()->subDays(rand(0, 3)) : null,
                ]);

                $lineProducts = $products->random(rand(2, 4));
                foreach ($lineProducts as $product) {
                    $requested = rand(5, 20);
                    StockTransferItem::create([
                        'transfer_id'        => $transfer->id,
                        'product_id'         => $product->id,
                        'quantity_requested' => $requested,
                        'quantity_sent'      => $status === 'received' ? $requested : 0,
                        'unit_cost'          => $product->unit_cost,
                    ]);
                }
            });
        }

        $this->command->info('✔ ' . StockTransfer::count() . ' stock transfers seeded.');
    }
}
