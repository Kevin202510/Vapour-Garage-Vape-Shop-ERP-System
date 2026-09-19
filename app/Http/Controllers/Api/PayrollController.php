<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PayrollService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    use AuthorizesRequests;
    public function __construct(protected PayrollService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('view payroll');
        $filters = $request->only(['employee_id','branch_id','status','period_start','period_end','search','sort_by','sort_order']);
        if ($request->user()->isBranchManager() && ! $request->user()->isSuperAdmin()) {
            $filters['branch_id'] = $request->user()->branch_id;
        }
        return response()->json($this->service->list($filters, $request->integer('per_page', 15)));
    }

    public function show(int $id): JsonResponse
    {
        $this->authorize('view payroll');
        return response()->json(['data' => $this->service->findOrFail($id)]);
    }

    public function generate(Request $request): JsonResponse
    {
        $this->authorize('process payroll');
        $validated = $request->validate([
            'branch_id'    => ['required','integer','exists:branches,id'],
            'period_start' => ['required','date'],
            'period_end'   => ['required','date','after_or_equal:period_start'],
            'period_label' => ['required','string','max:100'],
        ]);
        $records = $this->service->generateForBranch(
            $validated['branch_id'],
            $validated['period_start'],
            $validated['period_end'],
            $validated['period_label'],
            $request->user()->employee_id,
            $request->ip()
        );
        return response()->json(['message' => count($records) . ' payroll records generated.', 'data' => $records], 201);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $this->authorize('approve payroll');
        $payroll = $this->service->approve($id, $request->user()->employee_id ?? $request->user()->id, $request->ip());
        return response()->json(['message' => 'Payroll approved.', 'data' => $payroll]);
    }

    public function release(Request $request, int $id): JsonResponse
    {
        $this->authorize('release payroll');
        $payroll = $this->service->release($id, $request->user()->employee_id, $request->ip());
        return response()->json(['message' => 'Payroll released.', 'data' => $payroll]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->authorize('process payroll');
        $this->service->delete($id, $request->user()->employee_id, $request->ip());
        return response()->json(['message' => 'Payroll record deleted.']);
    }

    public function employeeSlips(int $employeeId): JsonResponse
    {
        $this->authorize('view payroll');
        return response()->json(['data' => $this->service->getByEmployee($employeeId)]);
    }
}
