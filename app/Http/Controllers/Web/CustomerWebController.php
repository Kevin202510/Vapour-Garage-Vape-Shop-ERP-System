<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CustomerService;
use App\Services\BranchService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class CustomerWebController extends Controller
{
    use AuthorizesRequests;
    public function __construct(
        protected CustomerService $customerService,
        protected BranchService   $branchService,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'branch_id', 'tier']);
        $user    = auth()->user();
        $effectiveBranchId = $user->isSuperAdmin()
            ? (request()->has('branch_id') ? (request('branch_id') ?: null) : $user->activeBranchId())
            : $user->branch_id;
        if (! $user->isSuperAdmin()) { $filters['branch_id'] = $user->branch_id; }
        elseif ($effectiveBranchId)  { $filters['branch_id'] = $effectiveBranchId; }
        else                         { unset($filters['branch_id']); }
        if (($filters['tier'] ?? 'all') === 'all') unset($filters['tier']);

        $customers  = $this->customerService->list($filters, 20);
        $branches   = $this->branchService->allActive();
        $activeBranchLabel = $effectiveBranchId
            ? ($branches->firstWhere('id', $effectiveBranchId)?->name ?? 'Selected Branch')
            : 'All Branches';

        $totalCount = \App\Models\Customer::when($effectiveBranchId, fn ($q) => $q->where('registered_branch_id', $effectiveBranchId))->count();
        $loyalCount = \App\Models\Customer::when($effectiveBranchId, fn ($q) => $q->where('registered_branch_id', $effectiveBranchId))->whereIn('tier', ['gold', 'platinum'])->count();
        $avgPoints  = \App\Models\Customer::when($effectiveBranchId, fn ($q) => $q->where('registered_branch_id', $effectiveBranchId))->avg('loyalty_points') ?? 0;

        return view('pages.customers.index', compact('customers', 'branches', 'totalCount', 'loyalCount', 'avgPoints', 'effectiveBranchId', 'activeBranchLabel'));
    }

    public function create()
    {
        $branches = $this->branchService->allActive();
        return view('pages.customers.form', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'           => 'required|string|max:100',
            'last_name'            => 'required|string|max:100',
            'email'                => 'nullable|email|max:255',
            'phone'                => 'nullable|string|max:20',
            'birth_date'           => 'nullable|date|before:-18 years',
            'registered_branch_id' => 'nullable|exists:branches,id',
        ]);
        $this->customerService->create($validated);
        return redirect()->route('customers.index')->with('success', 'Customer registered.');
    }

    public function show(int $id)
    {
        $customer = $this->customerService->findOrFail($id);
        return view('pages.customers.show', compact('customer'));
    }

    public function edit(int $id)
    {
        $customer = $this->customerService->findOrFail($id);
        $branches = $this->branchService->allActive();
        return view('pages.customers.form', compact('customer', 'branches'));
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:100',
            'last_name'  => 'sometimes|string|max:100',
            'email'      => 'nullable|email|max:255',
            'phone'      => 'nullable|string|max:20',
            'birth_date' => 'nullable|date|before:-18 years',
        ]);
        $this->customerService->update($id, $validated);
        return redirect()->route('customers.index')->with('success', 'Customer updated.');
    }

    public function destroy(int $id)
    {
        $this->customerService->delete($id);
        return redirect()->route('customers.index')->with('success', 'Customer deleted.');
    }
}
