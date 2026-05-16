<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\SalesService;
use App\Services\BranchService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesWebController extends Controller
{
    use AuthorizesRequests;
    public function __construct(
        protected SalesService  $salesService,
        protected BranchService $branchService,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['branch_id', 'cashier_id', 'status', 'payment_method', 'date_from', 'date_to', 'search']);
        $user    = auth()->user();

        $effectiveBranchId = $user->isSuperAdmin()
            ? (request()->has('branch_id') ? (request('branch_id') ?: null) : $user->activeBranchId())
            : $user->branch_id;

        if (! $user->isSuperAdmin()) {
            $filters['branch_id'] = $user->branch_id;
        } elseif ($effectiveBranchId) {
            $filters['branch_id'] = $effectiveBranchId;
        } else {
            unset($filters['branch_id']);
        }

        if (($filters['status'] ?? 'all') === 'all') unset($filters['status']);

        $transactions      = $this->salesService->list($filters, 20);
        $branches          = $this->branchService->allActive();
        $activeBranchLabel = $effectiveBranchId
            ? ($branches->firstWhere('id', $effectiveBranchId)?->name ?? 'Selected Branch')
            : 'All Branches';

        $stats = [
            'month_revenue' => DB::table('sales_transactions')
                ->whereMonth('transaction_date', now()->month)
                ->whereYear('transaction_date', now()->year)
                ->where('status', 'completed')
                ->when($effectiveBranchId, fn ($q) => $q->where('branch_id', $effectiveBranchId))
                ->sum('total_amount'),
            'month_count' => DB::table('sales_transactions')
                ->whereMonth('transaction_date', now()->month)
                ->whereYear('transaction_date', now()->year)
                ->where('status', 'completed')
                ->when($effectiveBranchId, fn ($q) => $q->where('branch_id', $effectiveBranchId))
                ->count(),
            'avg_basket' => 0,
        ];
        $stats['avg_basket'] = $stats['month_count'] > 0
            ? round($stats['month_revenue'] / $stats['month_count'])
            : 0;

        return view('pages.sales.index', compact('transactions', 'branches', 'stats', 'effectiveBranchId', 'activeBranchLabel'));
    }

    public function show(int $id)
    {
        $transaction = $this->salesService->findOrFail($id);
        return view('pages.sales.show', compact('transaction'));
    }

    public function void(Request $request, int $id)
    {
        $this->authorize('void sales');
        $validated = $request->validate(['reason' => 'required|string|max:500']);
        $this->salesService->void($id, $validated['reason']);
        return back()->with('success', 'Transaction voided.');
    }

    public function export(Request $request)
    {
        $this->authorize('export reports');

        $filters = $request->only(['branch_id', 'cashier_id', 'status', 'payment_method', 'date_from', 'date_to', 'search']);
        $user    = auth()->user();
        $effectiveBranchId = $user->isSuperAdmin()
            ? (request()->has('branch_id') ? (request('branch_id') ?: null) : $user->activeBranchId())
            : $user->branch_id;
        if (! $user->isSuperAdmin()) { $filters['branch_id'] = $user->branch_id; }
        elseif ($effectiveBranchId)  { $filters['branch_id'] = $effectiveBranchId; }
        else                         { unset($filters['branch_id']); }
        if (($filters['status'] ?? 'all') === 'all') unset($filters['status']);

        $rows = DB::table('sales_transactions')
            ->join('branches', 'sales_transactions.branch_id', '=', 'branches.id')
            ->leftJoin('employees as cashiers', 'sales_transactions.cashier_id', '=', 'cashiers.id')
            ->leftJoin('customers', 'sales_transactions.customer_id', '=', 'customers.id')
            ->whereNull('branches.deleted_at')
            ->when(isset($filters['branch_id']),      fn ($q) => $q->where('sales_transactions.branch_id', $filters['branch_id']))
            ->when(isset($filters['status']),         fn ($q) => $q->where('sales_transactions.status', $filters['status']))
            ->when(isset($filters['payment_method']), fn ($q) => $q->where('sales_transactions.payment_method', $filters['payment_method']))
            ->when(! empty($filters['date_from']),    fn ($q) => $q->whereDate('transaction_date', '>=', $filters['date_from']))
            ->when(! empty($filters['date_to']),      fn ($q) => $q->whereDate('transaction_date', '<=', $filters['date_to']))
            ->when(! empty($filters['search']),       fn ($q) => $q->where('sales_transactions.transaction_number', 'like', "%{$filters['search']}%"))
            ->select(
                'sales_transactions.transaction_number',
                'sales_transactions.transaction_date',
                'branches.name as branch',
                DB::raw("TRIM(CONCAT(COALESCE(cashiers.first_name,''), ' ', COALESCE(cashiers.last_name,''))) as cashier"),
                DB::raw("COALESCE(TRIM(CONCAT(COALESCE(customers.first_name,''), ' ', COALESCE(customers.last_name,''))), 'Walk-in') as customer"),
                'sales_transactions.payment_method',
                'sales_transactions.status',
                'sales_transactions.subtotal',
                'sales_transactions.tax_amount',
                'sales_transactions.discount_amount',
                'sales_transactions.total_amount',
            )
            ->orderBy('transaction_date')
            ->get();

        $filename = 'sales-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['TXN Number', 'Date & Time', 'Branch', 'Cashier', 'Customer', 'Payment', 'Status', 'Subtotal', 'Tax', 'Discount', 'Total']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->transaction_number,
                    $row->transaction_date,
                    $row->branch,
                    $row->cashier ?: 'N/A',
                    $row->customer,
                    ucfirst(str_replace('_', ' ', $row->payment_method)),
                    ucfirst($row->status),
                    number_format($row->subtotal, 2),
                    number_format($row->tax_amount, 2),
                    number_format($row->discount_amount, 2),
                    number_format($row->total_amount, 2),
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
