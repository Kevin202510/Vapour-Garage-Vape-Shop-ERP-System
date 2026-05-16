<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Contracts\Repositories\BranchRepositoryInterface;
use App\Models\Product;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected BranchRepositoryInterface $branchRepo,
    ) {}

    public function index()
    {
        $branches = $this->branchRepo->getActive();

        // ── New Arrivals — most recently added active products ────────────────
        $newArrivals = Product::with('category')
            ->where('is_active', true)
            ->where(fn ($q) => $q->where('is_new_arrival', true)->orWhereRaw('1=1'))
            ->latest()
            ->take(8)
            ->get();

        // ── Best Sellers — by total units sold (all-time) ─────────────────────
        $bestSellers = Product::with('category')
            ->where('is_active', true)
            ->select('products.*')
            ->selectSub(
                DB::table('sales_transaction_items as sti')
                    ->join('sales_transactions as st', 'sti.transaction_id', '=', 'st.id')
                    ->where('st.status', 'completed')
                    ->whereNull('products.deleted_at')
                    ->whereColumn('sti.product_id', 'products.id')
                    ->select(DB::raw('COALESCE(SUM(sti.quantity), 0)')),
                'total_sold'
            )
            ->orderByDesc('total_sold')
            ->take(8)
            ->get();

        // ── Hot Products — by revenue in the last 30 days ─────────────────────
        $hotProducts = Product::with('category')
            ->where('is_active', true)
            ->select('products.*')
            ->selectSub(
                DB::table('sales_transaction_items as sti')
                    ->join('sales_transactions as st', 'sti.transaction_id', '=', 'st.id')
                    ->where('st.status', 'completed')
                    ->where('st.transaction_date', '>=', now()->subDays(30))
                    ->whereColumn('sti.product_id', 'products.id')
                    ->select(DB::raw('COALESCE(SUM(sti.subtotal), 0)')),
                'total_revenue'
            )
            ->orderByDesc('total_revenue')
            ->take(8)
            ->get();

        // ── Sale Products — flagged as on sale with a sale price ──────────────
        $saleProducts = Product::with('category')
            ->where('is_active', true)
            ->where('is_on_sale', true)
            ->whereNotNull('sale_price')
            ->latest()
            ->take(8)
            ->get();

        // ── Wholesale Products — flagged for bulk/wholesale ordering ──────────
        $wholesaleProducts = Product::with('category')
            ->where('is_active', true)
            ->where('is_wholesale', true)
            ->latest()
            ->take(8)
            ->get();

        return view('pages.landing.index', compact(
            'branches',
            'newArrivals',
            'bestSellers',
            'hotProducts',
            'saleProducts',
            'wholesaleProducts',
        ));
    }
}
