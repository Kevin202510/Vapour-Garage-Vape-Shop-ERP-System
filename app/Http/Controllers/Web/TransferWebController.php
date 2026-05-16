<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\InventoryService;
use App\Services\BranchService;
use App\Services\ProductService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class TransferWebController extends Controller
{
    use AuthorizesRequests;
    public function __construct(
        protected InventoryService $inventoryService,
        protected BranchService    $branchService,
        protected ProductService   $productService,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'branch_id', 'date_from', 'date_to', 'search']);
        $user    = auth()->user();
        if (! $user->isSuperAdmin()) $filters['branch_id'] = $user->branch_id;
        if (($filters['status'] ?? 'all') === 'all') unset($filters['status']);

        $transfers         = \App\Models\StockTransfer::with(['fromBranch', 'toBranch', 'requestedBy', 'items.product'])
            ->when(isset($filters['status']),       fn ($q) => $q->where('status', $filters['status']))
            ->when(isset($filters['branch_id']),    fn ($q) => $q->where(fn ($s) =>
                $s->where('from_branch_id', $filters['branch_id'])->orWhere('to_branch_id', $filters['branch_id'])
            ))
            ->when(! empty($filters['date_from']),  fn ($q) => $q->whereDate('requested_at', '>=', $filters['date_from']))
            ->when(! empty($filters['date_to']),    fn ($q) => $q->whereDate('requested_at', '<=', $filters['date_to']))
            ->when(! empty($filters['search']),     fn ($q) => $q->where('transfer_number', 'like', "%{$filters['search']}%"))
            ->orderByDesc('requested_at')
            ->paginate(15);

        $pendingCount      = \App\Models\StockTransfer::where('status', 'pending')->count();
        $inTransitCount    = \App\Models\StockTransfer::where('status', 'in_transit')->count();
        $completedThisWeek = \App\Models\StockTransfer::where('status', 'received')
            ->whereBetween('completed_at', [now()->startOfWeek(), now()->endOfWeek()])->count();

        $branches = $this->branchService->allActive();
        $products = $this->productService->list(['is_active' => true]);

        return view('pages.transfers.index', compact(
            'transfers', 'pendingCount', 'inTransitCount', 'completedThisWeek', 'branches', 'products'
        ));
    }

    public function show(int $id)
    {
        $transfer = \App\Models\StockTransfer::with(['fromBranch', 'toBranch', 'requestedBy', 'approvedBy', 'items.product'])->findOrFail($id);
        return view('pages.transfers.show', compact('transfer'));
    }

    public function store(Request $request)
    {
        $this->authorize('create stock transfers');
        $validated = $request->validate([
            'from_branch_id'             => 'required|integer|exists:branches,id',
            'to_branch_id'               => 'required|integer|exists:branches,id|different:from_branch_id',
            'notes'                      => 'nullable|string',
            'items'                      => 'required|array|min:1',
            'items.*.product_id'         => 'required|integer|exists:products,id',
            'items.*.quantity_requested' => 'required|integer|min:1',
        ]);
        $this->inventoryService->requestTransfer($validated, auth()->user()->employee_id ?? auth()->id());
        return redirect()->route('transfers.index')->with('success', 'Transfer requested.');
    }

    public function approve(Request $request, int $id)
    {
        $this->authorize('approve stock transfers');
        $this->inventoryService->approveTransfer($id, auth()->user()->employee_id ?? auth()->id());
        return back()->with('success', 'Transfer approved.');
    }

    public function reject(Request $request, int $id)
    {
        $this->authorize('approve stock transfers');
        $validated = $request->validate(['reason' => 'required|string|max:500']);
        $this->inventoryService->rejectTransfer($id, $validated['reason']);
        return back()->with('success', 'Transfer rejected.');
    }

    public function receive(int $id)
    {
        $this->authorize('receive stock transfers');
        $this->inventoryService->receiveTransfer($id);
        return back()->with('success', 'Transfer received. Inventory updated.');
    }
}
