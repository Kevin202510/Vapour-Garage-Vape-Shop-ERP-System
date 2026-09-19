<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Concerns\ResolvesActiveBranch;
use App\Http\Controllers\Controller;
use App\Contracts\Repositories\BranchRepositoryInterface;
use App\Contracts\Repositories\SalesTransactionRepositoryInterface;
use App\Contracts\Repositories\BranchInventoryRepositoryInterface;
use App\Contracts\Repositories\StockTransferRepositoryInterface;
use App\Contracts\Repositories\ProductCategoryRepositoryInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use AuthorizesRequests, ResolvesActiveBranch;

    public function __construct(
        protected BranchRepositoryInterface              $branchRepo,
        protected SalesTransactionRepositoryInterface    $salesRepo,
        protected BranchInventoryRepositoryInterface     $inventoryRepo,
        protected StockTransferRepositoryInterface       $transferRepo,
        protected ProductCategoryRepositoryInterface     $categoryRepo,
    ) {}

    public function index(Request $request)
    {
        $user  = auth()->user();
        $range = $request->input('range', 'today');

        // Resolve the branch whose data to show.
        // Admins: URL ?range= param ignored for branch; use active branch from session unless URL explicitly overrides.
        $effectiveBranchId = $this->resolveEffectiveBranchId($user);

        $activeBranchLabel = $effectiveBranchId
            ? ($user->activeBranch()?->name ?? 'Selected Branch')
            : 'All Branches';

        [$dateFrom, $dateTo, $prevFrom, $prevTo, $deltaLabel] = $this->resolveDateRange($range);

        $branches = $this->branchRepo->getActive();

        $currentRev       = $this->getRangeRevenue($dateFrom, $dateTo, $effectiveBranchId);
        $previousRev      = $this->getRangeRevenue($prevFrom, $prevTo, $effectiveBranchId);
        $currentTxn       = $this->getRangeTxnCount($dateFrom, $dateTo, $effectiveBranchId);
        $previousTxn      = $this->getRangeTxnCount($prevFrom, $prevTo, $effectiveBranchId);
        $currentItems     = $this->getRangeItemsSold($dateFrom, $dateTo, $effectiveBranchId);
        $previousItems    = $this->getRangeItemsSold($prevFrom, $prevTo, $effectiveBranchId);
        $currentCustomers = $this->getRangeNewCustomers($dateFrom, $dateTo, $effectiveBranchId);
        $previousCustomers= $this->getRangeNewCustomers($prevFrom, $prevTo, $effectiveBranchId);

        $stats = [
            'revenue_today'      => $currentRev,
            'revenue_delta'      => $previousRev > 0 ? round(($currentRev - $previousRev) / $previousRev * 100, 1) : 0,
            'transactions_today' => $currentTxn,
            'transactions_delta' => $previousTxn > 0 ? round(($currentTxn - $previousTxn) / $previousTxn * 100, 1) : 0,
            'items_sold'         => $currentItems,
            'items_delta'        => $previousItems > 0 ? round(($currentItems - $previousItems) / $previousItems * 100, 1) : 0,
            'new_customers'      => $currentCustomers,
            'customers_delta'    => $previousCustomers > 0 ? round(($currentCustomers - $previousCustomers) / $previousCustomers * 100, 1) : 0,
            'avg_basket'         => $currentTxn > 0 ? round($currentRev / $currentTxn) : 0,
        ];

        $branchRevenue = $this->getBranchRevenue($dateFrom, $dateTo, $effectiveBranchId);
        $topBranch     = $branchRevenue->first();
        $categorySales = $this->getCategorySales($dateFrom, $dateTo, $effectiveBranchId, limit: 4);

        $recentTransactions = $this->salesRepo->paginate(8, array_filter([
            'branch_id'  => $effectiveBranchId,
            'sort_by'    => 'transaction_date',
            'sort_order' => 'desc',
        ]))->withQueryString();

        $lowStockCount = DB::table('branch_inventory')
            ->join('products', 'branch_inventory.product_id', '=', 'products.id')
            ->whereNull('products.deleted_at')
            ->where('products.is_active', true)
            ->when($effectiveBranchId, fn ($q) => $q->where('branch_inventory.branch_id', $effectiveBranchId))
            ->whereRaw('(branch_inventory.quantity_on_hand - branch_inventory.quantity_reserved) <= products.reorder_level')
            ->count();

        $pendingTransfers = DB::table('stock_transfers')
            ->where('status', 'pending')
            ->when($effectiveBranchId, fn ($q) => $q->where(fn ($s) =>
                $s->where('from_branch_id', $effectiveBranchId)->orWhere('to_branch_id', $effectiveBranchId)
            ))
            ->count();

        return view('pages.dashboard.index', compact(
            'branches', 'stats', 'branchRevenue', 'topBranch',
            'categorySales', 'recentTransactions', 'lowStockCount', 'pendingTransfers',
            'range', 'dateFrom', 'dateTo', 'deltaLabel',
            'effectiveBranchId', 'activeBranchLabel'
        ));
    }

    /**
     * Export the full dashboard picture for the selected range — the same
     * summary/branch/category data shown on screen, not just the transaction
     * list — as either a multi-section CSV or a formatted PDF report.
     */
    public function export(Request $request)
    {
        $this->authorize('export reports');

        $format = $request->input('format', 'csv') === 'pdf' ? 'pdf' : 'csv';
        $data   = $this->buildExportDataset($request);

        return $format === 'pdf'
            ? $this->exportPdf($data)
            : $this->exportCsv($data);
    }

    /**
     * Gather everything both export formats need, in one place, so CSV and
     * PDF stay in sync instead of drifting into two separate queries.
     */
    private function buildExportDataset(Request $request): array
    {
        $user  = auth()->user();
        $range = $request->input('range', 'today');
        [$dateFrom, $dateTo, $prevFrom, $prevTo, $deltaLabel] = $this->resolveDateRange($range);

        // Same branch resolution as index() — the previous implementation
        // ignored a super-admin's ?branch_id= override on the dashboard,
        // so the export silently covered a different scope than what was
        // on screen.
        $effectiveBranchId = $this->resolveEffectiveBranchId($user);
        $branchLabel        = $effectiveBranchId
            ? ($this->branchRepo->getActive()->firstWhere('id', $effectiveBranchId)?->name ?? 'Selected Branch')
            : 'All Branches';

        $currentRev        = $this->getRangeRevenue($dateFrom, $dateTo, $effectiveBranchId);
        $previousRev       = $this->getRangeRevenue($prevFrom, $prevTo, $effectiveBranchId);
        $currentTxn        = $this->getRangeTxnCount($dateFrom, $dateTo, $effectiveBranchId);
        $previousTxn       = $this->getRangeTxnCount($prevFrom, $prevTo, $effectiveBranchId);
        $currentItems      = $this->getRangeItemsSold($dateFrom, $dateTo, $effectiveBranchId);
        $previousItems     = $this->getRangeItemsSold($prevFrom, $prevTo, $effectiveBranchId);
        $currentCustomers  = $this->getRangeNewCustomers($dateFrom, $dateTo, $effectiveBranchId);
        $previousCustomers = $this->getRangeNewCustomers($prevFrom, $prevTo, $effectiveBranchId);

        $summary = [
            ['Revenue',       '₱' . number_format($currentRev, 2),   $this->pctDelta($currentRev, $previousRev) . ' ' . $deltaLabel],
            ['Transactions',  number_format($currentTxn),            $this->pctDelta($currentTxn, $previousTxn) . ' ' . $deltaLabel],
            ['Items Sold',    number_format($currentItems),          $this->pctDelta($currentItems, $previousItems) . ' ' . $deltaLabel],
            ['New Customers', number_format($currentCustomers),      $this->pctDelta($currentCustomers, $previousCustomers) . ' ' . $deltaLabel],
            ['Avg Basket Size', '₱' . number_format($currentTxn > 0 ? $currentRev / $currentTxn : 0, 2), ''],
        ];

        $branchRevenue = $this->getBranchRevenue($dateFrom, $dateTo, $effectiveBranchId);
        // Unlike the dashboard's donut chart (top 4 only), the export covers
        // every category — this is a full data export, not a screen mirror.
        $categorySales = $this->getCategorySales($dateFrom, $dateTo, $effectiveBranchId, limit: null);
        $totalCategoryRevenue = $categorySales->sum('total') ?: 1;

        $transactions = DB::table('sales_transactions')
            ->join('branches', 'sales_transactions.branch_id', '=', 'branches.id')
            ->leftJoin('employees as cashiers', 'sales_transactions.cashier_id', '=', 'cashiers.id')
            ->leftJoin('customers', 'sales_transactions.customer_id', '=', 'customers.id')
            ->whereNull('branches.deleted_at')
            ->whereBetween(DB::raw('DATE(transaction_date)'), [$dateFrom, $dateTo])
            ->where('sales_transactions.status', 'completed')
            ->when($effectiveBranchId, fn ($q) => $q->where('sales_transactions.branch_id', $effectiveBranchId))
            ->select(
                'sales_transactions.transaction_number',
                'sales_transactions.transaction_date',
                'branches.name as branch',
                DB::raw("TRIM(CONCAT(COALESCE(cashiers.first_name,''), ' ', COALESCE(cashiers.last_name,''))) as cashier"),
                DB::raw("COALESCE(TRIM(CONCAT(COALESCE(customers.first_name,''), ' ', COALESCE(customers.last_name,''))), 'Walk-in') as customer"),
                'sales_transactions.payment_method',
                'sales_transactions.subtotal',
                'sales_transactions.tax_amount',
                'sales_transactions.discount_amount',
                'sales_transactions.total_amount',
            )
            ->orderBy('transaction_date')
            ->get();

        return compact(
            'summary', 'branchRevenue', 'categorySales', 'totalCategoryRevenue', 'transactions',
            'dateFrom', 'dateTo', 'range', 'branchLabel'
        );
    }

    private function exportCsv(array $data)
    {
        ['summary' => $summary, 'branchRevenue' => $branchRevenue, 'categorySales' => $categorySales,
         'totalCategoryRevenue' => $totalCategoryRevenue, 'transactions' => $transactions,
         'dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'range' => $range, 'branchLabel' => $branchLabel] = $data;

        $filename = "dashboard-{$range}-{$dateFrom}-to-{$dateTo}.csv";

        return response()->streamDownload(function () use (
            $summary, $branchRevenue, $categorySales, $totalCategoryRevenue, $transactions,
            $dateFrom, $dateTo, $range, $branchLabel
        ) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ["Vapour Garage Main — Dashboard Report ({$range}: {$dateFrom} to {$dateTo})"]);
            fputcsv($handle, ["Branch: {$branchLabel}"]);
            fputcsv($handle, []);

            fputcsv($handle, ['SUMMARY']);
            fputcsv($handle, ['Metric', 'Value', 'Change']);
            foreach ($summary as $row) {
                fputcsv($handle, $row);
            }
            fputcsv($handle, []);

            fputcsv($handle, ['REVENUE BY BRANCH']);
            fputcsv($handle, ['Branch', 'Revenue']);
            foreach ($branchRevenue as $branch) {
                fputcsv($handle, [$branch->name, number_format($branch->revenue, 2)]);
            }
            fputcsv($handle, []);

            fputcsv($handle, ['SALES MIX BY CATEGORY']);
            fputcsv($handle, ['Category', 'Revenue', '% of Total']);
            foreach ($categorySales as $cat) {
                fputcsv($handle, [$cat->name, number_format($cat->total, 2), round(($cat->total / $totalCategoryRevenue) * 100) . '%']);
            }
            fputcsv($handle, []);

            fputcsv($handle, ['TRANSACTION DETAIL']);
            fputcsv($handle, ['TXN Number', 'Date & Time', 'Branch', 'Cashier', 'Customer', 'Payment', 'Subtotal', 'Tax', 'Discount', 'Total']);
            foreach ($transactions as $txn) {
                fputcsv($handle, [
                    $txn->transaction_number,
                    $txn->transaction_date,
                    $txn->branch,
                    $txn->cashier ?: 'N/A',
                    $txn->customer,
                    ucfirst(str_replace('_', ' ', $txn->payment_method)),
                    number_format($txn->subtotal, 2),
                    number_format($txn->tax_amount, 2),
                    number_format($txn->discount_amount, 2),
                    number_format($txn->total_amount, 2),
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function exportPdf(array $data)
    {
        $filename = "dashboard-{$data['range']}-{$data['dateFrom']}-to-{$data['dateTo']}.pdf";

        $pdf = Pdf::loadView('pages.dashboard.export-pdf', $data)->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

    // -------------------------------------------------------------------------

    private function getBranchRevenue(string $dateFrom, string $dateTo, ?int $branchId): \Illuminate\Support\Collection
    {
        return DB::table('sales_transactions')
            ->join('branches', 'sales_transactions.branch_id', '=', 'branches.id')
            ->whereNull('branches.deleted_at')
            ->whereBetween(DB::raw('DATE(transaction_date)'), [$dateFrom, $dateTo])
            ->where('status', 'completed')
            ->when($branchId, fn ($q) => $q->where('sales_transactions.branch_id', $branchId))
            ->select('branches.name', DB::raw('SUM(total_amount) as revenue'))
            ->groupBy('branches.id', 'branches.name')
            ->orderByDesc('revenue')
            ->get();
    }

    private function getCategorySales(string $dateFrom, string $dateTo, ?int $branchId, ?int $limit = 4): \Illuminate\Support\Collection
    {
        return DB::table('sales_transaction_items')
            ->join('sales_transactions', 'sales_transaction_items.transaction_id', '=', 'sales_transactions.id')
            ->join('products', 'sales_transaction_items.product_id', '=', 'products.id')
            ->join('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->whereNull('products.deleted_at')
            ->whereBetween(DB::raw('DATE(sales_transactions.transaction_date)'), [$dateFrom, $dateTo])
            ->where('sales_transactions.status', 'completed')
            ->when($branchId, fn ($q) => $q->where('sales_transactions.branch_id', $branchId))
            ->select('product_categories.name', DB::raw('SUM(sales_transaction_items.subtotal) as total'))
            ->groupBy('product_categories.id', 'product_categories.name')
            ->orderByDesc('total')
            ->when($limit, fn ($q) => $q->limit($limit))
            ->get();
    }

    private function pctDelta(int|float $current, int|float $previous): string
    {
        $pct = $previous > 0 ? round(($current - $previous) / $previous * 100, 1) : 0;
        return ($pct >= 0 ? '+' : '') . number_format($pct, 1) . '%';
    }

    private function resolveDateRange(string $range): array
    {
        return match($range) {
            'week' => [
                now()->startOfWeek()->toDateString(),
                now()->toDateString(),
                now()->subWeek()->startOfWeek()->toDateString(),
                now()->subWeek()->endOfWeek()->toDateString(),
                'vs last week',
            ],
            'month' => [
                now()->startOfMonth()->toDateString(),
                now()->toDateString(),
                now()->subMonth()->startOfMonth()->toDateString(),
                now()->subMonth()->endOfMonth()->toDateString(),
                'vs last month',
            ],
            default => [
                now()->toDateString(),
                now()->toDateString(),
                now()->subDay()->toDateString(),
                now()->subDay()->toDateString(),
                'vs yesterday',
            ],
        };
    }

    private function getRangeRevenue(string $from, string $to, ?int $branchId): float
    {
        return (float) DB::table('sales_transactions')
            ->whereBetween(DB::raw('DATE(transaction_date)'), [$from, $to])
            ->where('status', 'completed')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->sum('total_amount');
    }

    private function getRangeTxnCount(string $from, string $to, ?int $branchId): int
    {
        return DB::table('sales_transactions')
            ->whereBetween(DB::raw('DATE(transaction_date)'), [$from, $to])
            ->where('status', 'completed')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->count();
    }

    private function getRangeItemsSold(string $from, string $to, ?int $branchId): int
    {
        return (int) DB::table('sales_transaction_items')
            ->join('sales_transactions', 'sales_transaction_items.transaction_id', '=', 'sales_transactions.id')
            ->whereBetween(DB::raw('DATE(sales_transactions.transaction_date)'), [$from, $to])
            ->where('sales_transactions.status', 'completed')
            ->when($branchId, fn ($q) => $q->where('sales_transactions.branch_id', $branchId))
            ->sum('sales_transaction_items.quantity');
    }

    private function getRangeNewCustomers(string $from, string $to, ?int $branchId): int
    {
        return DB::table('customers')
            ->whereNull('deleted_at')
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->when($branchId, fn ($q) => $q->where('registered_branch_id', $branchId))
            ->count();
    }
}
