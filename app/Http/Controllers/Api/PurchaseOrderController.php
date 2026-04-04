<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PurchaseOrderService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    use AuthorizesRequests;
    public function __construct(protected PurchaseOrderService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('view purchase orders');
        $filters = $request->only(['branch_id','supplier_id','status','date_from','date_to','search','with_trashed','sort_by','sort_order']);
        if ($request->user()->isBranchManager() && ! $request->user()->isSuperAdmin()) {
            $filters['branch_id'] = $request->user()->branch_id;
        }
        return response()->json($this->service->list($filters, $request->integer('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create purchase orders');
        $validated = $request->validate([
            'supplier_id'               => ['required','integer','exists:suppliers,id'],
            'branch_id'                 => ['required','integer','exists:branches,id'],
            'order_date'                => ['required','date'],
            'expected_date'             => ['nullable','date','after_or_equal:order_date'],
            'notes'                     => ['nullable','string'],
            'items'                     => ['required','array','min:1'],
            'items.*.product_id'        => ['required','integer','exists:products,id'],
            'items.*.quantity_ordered'  => ['required','integer','min:1'],
            'items.*.unit_cost'         => ['required','numeric','min:0'],
        ]);
        $validated['created_by'] = $request->user()->employee_id ?? $request->user()->id;
        return response()->json(['message' => 'Purchase order created.', 'data' => $this->service->create($validated)], 201);
    }

    public function show(int $id): JsonResponse
    {
        $this->authorize('view purchase orders');
        return response()->json(['data' => $this->service->findOrFail($id)]);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $this->authorize('approve purchase orders');
        $po = $this->service->approve($id, $request->user()->employee_id ?? $request->user()->id);
        return response()->json(['message' => 'Purchase order approved.', 'data' => $po]);
    }

    public function receive(Request $request, int $id): JsonResponse
    {
        $this->authorize('receive purchase orders');
        $validated = $request->validate([
            'items'                        => ['required','array','min:1'],
            'items.*.product_id'           => ['required','integer','exists:products,id'],
            'items.*.quantity_received'    => ['required','integer','min:0'],
        ]);
        $po = $this->service->receive($id, $validated['items']);
        return response()->json(['message' => 'Items received. Inventory updated.', 'data' => $po]);
    }

    public function cancel(int $id): JsonResponse
    {
        $this->authorize('cancel purchase orders');
        $po = $this->service->cancel($id);
        return response()->json(['message' => 'Purchase order cancelled.', 'data' => $po]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorize('cancel purchase orders');
        $this->service->delete($id);
        return response()->json(['message' => 'Purchase order deleted.']);
    }
}
