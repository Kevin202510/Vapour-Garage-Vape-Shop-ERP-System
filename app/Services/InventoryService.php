<?php

namespace App\Services;

use App\Contracts\Repositories\BranchInventoryRepositoryInterface;
use App\Contracts\Repositories\StockTransferRepositoryInterface;
use App\Contracts\Repositories\AuditLogRepositoryInterface;
use App\Models\BranchInventory;
use App\Models\StockTransfer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function __construct(
        protected BranchInventoryRepositoryInterface $inventoryRepo,
        protected StockTransferRepositoryInterface   $transferRepo,
        protected AuditLogRepositoryInterface        $auditRepo,
    ) {}

    public function getByBranch(int $branchId): Collection
    {
        return $this->inventoryRepo->getByBranch($branchId);
    }

    public function getLowStock(int $branchId): Collection
    {
        return $this->inventoryRepo->getLowStockByBranch($branchId);
    }

    public function adjust(int $branchId, int $productId, int $newQty, ?int $employeeId, ?string $ipAddress, ?string $reason = null): BranchInventory
    {
        return DB::transaction(function () use ($branchId, $productId, $newQty, $employeeId, $ipAddress, $reason) {
            $record  = $this->inventoryRepo->upsert($branchId, $productId, 0);
            $oldQty  = $record->quantity_on_hand;

            $record->update(['quantity_on_hand' => max(0, $newQty)]);

            $this->auditRepo->log(
                tableName:  'branch_inventory',
                recordId:   $record->id,
                action:     'update',
                oldValues:  ['quantity_on_hand' => $oldQty],
                newValues:  ['quantity_on_hand' => $newQty, 'reason' => $reason],
                employeeId: $employeeId,
                branchId:   $branchId,
                ipAddress:  $ipAddress,
            );

            return $record->fresh(['product', 'branch']);
        });
    }

    /**
     * Create a new stock transfer request and reserve stock from the source branch.
     */
    public function requestTransfer(array $data, int $requesterId): StockTransfer
    {
        return DB::transaction(function () use ($data, $requesterId) {
            // Reserve items in the source branch inventory
            foreach ($data['items'] as $item) {
                $this->inventoryRepo->reserveStock(
                    $data['from_branch_id'],
                    $item['product_id'],
                    $item['quantity_requested']
                );
            }

            $transfer = $this->transferRepo->create([
                'transfer_number' => StockTransfer::generateTransferNumber(),
                'from_branch_id'  => $data['from_branch_id'],
                'to_branch_id'    => $data['to_branch_id'],
                'requested_by'    => $requesterId,
                'status'          => 'pending',
                'notes'           => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $transfer->items()->create([
                    'product_id'         => $item['product_id'],
                    'quantity_requested' => $item['quantity_requested'],
                    'quantity_sent'      => 0,
                    'unit_cost'          => $item['unit_cost'] ?? 0,
                ]);
            }

            return $transfer->load(['fromBranch', 'toBranch', 'items.product']);
        });
    }

    /**
     * Approve a pending transfer.
     */
    public function approveTransfer(int $transferId, int $approverId): StockTransfer
    {
        $transfer = $this->transferRepo->findById($transferId)
            ?? throw new ModelNotFoundException("Transfer [{$transferId}] not found.");

        if ($transfer->status !== 'pending') {
            throw new \RuntimeException("Only pending transfers can be approved.");
        }

        return $this->transferRepo->approve($transfer, $approverId);
    }

    /**
     * Mark transfer received: deduct from source, add to destination, release reserved.
     */
    public function receiveTransfer(int $transferId): StockTransfer
    {
        return DB::transaction(function () use ($transferId) {
            $transfer = $this->transferRepo->findById($transferId, ['fromBranch', 'toBranch', 'items.product'])
                ?? throw new ModelNotFoundException("Transfer [{$transferId}] not found.");

            if (! in_array($transfer->status, ['approved', 'in_transit'])) {
                throw new \RuntimeException("Transfer must be approved or in-transit before receiving.");
            }

            foreach ($transfer->items as $item) {
                $qty = $item->quantity_requested;

                // Deduct from source + release the reservation
                $this->inventoryRepo->releaseReserved($transfer->from_branch_id, $item->product_id, $qty);
                $this->inventoryRepo->decrementStock($transfer->from_branch_id, $item->product_id, $qty);

                // Add to destination
                $this->inventoryRepo->incrementStock($transfer->to_branch_id, $item->product_id, $qty);

                $item->update(['quantity_sent' => $qty]);
            }

            return $this->transferRepo->complete($transfer);
        });
    }

    /**
     * Reject a transfer and release reserved stock.
     */
    public function rejectTransfer(int $transferId, string $reason): StockTransfer
    {
        return DB::transaction(function () use ($transferId, $reason) {
            $transfer = $this->transferRepo->findById($transferId, ['items'])
                ?? throw new ModelNotFoundException("Transfer [{$transferId}] not found.");

            if ($transfer->status !== 'pending') {
                throw new \RuntimeException("Only pending transfers can be rejected.");
            }

            foreach ($transfer->items as $item) {
                $this->inventoryRepo->releaseReserved(
                    $transfer->from_branch_id,
                    $item->product_id,
                    $item->quantity_requested
                );
            }

            return $this->transferRepo->reject($transfer, $reason);
        });
    }
}
