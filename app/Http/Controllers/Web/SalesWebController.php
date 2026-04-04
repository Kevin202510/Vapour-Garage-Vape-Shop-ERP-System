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
        if (! $user->isSuperAdmin()) $filters['branch_id'] = $user->branch_id;
        if (($filters['status'] ?? 'all') === 'all') unset($filters['status']);

        $transactions = $this->salesService->list($filters, 20);
        $branches     = $this->branchService->allActive();

        $branchId = $user->isSuperAdmin() ? null : $user->branch_id;
        $stats = [
            'month_revenue' => DB::table('sales_transactions')
                ->whereMonth('transaction_date', now()->month)
                ->whereYear('transaction_date', now()->year)
                ->where('status', 'completed')
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->sum('total_amount'),
            'month_count' => DB::table('sales_transactions')
                ->whereMonth('transaction_date', now()->month)
                ->whereYear('transaction_date', now()->year)
                ->where('status', 'completed')
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->count(),
            'avg_basket' => 0,
        ];
        $stats['avg_basket'] = $stats['month_count'] > 0
            ? round($stats['month_revenue'] / $stats['month_count'])
            : 0;

        return view('pages.sales.index', compact('transactions', 'branches', 'stats'));
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
}
