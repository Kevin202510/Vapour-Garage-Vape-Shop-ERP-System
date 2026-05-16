<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\BranchService;
use App\Services\InventoryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryWebController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected BranchService    $branchService,
        protected InventoryService $inventoryService,
    ) {}

    public function index(Request $request)
    {
        $user      = auth()->user();
        $branchId  = $request->input('branch_id',
            $user->isSuperAdmin()
                ? (request()->has('branch_id') ? (request('branch_id') ?: null) : $user->activeBranchId())
                : $user->branch_id
        );
        $search   = $request->input('search');
        $branches = $this->branchService->allActive();
        $activeBranchLabel = $branchId
            ? ($branches->firstWhere('id', $branchId)?->name ?? 'Selected Branch')
            : 'All Branches';
        $products = Product::where('is_active', true)->orderBy('name')->get();

        $inventory = \App\Models\BranchInventory::with(['product.category', 'branch'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($search,   fn ($q) => $q->whereHas('product', fn ($p) =>
                $p->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%")
            ))
            ->paginate(25);

        $totalRecords  = $inventory->total();
        $criticalCount = DB::table('branch_inventory')
            ->join('products', 'branch_inventory.product_id', '=', 'products.id')
            ->whereRaw('(branch_inventory.quantity_on_hand - branch_inventory.quantity_reserved) <= 0')
            ->count();
        $lowCount = DB::table('branch_inventory')
            ->join('products', 'branch_inventory.product_id', '=', 'products.id')
            ->whereRaw('(branch_inventory.quantity_on_hand - branch_inventory.quantity_reserved) > 0')
            ->whereRaw('(branch_inventory.quantity_on_hand - branch_inventory.quantity_reserved) <= products.reorder_level')
            ->count();

        $branchHealth = \App\Models\Branch::all()->map(function ($branch) {
            $total   = \App\Models\BranchInventory::where('branch_id', $branch->id)->count();
            $healthy = \App\Models\BranchInventory::where('branch_id', $branch->id)
                ->join('products', 'branch_inventory.product_id', '=', 'products.id')
                ->whereRaw('(branch_inventory.quantity_on_hand - branch_inventory.quantity_reserved) > products.reorder_level')
                ->count();
            $branch->health_pct = $total > 0 ? round(($healthy / $total) * 100) : 0;
            return $branch;
        });

        $reorderAlerts = DB::table('branch_inventory')
            ->join('products', 'branch_inventory.product_id', '=', 'products.id')
            ->join('branches', 'branch_inventory.branch_id', '=', 'branches.id')
            ->whereRaw('(branch_inventory.quantity_on_hand - branch_inventory.quantity_reserved) <= products.reorder_level')
            ->select(
                'products.id as product_id',
                'products.name as product_name',
                'products.sku',
                'products.reorder_level',
                'branches.id as branch_id',
                'branches.name as branch_name',
                DB::raw('(branch_inventory.quantity_on_hand - branch_inventory.quantity_reserved) as available'),
                DB::raw('branch_inventory.quantity_on_hand as quantity_on_hand'),
            )
            ->orderBy('available')
            ->limit(5)
            ->get();

        $effectiveBranchId = $branchId;
        return view('pages.inventory.index', compact(
            'inventory', 'branches', 'products', 'totalRecords', 'criticalCount',
            'lowCount', 'branchHealth', 'reorderAlerts', 'effectiveBranchId', 'activeBranchLabel'
        ));
    }

    public function adjust(Request $request)
    {
        $this->authorize('adjust inventory');

        $validated = $request->validate([
            'branch_id'  => 'required|integer|exists:branches,id',
            'product_id' => 'required|integer|exists:products,id',
            'quantity'   => 'required|integer|min:0',
            'reason'     => 'required|string|max:500',
        ]);

        $user = auth()->user();

        if (! $user->isSuperAdmin() && (int) $validated['branch_id'] !== (int) $user->branch_id) {
            abort(403, 'You can only adjust inventory for your own branch.');
        }

        try {
            $this->inventoryService->adjust(
                (int) $validated['branch_id'],
                (int) $validated['product_id'],
                (int) $validated['quantity'],
                $user->employee_id,
                $request->ip(),
                $validated['reason'],
            );
            return back()->with('success', 'Stock adjusted successfully.');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['adjust' => $e->getMessage()]);
        }
    }
}
