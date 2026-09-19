<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InventoryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    use AuthorizesRequests;
    public function __construct(protected InventoryService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('view inventory');
        $request->validate(['branch_id' => ['required','integer','exists:branches,id']]);
        $branchId = $request->integer('branch_id');

        if (! $request->user()->isSuperAdmin() && $branchId != $request->user()->branch_id) {
            abort(403, 'You may only view inventory for your own branch.');
        }

        return response()->json(['data' => $this->service->getByBranch($branchId)]);
    }

    public function lowStock(Request $request): JsonResponse
    {
        $this->authorize('view inventory');
        $request->validate(['branch_id' => ['required','integer','exists:branches,id']]);
        $branchId = $request->integer('branch_id');

        if (! $request->user()->isSuperAdmin() && $branchId != $request->user()->branch_id) {
            abort(403, 'You may only view inventory for your own branch.');
        }

        return response()->json(['data' => $this->service->getLowStock($branchId)]);
    }

    public function adjust(Request $request): JsonResponse
    {
        $this->authorize('adjust inventory');
        $validated = $request->validate([
            'branch_id'  => ['required','integer','exists:branches,id'],
            'product_id' => ['required','integer','exists:products,id'],
            'quantity'   => ['required','integer','min:0'],
        ]);

        if (! $request->user()->isSuperAdmin() && $validated['branch_id'] != $request->user()->branch_id) {
            abort(403, 'You may only adjust inventory for your own branch.');
        }

        $record = $this->service->adjust(
            $validated['branch_id'],
            $validated['product_id'],
            $validated['quantity'],
            $request->user()->employee_id,
            $request->ip()
        );
        return response()->json(['message' => 'Stock adjusted.', 'data' => $record]);
    }

    public function requestTransfer(Request $request): JsonResponse
    {
        $this->authorize('create stock transfers');
        $validated = $request->validate([
            'from_branch_id'         => ['required','integer','exists:branches,id'],
            'to_branch_id'           => ['required','integer','exists:branches,id','different:from_branch_id'],
            'notes'                  => ['nullable','string'],
            'items'                  => ['required','array','min:1'],
            'items.*.product_id'     => ['required','integer','exists:products,id'],
            'items.*.quantity_requested' => ['required','integer','min:1'],
            'items.*.unit_cost'      => ['sometimes','numeric','min:0'],
        ]);
        $transfer = $this->service->requestTransfer($validated, $request->user()->employee_id ?? $request->user()->id);
        return response()->json(['message' => 'Transfer requested.', 'data' => $transfer], 201);
    }

    public function approveTransfer(Request $request, int $id): JsonResponse
    {
        $this->authorize('approve stock transfers');
        $transfer = $this->service->approveTransfer($id, $request->user()->employee_id ?? $request->user()->id);
        return response()->json(['message' => 'Transfer approved.', 'data' => $transfer]);
    }

    public function receiveTransfer(int $id): JsonResponse
    {
        $this->authorize('receive stock transfers');
        $transfer = $this->service->receiveTransfer($id);
        return response()->json(['message' => 'Transfer received. Inventory updated.', 'data' => $transfer]);
    }

    public function rejectTransfer(Request $request, int $id): JsonResponse
    {
        $this->authorize('approve stock transfers');
        $validated = $request->validate(['reason' => ['required','string','max:500']]);
        $transfer  = $this->service->rejectTransfer($id, $validated['reason']);
        return response()->json(['message' => 'Transfer rejected.', 'data' => $transfer]);
    }
}
