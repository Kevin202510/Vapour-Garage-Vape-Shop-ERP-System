<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductCategoryService;
use App\Services\BranchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosWebController extends Controller
{
    public function __construct(
        protected ProductCategoryService $categoryService,
        protected BranchService          $branchService,
    ) {}

    public function index(Request $request)
    {
        $user     = auth()->user();
        $branches = $user->isSuperAdmin() ? $this->branchService->allActive() : collect();

        // Admins/super-admins: honour ?branch_id= param first, then session active branch, then first branch.
        // Regular users are locked to their assigned branch.
        $branchId = $user->isSuperAdmin()
            ? ($request->integer('branch_id') ?: $user->activeBranchId() ?? $branches->first()?->id)
            : $user->branch_id;

        $activeBranch = $branches->firstWhere('id', $branchId) ?? $user->branch;
        $categories   = $this->categoryService->all();

        // Load ALL active products with inventory eager-loaded.
        // Must not paginate here — the entire catalogue is passed to the
        // client-side Alpine app for search/filter to work correctly.
        $products = Product::with(['category', 'inventory'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
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

        return view('pages.pos.index', compact('products', 'categories', 'customers', 'branchId', 'activeBranch', 'branches'));
    }
}
