<?php

namespace App\Http\Controllers\Web;

use App\Concerns\EnforcesBranchAccess;
use App\Http\Controllers\Concerns\ResolvesActiveBranch;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Services\InventoryService;
use App\Services\BranchService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class TransferWebController extends Controller
{
    use AuthorizesRequests, ResolvesActiveBranch, EnforcesBranchAccess;

    public function __construct(
        protected InventoryService $inventoryService,
        protected BranchService    $branchService,
    ) {}

    public function index(Request $request)
    {
        $this->authorize('view stock transfers');

        $filters = $request->only(['status', 'branch_id', 'date_from', 'date_to', 'search']);
        $user    = auth()->user();
        $effectiveBranchId = $this->resolveEffectiveBranchId($user);
        if (! $user->isSuperAdmin()) { $filters['branch_id'] = $user->branch_id; }
        elseif ($effectiveBranchId)  { $filters['branch_id'] = $effectiveBranchId; }
        else                         { unset($filters['branch_id']); }
        if (($filters['status'] ?? 'all') === 'all') unset($filters['status']);

        $transfers = StockTransfer::with(['fromBranch', 'toBranch', 'requestedBy', 'items'])
            ->when(isset($filters['status']),      fn ($q) => $q->where('status', $filters['status']))
            ->when(isset($filters['branch_id']),   fn ($q) => $q->where(fn ($s) =>
                $s->where('from_branch_id', $filters['branch_id'])->orWhere('to_branch_id', $filters['branch_id'])
            ))
            ->when(! empty($filters['date_from']), fn ($q) => $q->whereDate('requested_at', '>=', $filters['date_from']))
            ->when(! empty($filters['date_to']),   fn ($q) => $q->whereDate('requested_at', '<=', $filters['date_to']))
            ->when(! empty($filters['search']),    fn ($q) => $q->where('transfer_number', 'like', "%{$filters['search']}%"))
            ->orderByDesc('requested_at')
            ->paginate(15);

        $pendingCount      = StockTransfer::where('status', 'pending')->count();
        $inTransitCount    = StockTransfer::where('status', 'in_transit')->count();
        $completedThisWeek = StockTransfer::where('status', 'received')
            ->whereBetween('completed_at', [now()->startOfWeek(), now()->endOfWeek()])->count();

        $branches = $this->branchService->allActive();
        $activeBranchLabel = $effectiveBranchId
            ? ($branches->firstWhere('id', $effectiveBranchId)?->name ?? 'Selected Branch')
            : 'All Branches';

        // Load ALL active products for the request-transfer modal (not paginated)
        $products = Product::where('is_active', true)
            ->with('category')
            ->orderBy('name')
            ->get();

        return view('pages.transfers.index', compact(
            'transfers', 'pendingCount', 'inTransitCount', 'completedThisWeek',
            'branches', 'products', 'effectiveBranchId', 'activeBranchLabel'
        ));
    }

    public function show(int $id)
    {
        $this->authorize('view stock transfers');

        $transfer = StockTransfer::with([
            'fromBranch', 'toBranch', 'requestedBy', 'approvedBy', 'items.product',
        ])->findOrFail($id);

        $this->assertBranchAccess(auth()->user(), $transfer->from_branch_id, $transfer->to_branch_id);

        return view('pages.transfers.show', compact('transfer'));
    }

    public function export(Request $request)
    {
        $this->authorize('view stock transfers');

        $filters = $request->only(['status', 'branch_id', 'date_from', 'date_to', 'search']);
        $user    = auth()->user();
        $effectiveBranchId = $this->resolveEffectiveBranchId($user);
        if (! $user->isSuperAdmin()) { $filters['branch_id'] = $user->branch_id; }
        elseif ($effectiveBranchId)  { $filters['branch_id'] = $effectiveBranchId; }
        else                         { unset($filters['branch_id']); }
        if (($filters['status'] ?? 'all') === 'all') unset($filters['status']);

        $transfers = StockTransfer::with(['fromBranch', 'toBranch', 'requestedBy'])
            ->when(isset($filters['status']),      fn ($q) => $q->where('status', $filters['status']))
            ->when(isset($filters['branch_id']),   fn ($q) => $q->where(fn ($s) =>
                $s->where('from_branch_id', $filters['branch_id'])->orWhere('to_branch_id', $filters['branch_id'])
            ))
            ->when(! empty($filters['date_from']), fn ($q) => $q->whereDate('requested_at', '>=', $filters['date_from']))
            ->when(! empty($filters['date_to']),   fn ($q) => $q->whereDate('requested_at', '<=', $filters['date_to']))
            ->when(! empty($filters['search']),    fn ($q) => $q->where('transfer_number', 'like', "%{$filters['search']}%"))
            ->orderByDesc('requested_at')
            ->get();

        $filename = 'stock-transfers-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($transfers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Transfer Number', 'From Branch', 'To Branch', 'Requested By', 'Status', 'Requested At']);
            foreach ($transfers as $transfer) {
                fputcsv($handle, [
                    $transfer->transfer_number,
                    $transfer->fromBranch->name ?? 'N/A',
                    $transfer->toBranch->name ?? 'N/A',
                    $transfer->requestedBy->full_name ?? 'N/A',
                    ucwords(str_replace('_', ' ', $transfer->status)),
                    optional($transfer->requested_at)->format('Y-m-d H:i'),
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function store(Request $request)
    {
        $this->authorize('create stock transfers');

        $validated = $request->validate([
            'from_branch_id'             => 'required|integer|exists:branches,id',
            'to_branch_id'               => 'required|integer|exists:branches,id|different:from_branch_id',
            'notes'                      => 'nullable|string|max:1000',
            'items'                      => 'required|array|min:1',
            'items.*.product_id'         => 'required|integer|exists:products,id',
            'items.*.quantity_requested' => 'required|integer|min:1',
        ]);

        try {
            $this->inventoryService->requestTransfer(
                $validated,
                auth()->user()->employee_id ?? auth()->id()
            );
            return redirect()->route('transfers.index')
                ->with('success', 'Transfer request submitted. Stock has been reserved.');
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['transfer' => $e->getMessage()]);
        }
    }

    public function approve(Request $request, int $id)
    {
        $this->authorize('approve stock transfers');
        try {
            $this->inventoryService->approveTransfer($id, auth()->user()->employee_id ?? auth()->id(), auth()->user());
            return back()->with('success', 'Transfer approved and ready for dispatch.');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['transfer' => $e->getMessage()]);
        }
    }

    public function dispatch(int $id)
    {
        $this->authorize('approve stock transfers');
        try {
            $this->inventoryService->dispatchTransfer($id, auth()->user());
            return back()->with('success', 'Transfer marked as in transit.');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['transfer' => $e->getMessage()]);
        }
    }

    public function reject(Request $request, int $id)
    {
        $this->authorize('approve stock transfers');
        $validated = $request->validate(['reason' => 'required|string|max:500']);
        try {
            $this->inventoryService->rejectTransfer($id, $validated['reason'], auth()->user());
            return back()->with('success', 'Transfer rejected. Reserved stock released.');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['transfer' => $e->getMessage()]);
        }
    }

    public function receive(int $id)
    {
        $this->authorize('receive stock transfers');
        try {
            $this->inventoryService->receiveTransfer($id, auth()->user());
            return back()->with('success', 'Transfer received. Inventory updated at destination branch.');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['transfer' => $e->getMessage()]);
        }
    }

    public function cancel(int $id)
    {
        $this->authorize('create stock transfers');
        try {
            $this->inventoryService->cancelTransfer($id, auth()->user());
            return back()->with('success', 'Transfer cancelled. Reserved stock released.');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['transfer' => $e->getMessage()]);
        }
    }
}
