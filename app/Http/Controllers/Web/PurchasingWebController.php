<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Concerns\ResolvesActiveBranch;
use App\Http\Controllers\Controller;
use App\Services\PurchaseOrderService;
use App\Services\SupplierService;
use App\Services\BranchService;
use App\Services\ProductService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class PurchasingWebController extends Controller
{
    use AuthorizesRequests, ResolvesActiveBranch;
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
        $effectiveBranchId = $this->resolveEffectiveBranchId($user);
        if (! $user->isSuperAdmin()) { $filters['branch_id'] = $user->branch_id; }
        elseif ($effectiveBranchId)  { $filters['branch_id'] = $effectiveBranchId; }
        else                         { unset($filters['branch_id']); }
        if (($filters['status'] ?? 'all') === 'all') unset($filters['status']);

        $purchaseOrders    = $this->poService->list($filters, 20);
        $suppliers         = $this->supplierService->allActive();
        $branches          = $this->branchService->allActive();
        $activePoCount     = \App\Models\PurchaseOrder::whereIn('status', ['draft', 'approved', 'sent', 'partial'])->count();
        $activeBranchLabel = $effectiveBranchId
            ? ($branches->firstWhere('id', $effectiveBranchId)?->name ?? 'Selected Branch')
            : 'All Branches';

        return view('pages.purchasing.index', compact('purchaseOrders', 'suppliers', 'branches', 'activePoCount', 'effectiveBranchId', 'activeBranchLabel'));
    }

    public function export(Request $request)
    {
        $this->authorize('view purchase orders');

        $filters = $request->only(['status', 'supplier_id', 'branch_id', 'date_from', 'date_to', 'search']);
        $user    = auth()->user();
        $effectiveBranchId = $this->resolveEffectiveBranchId($user);
        if (! $user->isSuperAdmin()) { $filters['branch_id'] = $user->branch_id; }
        elseif ($effectiveBranchId)  { $filters['branch_id'] = $effectiveBranchId; }
        else                         { unset($filters['branch_id']); }
        if (($filters['status'] ?? 'all') === 'all') unset($filters['status']);

        $orders   = $this->poService->list($filters, 100000);
        $filename = 'purchase-orders-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['PO Number', 'Supplier', 'Branch', 'Order Date', 'Expected Date', 'Status', 'Total Amount']);
            foreach ($orders as $po) {
                fputcsv($handle, [
                    $po->po_number,
                    $po->supplier->company_name ?? 'N/A',
                    $po->branch->name ?? 'N/A',
                    optional($po->order_date)->format('Y-m-d'),
                    optional($po->expected_date)->format('Y-m-d'),
                    ucfirst($po->status),
                    number_format($po->total_amount, 2),
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
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

        if (! in_array($po->status, ['approved', 'sent', 'partial'])) {
            return redirect()->route('purchasing.show', $id)
                ->with('error', 'This purchase order cannot be received in its current status.');
        }

        return view('pages.purchasing.receive', compact('po'));
    }

    public function receive(Request $request, int $id)
    {
        $this->authorize('receive purchase orders');

        $validated = $request->validate([
            'items'                     => 'required|array',
            'items.*.product_id'        => 'required|integer|exists:products,id',
            'items.*.quantity_received' => 'required|integer|min:0',
        ]);

        try {
            $po     = $this->poService->receive($id, $validated['items']);
            $status = $po->status === 'received' ? 'fully received' : 'partially received';
            return redirect()->route('purchasing.show', $id)
                ->with('success', "Purchase order {$status}. Inventory updated at {$po->branch->name}.");
        } catch (\RuntimeException $e) {
            return back()->withErrors(['receive' => $e->getMessage()]);
        }
    }

    public function cancel(int $id)
    {
        $this->authorize('cancel purchase orders');
        $this->poService->cancel($id);
        return back()->with('success', 'Purchase order cancelled.');
    }
}
