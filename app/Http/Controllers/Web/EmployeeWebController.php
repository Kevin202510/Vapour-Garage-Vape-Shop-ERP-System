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
            ->whereNull('deleted_at')
            ->when($effectiveBranchId, fn ($q) => $q->where('branch_id', $effectiveBranchId))
            ->where('is_active', true)->count();
        $presentToday = DB::table('attendances')
            ->join('employees', 'attendances.employee_id', '=', 'employees.id')
            ->whereNull('employees.deleted_at')
            ->when($effectiveBranchId, fn ($q) => $q->where('employees.branch_id', $effectiveBranchId))
            ->where('attendances.work_date', now()->toDateString())->where('attendances.status', 'present')->count();
        $onLeave      = DB::table('attendances')
            ->join('employees', 'attendances.employee_id', '=', 'employees.id')
            ->whereNull('employees.deleted_at')
            ->when($effectiveBranchId, fn ($q) => $q->where('employees.branch_id', $effectiveBranchId))
            ->where('attendances.work_date', now()->toDateString())->where('attendances.status', 'leave')->count();

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

        // Validate login account fields if admin opted to create one
        if ($request->boolean('create_account')) {
            $request->validate([
                'login_email'                  => 'required|email|max:255|unique:users,email',
                'login_password'               => 'required|string|min:8|confirmed',
                'login_password_confirmation'  => 'required',
            ]);
        }

        $validated['is_active'] = true;
        $employee = $this->employeeService->create($validated);

        if ($request->boolean('create_account')) {
            $loginEmail    = $request->input('login_email');
            $loginPassword = $request->input('login_password');

            $user = \App\Models\User::create([
                'name'              => $employee->full_name,
                'email'             => $loginEmail,
                'password'          => $loginPassword,  // cast: 'hashed' handles bcrypt
                'employee_id'       => $employee->id,
                'branch_id'         => $employee->branch_id,
                'is_active'         => true,
                'email_verified_at' => now(),
            ]);

            // Map employee role → Spatie permission role
            $roleMap = [
                'branch_manager'  => 'branch_manager',
                'supervisor'      => 'supervisor',
                'cashier'         => 'cashier',
                'inventory_clerk' => 'inventory_clerk',
            ];
            if (isset($roleMap[$employee->role])) {
                $user->syncRoles([$roleMap[$employee->role]]);
            }

            // Flash once — admin must share these credentials with the employee
            session()->flash('new_account', [
                'name'     => $employee->full_name,
                'email'    => $loginEmail,
                'password' => $loginPassword,
                'role'     => ucwords(str_replace('_', ' ', $employee->role)),
            ]);
        }

        return redirect()->route('employees.index')
            ->with('success', "Employee {$employee->full_name} created successfully.");
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
