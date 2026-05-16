<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\PurchaseOrderService;
use App\Services\SupplierService;
use App\Services\BranchService;
use App\Services\ProductService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class PurchasingWebController extends Controller
{
    use AuthorizesRequests;
    public function __construct(
        protected PurchaseOrderService $poService,
        protected SupplierService      $supplierService,
        protected BranchService        $branchService,
        protected ProductService       $productService,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'supplier_id', 'branch_id', 'date_from', 'date_to', 'search']);
        $user    = auth()->user();
        if (! $user->isSuperAdmin()) $filters['branch_id'] = $user->branch_id;
        if (($filters['status'] ?? 'all') === 'all') unset($filters['status']);

        $purchaseOrders = $this->poService->list($filters, 20);
        $suppliers      = $this->supplierService->allActive();
        $branches       = $this->branchService->allActive();
        $activePoCount  = \App\Models\PurchaseOrder::whereIn('status', ['draft', 'approved', 'sent', 'partial'])->count();

        return view('pages.purchasing.index', compact('purchaseOrders', 'suppliers', 'branches', 'activePoCount'));
    }

    public function create()
    {
        $suppliers = $this->supplierService->allActive();
        $branches  = $this->branchService->allActive();
        $products  = $this->productService->list(['is_active' => true]);
        return view('pages.purchasing.form', compact('suppliers', 'branches', 'products'));
    }

    public function store(Request $request)
    {
        $this->authorize('create purchase orders');
        $validated = $request->validate([
            'supplier_id'              => 'required|integer|exists:suppliers,id',
            'branch_id'                => 'required|integer|exists:branches,id',
            'order_date'               => 'required|date',
            'expected_date'            => 'nullable|date|after_or_equal:order_date',
            'notes'                    => 'nullable|string',
            'items'                    => 'required|array|min:1',
            'items.*.product_id'       => 'required|integer|exists:products,id',
            'items.*.quantity_ordered' => 'required|integer|min:1',
            'items.*.unit_cost'        => 'required|numeric|min:0',
        ]);
        $validated['created_by'] = auth()->user()->employee_id ?? auth()->id();
        $this->poService->create($validated);
        return redirect()->route('purchasing.index')->with('success', 'Purchase order created.');
    }

    public function show(int $id)
    {
        $po = $this->poService->findOrFail($id);
        return view('pages.purchasing.show', compact('po'));
    }

    public function approve(Request $request, int $id)
    {
        $this->authorize('approve purchase orders');
        $this->poService->approve($id, auth()->user()->employee_id ?? auth()->id());
        return back()->with('success', 'Purchase order approved.');
    }

    public function receiveForm(int $id)
    {
        $po = $this->poService->findOrFail($id);
        return view('pages.purchasing.receive', compact('po'));
    }

    public function receive(Request $request, int $id)
    {
        $this->authorize('receive purchase orders');
        $validated = $request->validate([
            'items'                     => 'required|array',
            'items.*.product_id'        => 'required|integer',
            'items.*.quantity_received' => 'required|integer|min:0',
        ]);
        $this->poService->receive($id, $validated['items']);
        return redirect()->route('purchasing.index')->with('success', 'Items received. Inventory updated.');
    }

    public function cancel(int $id)
    {
        $this->authorize('cancel purchase orders');
        $this->poService->cancel($id);
        return back()->with('success', 'Purchase order cancelled.');
    }
}
