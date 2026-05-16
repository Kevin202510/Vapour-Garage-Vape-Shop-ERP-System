<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\EmployeeService;
use App\Services\BranchService;
use App\Services\DepartmentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeWebController extends Controller
{
    use AuthorizesRequests;
    public function __construct(
        protected EmployeeService   $employeeService,
        protected BranchService     $branchService,
        protected DepartmentService $departmentService,
    ) {}

    public function index(Request $request)
    {
        $user    = auth()->user();
        $filters = $request->only(['search', 'branch_id', 'role', 'is_active']);

        $effectiveBranchId = $user->isSuperAdmin()
            ? (request()->has('branch_id') ? (request('branch_id') ?: null) : $user->activeBranchId())
            : $user->branch_id;
        if (! $user->isSuperAdmin()) { $filters['branch_id'] = $user->branch_id; }
        elseif ($effectiveBranchId)  { $filters['branch_id'] = $effectiveBranchId; }
        else                         { unset($filters['branch_id']); }
        $filters['is_active'] = true;

        $employees = $this->employeeService->list($filters, 20);
        $branches  = $this->branchService->allActive();
        $activeBranchLabel = $effectiveBranchId
            ? ($branches->firstWhere('id', $effectiveBranchId)?->name ?? 'Selected Branch')
            : 'All Branches';

        $totalActive  = DB::table('employees')
            ->when($effectiveBranchId, fn ($q) => $q->where('branch_id', $effectiveBranchId))
            ->where('is_active', true)->count();
        $presentToday = DB::table('attendances')
            ->when($effectiveBranchId, fn ($q) => $q->where('branch_id', $effectiveBranchId))
            ->where('work_date', now()->toDateString())->where('status', 'present')->count();
        $onLeave      = DB::table('attendances')
            ->when($effectiveBranchId, fn ($q) => $q->where('branch_id', $effectiveBranchId))
            ->where('work_date', now()->toDateString())->where('status', 'leave')->count();

        return view('pages.employees.index', compact('employees', 'branches', 'totalActive', 'presentToday', 'onLeave', 'effectiveBranchId', 'activeBranchLabel'));
    }

    public function create()
    {
        $branches    = $this->branchService->allActive();
        $departments = $this->departmentService->allWithCount();
        return view('pages.employees.form', compact('branches', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'      => 'required|string|max:100',
            'last_name'       => 'required|string|max:100',
            'employee_code'   => 'required|string|max:20|unique:employees,employee_code',
            'email'           => 'nullable|email|max:255',
            'phone'           => 'nullable|string|max:20',
            'branch_id'       => 'required|integer|exists:branches,id',
            'department_id'   => 'nullable|integer|exists:departments,id',
            'role'            => 'required|string',
            'employment_type' => 'required|in:full_time,part_time,contractual',
            'base_salary'     => 'required|numeric|min:0',
            'hire_date'       => 'required|date',
        ]);
        $validated['is_active'] = true;

        $this->employeeService->create($validated);
        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }

    public function show(int $id)
    {
        $employee = $this->employeeService->findOrFail($id);
        return view('pages.employees.show', compact('employee'));
    }

    public function edit(int $id)
    {
        $employee    = $this->employeeService->findOrFail($id);
        $branches    = $this->branchService->allActive();
        $departments = $this->departmentService->allWithCount();
        return view('pages.employees.form', compact('employee', 'branches', 'departments'));
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'first_name'       => 'sometimes|string|max:100',
            'last_name'        => 'sometimes|string|max:100',
            'email'            => 'nullable|email|max:255',
            'phone'            => 'nullable|string|max:20',
            'branch_id'        => 'sometimes|integer|exists:branches,id',
            'department_id'    => 'nullable|integer|exists:departments,id',
            'role'             => 'sometimes|string',
            'employment_type'  => 'sometimes|in:full_time,part_time,contractual',
            'base_salary'      => 'sometimes|numeric|min:0',
            'hire_date'        => 'sometimes|date',
            'termination_date' => 'nullable|date',
        ]);
        $validated['is_active'] = $request->boolean('is_active');

        $this->employeeService->update($id, $validated);
        return redirect()->route('employees.index')->with('success', 'Employee updated.');
    }

    public function destroy(int $id)
    {
        $this->employeeService->delete($id);
        return redirect()->route('employees.index')->with('success', 'Employee removed.');
    }
}
