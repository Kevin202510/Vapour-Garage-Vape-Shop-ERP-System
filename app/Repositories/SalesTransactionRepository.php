<?php

namespace App\Repositories;

use App\Contracts\Repositories\SalesTransactionRepositoryInterface;
use App\Models\SalesTransaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SalesTransactionRepository extends BaseRepository implements SalesTransactionRepositoryInterface
{
    public function __construct(SalesTransaction $model)
    {
        parent::__construct($model);
    }

    public function findById(int $id, array $relations = []): ?SalesTransaction
    {
        return $this->model
            ->with($relations ?: ['branch', 'cashier', 'customer', 'items.product', 'items.discount'])
            ->find($id);
    }

    public function lockForUpdate(int $id, array $relations = []): ?SalesTransaction
    {
        return $this->model
            ->with($relations ?: ['branch', 'cashier', 'customer', 'items.product', 'items.discount'])
            ->lockForUpdate()
            ->find($id);
    }

    public function findByTransactionNumber(string $number): ?SalesTransaction
    {
        return $this->model
            ->with(['branch', 'cashier', 'customer', 'items.product'])
            ->where('transaction_number', $number)
            ->first();
    }

    public function getByBranch(int $branchId, array $filters = []): Collection
    {
        return $this->buildQuery(array_merge($filters, ['branch_id' => $branchId]))->get();
    }

    public function getByDateRange(string $from, string $to, ?int $branchId = null): Collection
    {
        return $this->model
            ->with(['branch', 'cashier', 'customer'])
            ->whereBetween('transaction_date', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', 'completed')
            ->orderBy('transaction_date', 'desc')
            ->get();
    }

    public function void(SalesTransaction $transaction, string $reason): SalesTransaction
    {
        $transaction->update([
            'status'      => 'voided',
            'void_reason' => $reason,
        ]);

        return $transaction->fresh(['branch', 'cashier', 'items.product']);
    }

    public function getDailySummary(string $date, int $branchId): array
    {
        $summary = $this->model
            ->where('branch_id', $branchId)
            ->whereDate('transaction_date', $date)
            ->where('status', 'completed')
            ->selectRaw('
                COUNT(*)                AS transaction_count,
                SUM(subtotal)           AS gross_sales,
                SUM(discount_amount)    AS total_discounts,
                SUM(tax_amount)         AS total_tax,
                SUM(total_amount)       AS net_sales,
                SUM(CASE WHEN payment_method = "cash"         THEN total_amount ELSE 0 END) AS cash_sales,
                SUM(CASE WHEN payment_method = "gcash"        THEN total_amount ELSE 0 END) AS gcash_sales,
                SUM(CASE WHEN payment_method = "maya"         THEN total_amount ELSE 0 END) AS maya_sales,
                SUM(CASE WHEN payment_method = "card"         THEN total_amount ELSE 0 END) AS card_sales,
                SUM(CASE WHEN payment_method = "bank_transfer"THEN total_amount ELSE 0 END) AS bank_transfer_sales
            ')
            ->first();

        $voidCount = $this->model
            ->where('branch_id', $branchId)
            ->whereDate('transaction_date', $date)
            ->where('status', 'voided')
            ->count();

        return array_merge($summary->toArray(), ['voided_count' => $voidCount]);
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->buildQuery($filters)->paginate($perPage);
    }

    protected function buildQuery(array $filters = [])
    {
        return $this->model
            ->with(['branch', 'cashier', 'customer'])
            ->when(isset($filters['branch_id']),   fn ($q) => $q->where('branch_id', $filters['branch_id']))
            ->when(isset($filters['cashier_id']),  fn ($q) => $q->where('cashier_id', $filters['cashier_id']))
            ->when(isset($filters['customer_id']), fn ($q) => $q->where('customer_id', $filters['customer_id']))
            ->when(isset($filters['status']),      fn ($q) => $q->where('status', $filters['status']))
            ->when(isset($filters['payment_method']), fn ($q) => $q->where('payment_method', $filters['payment_method']))
            ->when(isset($filters['date_from']),   fn ($q) => $q->where('transaction_date', '>=', $filters['date_from'] . ' 00:00:00'))
            ->when(isset($filters['date_to']),     fn ($q) => $q->where('transaction_date', '<=', $filters['date_to'] . ' 23:59:59'))
            ->when(isset($filters['search']), fn ($q) => $q->where('transaction_number', 'like', '%' . $filters['search'] . '%'))
            ->orderBy($filters['sort_by'] ?? 'transaction_date', $filters['sort_order'] ?? 'desc');
    }
}
