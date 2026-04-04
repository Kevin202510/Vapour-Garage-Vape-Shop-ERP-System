<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Contracts\Repositories\BranchRepositoryInterface;
use App\Contracts\Repositories\SalesTransactionRepositoryInterface;
use App\Contracts\Repositories\BranchInventoryRepositoryInterface;
use App\Contracts\Repositories\StockTransferRepositoryInterface;
use App\Contracts\Repositories\ProductCategoryRepositoryInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use AuthorizesRequests;
    public function __construct(
        protected BranchRepositoryInterface              $branchRepo,
        protected SalesTransactionRepositoryInterface    $salesRepo,
        protected BranchInventoryRepositoryInterface     $inventoryRepo,
        protected StockTransferRepositoryInterface       $transferRepo,
        protected ProductCategoryRepositoryInterface     $categoryRepo,
    ) {}

    public function index()
    {
        $user     = auth()->user();
        $branches = $this->branchRepo->getActive();

        // Yesterday / today revenue for delta calculation
        $todayRev     = $this->getDayRevenue(now()->toDateString(),           $user);
        $yesterdayRev = $this->getDayRevenue(now()->subDay()->toDateString(), $user);
        $todayTxn     = $this->getDayTransactionCount(now()->toDateString(),  $user);
        $yestTxn      = $this->getDayTransactionCount(now()->subDay()->toDateString(), $user);

        $stats = [
            'revenue_today'        => $todayRev,
            'revenue_delta'        => $yesterdayRev > 0 ? round(($todayRev - $yesterdayRev) / $yesterdayRev * 100, 1) : 0,
            'transactions_today'   => $todayTxn,
            'transactions_delta'   => $yestTxn > 0 ? round(($todayTxn - $yestTxn) / $yestTxn * 100, 1) : 0,
            'items_sold'           => $this->getItemsSold(now()->toDateString(), $user),
            'items_delta'          => 0,
            'new_customers'        => $this->getNewCustomers(now()->toDateString(), $user),
            'customers_delta'      => 0,
            'avg_basket'           => $todayTxn > 0 ? round($todayRev / $todayTxn) : 0,
        ];

        // Revenue per branch today
        $branchRevenue = DB::table('sales_transactions')
            ->join('branches', 'sales_transactions.branch_id', '=', 'branches.id')
            ->whereDate('transaction_date', now())
            ->where('status', 'completed')
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('sales_transactions.branch_id', $user->branch_id))
            ->select('branches.name', DB::raw('SUM(total_amount) as revenue'))
            ->groupBy('branches.id', 'branches.name')
            ->orderByDesc('revenue')
            ->get();

        $topBranch = $branchRevenue->first();

        // Category sales today
        $categorySales = DB::table('sales_transaction_items')
            ->join('sales_transactions', 'sales_transaction_items.transaction_id', '=', 'sales_transactions.id')
            ->join('products', 'sales_transaction_items.product_id', '=', 'products.id')
            ->join('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->whereDate('sales_transactions.transaction_date', now())
            ->where('sales_transactions.status', 'completed')
            ->select('product_categories.name', DB::raw('SUM(sales_transaction_items.subtotal) as total'))
            ->groupBy('product_categories.id', 'product_categories.name')
            ->orderByDesc('total')
            ->limit(4)
            ->get();

        $recentTransactions = $this->salesRepo->paginate(8, [
            'sort_by'    => 'transaction_date',
            'sort_order' => 'desc',
        ])->withQueryString();

        $lowStockCount    = DB::table('branch_inventory')
            ->join('products', 'branch_inventory.product_id', '=', 'products.id')
            ->whereRaw('(branch_inventory.quantity_on_hand - branch_inventory.quantity_reserved) <= products.reorder_level')
            ->count();

        $pendingTransfers = DB::table('stock_transfers')->where('status', 'pending')->count();

        return view('pages.dashboard.index', compact(
            'branches', 'stats', 'branchRevenue', 'topBranch',
            'categorySales', 'recentTransactions', 'lowStockCount', 'pendingTransfers'
        ));
    }

    private function getDayRevenue(string $date, $user): float
    {
        return DB::table('sales_transactions')
            ->whereDate('transaction_date', $date)
            ->where('status', 'completed')
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('branch_id', $user->branch_id))
            ->sum('total_amount') ?? 0;
    }

    private function getDayTransactionCount(string $date, $user): int
    {
        return DB::table('sales_transactions')
            ->whereDate('transaction_date', $date)
            ->where('status', 'completed')
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('branch_id', $user->branch_id))
            ->count();
    }

    private function getItemsSold(string $date, $user): int
    {
        return DB::table('sales_transaction_items')
            ->join('sales_transactions', 'sales_transaction_items.transaction_id', '=', 'sales_transactions.id')
            ->whereDate('sales_transactions.transaction_date', $date)
            ->where('sales_transactions.status', 'completed')
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('sales_transactions.branch_id', $user->branch_id))
            ->sum('sales_transaction_items.quantity') ?? 0;
    }

    private function getNewCustomers(string $date, $user): int
    {
        info($date);
        return DB::table('customers')
            ->whereDate('created_at', $date)
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('registered_branch_id', $user->branch_id))
            ->count();
    }
}
