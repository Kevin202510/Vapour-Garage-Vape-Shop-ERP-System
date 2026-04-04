<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Services\ProductCategoryService;
use App\Services\BranchService;
use Illuminate\Support\Facades\DB;

class PosWebController extends Controller
{
    public function __construct(
        protected ProductService         $productService,
        protected ProductCategoryService $categoryService,
        protected BranchService          $branchService,
    ) {}

    public function index()
    {
        $branchId   = auth()->user()->branch_id;
        $categories = $this->categoryService->all();

        // Products with branch stock
        $products = $this->productService->list(['is_active' => true])
            ->map(fn ($p) => [
                'id'            => $p->id,
                'name'          => $p->name,
                'sku'           => $p->sku,
                'selling_price' => $p->selling_price,
                'unit_cost'     => $p->unit_cost,
                'tax_rate'      => $p->tax_rate,
                'category_id'   => $p->category_id,
                'category_name' => $p->category->name ?? 'Uncategorised',
                'is_active'     => $p->is_active,
                'stock'         => $p->inventory
                    ->where('branch_id', $branchId)
                    ->first()?->available_quantity ?? 0,
                'reorder_level' => $p->reorder_level,
            ]);

        // Customers enriched with visit count + last products for returning-customer detection
        $customers = \App\Models\Customer::with([
                'transactions' => fn ($q) => $q
                    ->where('status', 'completed')
                    ->when($branchId, fn ($q2) => $q2->where('branch_id', $branchId))
                    ->latest('transaction_date')
                    ->limit(5),
                'transactions.items.product',
            ])
            ->get()
            ->map(function ($customer) use ($branchId) {
                $visitCount = $customer->transactions->count();

                // Collect last 3 unique product SKUs from most recent transactions
                $lastProducts = $customer->transactions
                    ->flatMap(fn ($t) => $t->items->map(fn ($i) => $i->product?->sku))
                    ->filter()
                    ->unique()
                    ->take(3)
                    ->values()
                    ->toArray();

                $lastVisit = $customer->transactions->first()?->transaction_date;

                return [
                    'id'             => $customer->id,
                    'name'           => $customer->full_name,
                    'email'          => $customer->email,
                    'phone'          => $customer->phone,
                    'tier'           => $customer->tier,
                    'loyalty_points' => $customer->loyalty_points,
                    'visit_count'    => $visitCount,
                    'last_visit'     => $lastVisit
                        ? \Carbon\Carbon::parse($lastVisit)->diffForHumans()
                        : null,
                    'last_products'  => $lastProducts,
                ];
            });

        return view('pages.pos.index', compact('products', 'categories', 'customers'));
    }
}
