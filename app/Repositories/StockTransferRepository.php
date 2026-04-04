<?php

namespace App\Repositories;

use App\Contracts\Repositories\StockTransferRepositoryInterface;
use App\Models\StockTransfer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class StockTransferRepository extends BaseRepository implements StockTransferRepositoryInterface
{
    public function __construct(StockTransfer $model)
    {
        parent::__construct($model);
    }

    public function findById(int $id, array $relations = []): ?StockTransfer
    {
        return $this->model
            ->with($relations ?: ['fromBranch', 'toBranch', 'requestedBy', 'approvedBy', 'items.product'])
            ->find($id);
    }

    public function findByTransferNumber(string $number): ?StockTransfer
    {
        return $this->model
            ->with(['fromBranch', 'toBranch', 'items.product'])
            ->where('transfer_number', $number)
            ->first();
    }

    public function getByBranch(int $branchId, string $direction = 'both'): Collection
    {
        return $this->model
            ->with(['fromBranch', 'toBranch', 'requestedBy', 'items.product'])
            ->when($direction === 'outgoing', fn ($q) => $q->where('from_branch_id', $branchId))
            ->when($direction === 'incoming', fn ($q) => $q->where('to_branch_id', $branchId))
            ->when($direction === 'both',     fn ($q) => $q->where(fn ($s) =>
                $s->where('from_branch_id', $branchId)->orWhere('to_branch_id', $branchId)
            ))
            ->orderBy('requested_at', 'desc')
            ->get();
    }

    public function getPending(): Collection
    {
        return $this->model
            ->with(['fromBranch', 'toBranch', 'requestedBy'])
            ->where('status', 'pending')
            ->orderBy('requested_at')
            ->get();
    }

    public function approve(StockTransfer $transfer, int $approverId): StockTransfer
    {
        $transfer->update([
            'status'      => 'approved',
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);

        return $transfer->fresh(['fromBranch', 'toBranch', 'approvedBy', 'items.product']);
    }

    public function reject(StockTransfer $transfer, string $reason): StockTransfer
    {
        $transfer->update([
            'status'           => 'rejected',
            'rejection_reason' => $reason,
        ]);

        return $transfer->fresh(['fromBranch', 'toBranch']);
    }

    public function complete(StockTransfer $transfer): StockTransfer
    {
        $transfer->update([
            'status'       => 'received',
            'completed_at' => now(),
        ]);

        return $transfer->fresh(['fromBranch', 'toBranch', 'items.product']);
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->buildQuery($filters)->paginate($perPage);
    }

    protected function buildQuery(array $filters = [])
    {
        return $this->model
            ->with(['fromBranch', 'toBranch', 'requestedBy'])
            ->when(isset($filters['from_branch_id']), fn ($q) => $q->where('from_branch_id', $filters['from_branch_id']))
            ->when(isset($filters['to_branch_id']),   fn ($q) => $q->where('to_branch_id', $filters['to_branch_id']))
            ->when(isset($filters['branch_id']), fn ($q) => $q->where(fn ($s) =>
                $s->where('from_branch_id', $filters['branch_id'])
                  ->orWhere('to_branch_id', $filters['branch_id'])
            ))
            ->when(isset($filters['status']),          fn ($q) => $q->where('status', $filters['status']))
            ->when(isset($filters['requested_by']),    fn ($q) => $q->where('requested_by', $filters['requested_by']))
            ->when(isset($filters['date_from']),       fn ($q) => $q->where('requested_at', '>=', $filters['date_from'] . ' 00:00:00'))
            ->when(isset($filters['date_to']),         fn ($q) => $q->where('requested_at', '<=', $filters['date_to'] . ' 23:59:59'))
            ->when(isset($filters['search']),          fn ($q) => $q->where('transfer_number', 'like', '%' . $filters['search'] . '%'))
            ->orderBy($filters['sort_by'] ?? 'requested_at', $filters['sort_order'] ?? 'desc');
    }
}
