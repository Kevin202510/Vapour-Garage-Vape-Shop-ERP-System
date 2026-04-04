<?php

namespace App\Repositories;

use App\Contracts\Repositories\PurchaseOrderRepositoryInterface;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PurchaseOrderRepository extends BaseRepository implements PurchaseOrderRepositoryInterface
{
    public function __construct(PurchaseOrder $model)
    {
        parent::__construct($model);
    }

    public function findById(int $id, array $relations = []): ?PurchaseOrder
    {
        return $this->model
            ->with($relations ?: ['supplier', 'branch', 'creator', 'approver', 'items.product'])
            ->withTrashed()
            ->find($id);
    }

    public function findByPoNumber(string $poNumber): ?PurchaseOrder
    {
        return $this->model
            ->with(['supplier', 'branch', 'items.product'])
            ->where('po_number', $poNumber)
            ->first();
    }

    public function getByBranch(int $branchId, array $filters = []): Collection
    {
        return $this->buildQuery(array_merge($filters, ['branch_id' => $branchId]))->get();
    }

    public function getBySupplier(int $supplierId): Collection
    {
        return $this->model
            ->with(['branch', 'items.product'])
            ->where('supplier_id', $supplierId)
            ->orderBy('order_date', 'desc')
            ->get();
    }

    public function getPending(): Collection
    {
        return $this->model
            ->with(['supplier', 'branch', 'creator'])
            ->whereIn('status', ['draft', 'approved', 'sent', 'partial'])
            ->orderBy('order_date')
            ->get();
    }

    public function approve(PurchaseOrder $po, int $approverId): PurchaseOrder
    {
        $po->update([
            'status'      => 'approved',
            'approved_by' => $approverId,
        ]);

        return $po->fresh(['supplier', 'branch', 'approver', 'items.product']);
    }

    public function receive(PurchaseOrder $po, array $receivedItems): PurchaseOrder
    {
        return DB::transaction(function () use ($po, $receivedItems) {
            foreach ($receivedItems as $item) {
                PurchaseOrderItem::where('po_id', $po->id)
                    ->where('product_id', $item['product_id'])
                    ->update(['quantity_received' => $item['quantity_received']]);
            }

            // Determine new status: all items fully received → 'received', otherwise 'partial'
            $po->load('items');
            $allReceived = $po->items->every(fn ($i) => $i->quantity_received >= $i->quantity_ordered);

            $po->update([
                'status'        => $allReceived ? 'received' : 'partial',
                'received_date' => $allReceived ? now()->toDateString() : null,
            ]);

            return $po->fresh(['supplier', 'branch', 'items.product']);
        });
    }

    public function cancel(PurchaseOrder $po): PurchaseOrder
    {
        $po->update(['status' => 'cancelled']);
        return $po->fresh();
    }

    public function restore(int $id): bool
    {
        return (bool) $this->model->withTrashed()->find($id)?->restore();
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->buildQuery($filters)->paginate($perPage);
    }

    protected function buildQuery(array $filters = [])
    {
        return $this->model
            ->with(['supplier', 'branch', 'creator'])
            ->when(isset($filters['branch_id']),   fn ($q) => $q->where('branch_id', $filters['branch_id']))
            ->when(isset($filters['supplier_id']), fn ($q) => $q->where('supplier_id', $filters['supplier_id']))
            ->when(isset($filters['status']),      fn ($q) => $q->where('status', $filters['status']))
            ->when(isset($filters['with_trashed']),fn ($q) => $q->withTrashed())
            ->when(isset($filters['date_from']),   fn ($q) => $q->where('order_date', '>=', $filters['date_from']))
            ->when(isset($filters['date_to']),     fn ($q) => $q->where('order_date', '<=', $filters['date_to']))
            ->when(isset($filters['search']),      fn ($q) => $q->where('po_number', 'like', '%' . $filters['search'] . '%'))
            ->orderBy($filters['sort_by'] ?? 'order_date', $filters['sort_order'] ?? 'desc');
    }
}
