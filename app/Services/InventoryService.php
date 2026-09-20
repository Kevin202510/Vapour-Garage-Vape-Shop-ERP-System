<?php

namespace App\Services;

use App\Concerns\EnforcesBranchAccess;
use App\Contracts\Repositories\BranchInventoryRepositoryInterface;
use App\Contracts\Repositories\StockTransferRepositoryInterface;
use App\Contracts\Repositories\AuditLogRepositoryInterface;
use App\Models\BranchInventory;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    use EnforcesBranchAccess;

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

            // Pre-load product unit costs to avoid N+1
            $productIds = collect($data['items'])->pluck('product_id')->unique();
            $costs      = Product::whereIn('id', $productIds)->pluck('unit_cost', 'id');

            foreach ($data['items'] as $item) {
                $transfer->items()->create([
                    'product_id'         => $item['product_id'],
                    'quantity_requested' => $item['quantity_requested'],
                    'quantity_sent'      => 0,
                    'unit_cost'          => $costs[$item['product_id']] ?? 0,
                ]);
            }

            return $transfer->load(['fromBranch', 'toBranch', 'items.product']);
        });
    }

    /**
     * Approve a pending transfer. Either branch endpoint may approve.
     */
    public function approveTransfer(int $transferId, int $approverId, User $actor): StockTransfer
    {
        $transfer = $this->transferRepo->findById($transferId)
            ?? throw new ModelNotFoundException("Transfer [{$transferId}] not found.");

        $this->assertBranchAccess($actor, $transfer->from_branch_id, $transfer->to_branch_id);

        if ($transfer->status !== 'pending') {
            throw new \RuntimeException("Only pending transfers can be approved.");
        }

        $approved = $this->transferRepo->approve($transfer, $approverId);

        $this->auditRepo->log(
            tableName:  'stock_transfers',
            recordId:   $transfer->id,
            action:     'approve',
            oldValues:  ['status' => 'pending'],
            newValues:  ['status' => 'approved'],
            employeeId: $approverId,
            branchId:   $transfer->to_branch_id,
        );

        return $approved;
    }

    /**
     * Mark transfer received: deduct from source, add to destination, release reserved.
     * Only the destination branch may receive. The transfer row is locked for
     * the duration of the transaction so two concurrent "Receive" requests
     * can't both pass the status check and double-process the same transfer.
     */
    public function receiveTransfer(int $transferId, User $actor): StockTransfer
    {
        return DB::transaction(function () use ($transferId, $actor) {
            $transfer = $this->transferRepo->lockForUpdate($transferId, ['fromBranch', 'toBranch', 'items.product'])
                ?? throw new ModelNotFoundException("Transfer [{$transferId}] not found.");

            $this->assertBranchAccess($actor, $transfer->to_branch_id);

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

            $completed = $this->transferRepo->complete($transfer);

            $this->auditRepo->log(
                tableName:  'stock_transfers',
                recordId:   $transfer->id,
                action:     'receive',
                oldValues:  ['status' => $transfer->status],
                newValues:  ['status' => 'received'],
                employeeId: $actor->employee_id,
                branchId:   $transfer->to_branch_id,
            );

            return $completed;
        });
    }

    /**
     * Mark an approved transfer as dispatched (in transit). Source branch only.
     */
    public function dispatchTransfer(int $transferId, User $actor): StockTransfer
    {
        $transfer = $this->transferRepo->findById($transferId)
            ?? throw new ModelNotFoundException("Transfer [{$transferId}] not found.");

        $this->assertBranchAccess($actor, $transfer->from_branch_id);

        if ($transfer->status !== 'approved') {
            throw new \RuntimeException("Only approved transfers can be marked as dispatched.");
        }

        $transfer->update(['status' => 'in_transit']);

        $this->auditRepo->log(
            tableName:  'stock_transfers',
            recordId:   $transfer->id,
            action:     'dispatch',
            oldValues:  ['status' => 'approved'],
            newValues:  ['status' => 'in_transit'],
            employeeId: $actor->employee_id,
            branchId:   $transfer->from_branch_id,
        );

        return $transfer->fresh(['fromBranch', 'toBranch']);
    }

    /**
     * Cancel a pending or approved transfer and release reserved stock.
     * Either branch endpoint may cancel.
     */
    public function cancelTransfer(int $transferId, User $actor): StockTransfer
    {
        return DB::transaction(function () use ($transferId, $actor) {
            $transfer = $this->transferRepo->findById($transferId, ['items'])
                ?? throw new ModelNotFoundException("Transfer [{$transferId}] not found.");

            $this->assertBranchAccess($actor, $transfer->from_branch_id, $transfer->to_branch_id);

            if (! in_array($transfer->status, ['pending', 'approved'])) {
                throw new \RuntimeException("Only pending or approved transfers can be cancelled.");
            }

            foreach ($transfer->items as $item) {
                $this->inventoryRepo->releaseReserved(
                    $transfer->from_branch_id,
                    $item->product_id,
                    $item->quantity_requested
                );
            }

            $transfer->update(['status' => 'cancelled']);

            $this->auditRepo->log(
                tableName:  'stock_transfers',
                recordId:   $transfer->id,
                action:     'cancel',
                oldValues:  ['status' => $transfer->status],
                newValues:  ['status' => 'cancelled'],
                employeeId: $actor->employee_id,
                branchId:   $transfer->from_branch_id,
            );

            return $transfer->fresh(['fromBranch', 'toBranch']);
        });
    }

    /**
     * Reject a transfer and release reserved stock. Either branch endpoint may reject.
     */
    public function rejectTransfer(int $transferId, string $reason, User $actor): StockTransfer
    {
        return DB::transaction(function () use ($transferId, $reason, $actor) {
            $transfer = $this->transferRepo->findById($transferId, ['items'])
                ?? throw new ModelNotFoundException("Transfer [{$transferId}] not found.");

            $this->assertBranchAccess($actor, $transfer->from_branch_id, $transfer->to_branch_id);

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

            $rejected = $this->transferRepo->reject($transfer, $reason);

            $this->auditRepo->log(
                tableName:  'stock_transfers',
                recordId:   $transfer->id,
                action:     'reject',
                oldValues:  ['status' => 'pending'],
                newValues:  ['status' => 'rejected', 'reason' => $reason],
                employeeId: $actor->employee_id,
                branchId:   $transfer->from_branch_id,
            );

            return $rejected;
        });
    }
}
