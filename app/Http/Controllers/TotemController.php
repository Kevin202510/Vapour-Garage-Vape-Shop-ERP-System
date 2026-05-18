<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TotemController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->integer('branch_id') ?: null;
        $branch   = $branchId ? Branch::find($branchId) : null;

        // Featured: newest active products
        $newArrivals = Product::with('category')
            ->where('is_active', true)
            ->latest()
            ->take(6)
            ->get();

        // Sale products
        $saleProducts = Product::with('category')
            ->where('is_active', true)
            ->where('is_on_sale', true)
            ->whereNotNull('sale_price')
            ->latest()
            ->take(6)
            ->get();

        // Best sellers by qty
        $bestSellers = Product::with('category')
            ->where('is_active', true)
            ->select('products.*')
            ->selectSub(
                DB::table('sales_transaction_items as sti')
                    ->join('sales_transactions as st', 'sti.transaction_id', '=', 'st.id')
                    ->where('st.status', 'completed')
                    ->whereColumn('sti.product_id', 'products.id')
                    ->select(DB::raw('COALESCE(SUM(sti.quantity), 0)')),
                'total_sold'
            )
            ->orderByDesc('total_sold')
            ->take(6)
            ->get();

        // All active branches for "Find Us" slide
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        $shopUrl = config('app.url') . '/shop';

        return view('pages.totem.index', compact(
            'branch', 'branches', 'newArrivals', 'saleProducts', 'bestSellers', 'shopUrl'
        ));
    }
}
