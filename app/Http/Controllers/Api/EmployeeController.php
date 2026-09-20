<?php

namespace App\Http\Controllers\Api;

use App\Concerns\EnforcesBranchAccess;
use App\Http\Controllers\Controller;
use App\Services\EmployeeService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    use AuthorizesRequests, EnforcesBranchAccess;
    public function __construct(protected EmployeeService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('view employees');
        $filters = $request->only(['branch_id','department_id','role','employment_type','is_active','search','with_trashed','sort_by','sort_order']);

        if (! $request->user()->isSuperAdmin()) {
            $filters['branch_id'] = $request->user()->branch_id;
        }

        return response()->json($this->service->list($filters, $request->integer('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create employees');
        $validated = $request->validate([
            'branch_id'       => ['required','integer','exists:branches,id'],
            'department_id'   => ['nullable','integer','exists:departments,id'],
            'employee_code'   => ['required','string','max:20'],
            'first_name'      => ['required','string','max:100'],
            'last_name'       => ['required','string','max:100'],
            'email'           => ['nullable','email','max:255'],
            'phone'           => ['nullable','string','max:20'],
            'role'            => ['required','string','in:cashier,supervisor,branch_manager,inventory_clerk,admin'],
            'employment_type' => ['required','in:full_time,part_time,contractual'],
            'base_salary'     => ['required','numeric','min:0'],
            'hire_date'       => ['required','date'],
        ]);
        $employee = $this->service->create($validated, $request->user()->employee_id, $request->ip());
        return response()->json(['message' => 'Employee created.', 'data' => $employee], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $this->authorize('view employees');
        $employee = $this->service->findOrFail($id);
        $this->assertBranchAccess($request->user(), $employee->branch_id);
        return response()->json(['data' => $employee]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $this->authorize('edit employees');
        $validated = $request->validate([
            'branch_id'       => ['sometimes','integer','exists:branches,id'],
            'department_id'   => ['sometimes','nullable','integer','exists:departments,id'],
            'first_name'      => ['sometimes','string','max:100'],
            'last_name'       => ['sometimes','string','max:100'],
            'email'           => ['sometimes','nullable','email','max:255'],
            'phone'           => ['sometimes','nullable','string','max:20'],
            'role'            => ['sometimes','string','in:cashier,supervisor,branch_manager,inventory_clerk,admin'],
            'employment_type' => ['sometimes','in:full_time,part_time,contractual'],
            'base_salary'     => ['sometimes','numeric','min:0'],
            'hire_date'       => ['sometimes','date'],
            'termination_date'=> ['sometimes','nullable','date'],
            'is_active'       => ['sometimes','boolean'],
        ]);
        $employee = $this->service->update($id, $validated, $request->user()->employee_id, $request->ip(), $request->user());
        return response()->json(['message' => 'Employee updated.', 'data' => $employee]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->authorize('delete employees');
        $this->service->delete($id, $request->user()->employee_id, $request->ip(), $request->user());
        return response()->json(['message' => 'Employee deleted.']);
    }

    public function restore(int $id): JsonResponse
    {
        $this->authorize('delete employees');
        $this->service->restore($id);
        return response()->json(['message' => 'Employee restored.']);
    }
}
