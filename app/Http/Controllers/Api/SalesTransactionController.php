<?php

namespace App\Http\Controllers\Api;

use App\Concerns\EnforcesBranchAccess;
use App\Http\Controllers\Controller;
use App\Services\SalesService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalesTransactionController extends Controller
{
    use AuthorizesRequests, EnforcesBranchAccess;
    public function __construct(protected SalesService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('view sales');
        $filters = $request->only(['branch_id','cashier_id','customer_id','status','payment_method','date_from','date_to','search','sort_by','sort_order']);

        if (! $request->user()->isSuperAdmin()) {
            $filters['branch_id'] = $request->user()->branch_id;
        }

        return response()->json($this->service->list($filters, $request->integer('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create sales');
        $validated = $request->validate([
            'branch_id'              => ['required','integer','exists:branches,id'],
            'cashier_id'             => ['required','integer','exists:employees,id'],
            'customer_id'            => ['nullable','integer','exists:customers,id'],
            'payment_method'         => ['required','in:cash,card,gcash,maya,bank_transfer,mixed'],
            'amount_tendered'        => ['required','numeric','min:0'],
            'idempotency_key'        => ['nullable','string','max:100'],
            'items'                  => ['required','array','min:1'],
            'items.*.product_id'     => ['required','integer','exists:products,id'],
            'items.*.quantity'       => ['required','integer','min:1'],
            'items.*.discount_id'    => ['nullable','integer','exists:discounts,id'],
        ]);

        // A cashier attributing a sale to a different branch/employee would
        // corrupt per-branch reporting and let them deplete another branch's
        // stock — non-super-admins always transact as themselves.
        if (! $request->user()->isSuperAdmin()) {
            $validated['branch_id']  = $request->user()->branch_id;
            $validated['cashier_id'] = $request->user()->employee_id;
        }

        $txn = $this->service->processTransaction($validated);
        return response()->json(['message' => 'Transaction completed.', 'data' => $txn], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $this->authorize('view sales');
        $txn = $this->service->findOrFail($id);
        $this->assertBranchAccess($request->user(), $txn->branch_id);
        return response()->json(['data' => $txn]);
    }

    public function void(Request $request, int $id): JsonResponse
    {
        $this->authorize('void sales');
        $validated = $request->validate(['reason' => ['required','string','max:500']]);
        $txn = $this->service->void($id, $validated['reason'], $request->user()->employee_id, $request->ip());
        return response()->json(['message' => 'Transaction voided.', 'data' => $txn]);
    }

    public function dailySummary(Request $request): JsonResponse
    {
        $this->authorize('view sales reports');
        $request->validate([
            'date'      => ['required','date'],
            'branch_id' => ['required','integer','exists:branches,id'],
        ]);
        $summary = $this->service->getDailySummary($request->date, $request->integer('branch_id'));
        return response()->json(['data' => $summary]);
    }
}
