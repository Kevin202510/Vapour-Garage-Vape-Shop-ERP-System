<?php

namespace App\Services;

use App\Contracts\Repositories\BranchInventoryRepositoryInterface;
use App\Contracts\Repositories\PurchaseOrderRepositoryInterface;
use App\Models\PurchaseOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PurchaseOrderService
{
    public function __construct(
        protected PurchaseOrderRepositoryInterface   $poRepo,
        protected BranchInventoryRepositoryInterface $inventoryRepo,
    ) {}

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->poRepo->paginate($perPage, $filters);
    }

    public function findOrFail(int $id): PurchaseOrder
    {
        return $this->poRepo->findById($id)
            ?? throw new ModelNotFoundException("Purchase order [{$id}] not found.");
    }

    public function create(array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'] ?? [];
            unset($data['items']);

            $total = collect($items)->sum(fn ($i) => $i['unit_cost'] * $i['quantity_ordered']);

            $po = $this->poRepo->create(array_merge($data, [
                'po_number'    => PurchaseOrder::generatePoNumber($data['branch_id']),
                'total_amount' => round($total, 2),
                'status'       => 'draft',
            ]));

            foreach ($items as $item) {
                $po->items()->create([
                    'product_id'        => $item['product_id'],
                    'quantity_ordered'  => $item['quantity_ordered'],
                    'quantity_received' => 0,
                    'unit_cost'         => $item['unit_cost'],
                    'subtotal'          => round($item['unit_cost'] * $item['quantity_ordered'], 2),
                ]);
            }

            return $po->load(['supplier', 'branch', 'items.product']);
        });
    }

    public function approve(int $id, int $approverId): PurchaseOrder
    {
        $po = $this->findOrFail($id);

        if ($po->status !== 'draft') {
            throw new RuntimeException("Only draft purchase orders can be approved.");
        }

        return $this->poRepo->approve($po, $approverId);
    }

    /**
     * Receive items and update branch inventory.
     *
     * $receivedItems = [['product_id' => int, 'quantity_received' => int], ...]
     */
    public function receive(int $id, array $receivedItems): PurchaseOrder
    {
        $po = $this->findOrFail($id);

        if (! in_array($po->status, ['approved', 'sent', 'partial'])) {
            throw new RuntimeException("Purchase order must be approved or sent before receiving.");
        }

        return DB::transaction(function () use ($po, $receivedItems) {
            // Lock the PO's item rows for the rest of this transaction so a
            // concurrent receive() call can't validate against the same stale
            // "remaining" quantity and over-receive past what was ordered.
            $lockedItems = $this->poRepo->lockItemsForUpdate($po->id)->keyBy('product_id');

            foreach ($receivedItems as $item) {
                $qty = (int) ($item['quantity_received'] ?? 0);
                if ($qty <= 0) continue;

                // Guard: cannot receive more than what remains
                $poItem = $lockedItems->get($item['product_id']);
                if ($poItem) {
                    $remaining = $poItem->quantity_ordered - $poItem->quantity_received;
                    if ($qty > $remaining) {
                        $name = $poItem->product->name ?? "Product #{$item['product_id']}";
                        throw new RuntimeException(
                            "Cannot receive {$qty} units of \"{$name}\" — only {$remaining} remaining."
                        );
                    }
                }

                $this->poRepo->incrementReceivedQuantity($po->id, $item['product_id'], $qty);
                $this->inventoryRepo->incrementStock($po->branch_id, $item['product_id'], $qty);
            }

            $freshItems  = $this->poRepo->lockItemsForUpdate($po->id);
            $allReceived = $freshItems->every(fn ($i) => $i->quantity_received >= $i->quantity_ordered);
            $anyReceived = $freshItems->some(fn ($i)  => $i->quantity_received > 0);

            return $this->poRepo->updateStatus(
                $po,
                $allReceived ? 'received' : ($anyReceived ? 'partial' : $po->status),
                $allReceived ? now()->toDateString() : null
            );
        });
    }

    public function cancel(int $id): PurchaseOrder
    {
        $po = $this->findOrFail($id);

        if (in_array($po->status, ['received', 'closed', 'cancelled'])) {
            throw new RuntimeException("Cannot cancel a {$po->status} purchase order.");
        }

        return $this->poRepo->cancel($po);
    }

    public function delete(int $id): bool
    {
        $po = $this->findOrFail($id);

        if ($po->status !== 'draft') {
            throw new RuntimeException("Only draft purchase orders can be deleted.");
        }

        return $this->poRepo->delete($po);
    }
}
