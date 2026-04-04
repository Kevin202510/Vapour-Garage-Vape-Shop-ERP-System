<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\BranchService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryWebController extends Controller
{
    use AuthorizesRequests;
    public function __construct(protected BranchService $branchService) {}

    public function index(Request $request)
    {
        $branchId = $request->input('branch_id', auth()->user()->branch_id);
        $branches = $this->branchService->allActive();

        $inventory = \App\Models\BranchInventory::with(['product.category', 'branch'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->paginate(25);

        $totalRecords = $inventory->total();
        $criticalCount = DB::table('branch_inventory')
            ->join('products', 'branch_inventory.product_id', '=', 'products.id')
            ->whereRaw('(branch_inventory.quantity_on_hand - branch_inventory.quantity_reserved) <= 0')
            ->count();
        $lowCount = DB::table('branch_inventory')
            ->join('products', 'branch_inventory.product_id', '=', 'products.id')
            ->whereRaw('(branch_inventory.quantity_on_hand - branch_inventory.quantity_reserved) > 0')
            ->whereRaw('(branch_inventory.quantity_on_hand - branch_inventory.quantity_reserved) <= products.reorder_level')
            ->count();

        // Branch health %
        $branchHealth = \App\Models\Branch::all()->map(function ($branch) {
            $total    = \App\Models\BranchInventory::where('branch_id', $branch->id)->count();
            $healthy  = \App\Models\BranchInventory::where('branch_id', $branch->id)
                ->join('products', 'branch_inventory.product_id', '=', 'products.id')
                ->whereRaw('(branch_inventory.quantity_on_hand - branch_inventory.quantity_reserved) > products.reorder_level')
                ->count();
            $branch->health_pct = $total > 0 ? round(($healthy / $total) * 100) : 0;
            return $branch;
        });

        // Reorder alerts
        $reorderAlerts = DB::table('branch_inventory')
            ->join('products', 'branch_inventory.product_id', '=', 'products.id')
            ->join('branches', 'branch_inventory.branch_id', '=', 'branches.id')
            ->whereRaw('(branch_inventory.quantity_on_hand - branch_inventory.quantity_reserved) <= products.reorder_level')
            ->select(
                'products.id as product_id',
                'products.name as product_name',
                'products.sku',
                'products.reorder_level',
                'branches.name as branch_name',
                DB::raw('(branch_inventory.quantity_on_hand - branch_inventory.quantity_reserved) as available')
            )
            ->orderBy('available')
            ->limit(5)
            ->get();

        return view('pages.inventory.index', compact(
            'inventory', 'branches', 'totalRecords', 'criticalCount',
            'lowCount', 'branchHealth', 'reorderAlerts'
        ));
    }
}
