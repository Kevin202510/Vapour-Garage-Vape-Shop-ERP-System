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
        if (($filters['tier'] ?? 'all') === 'all') unset($filters['tier']);

        $customers  = $this->customerService->list($filters, 20);
        $totalCount = \App\Models\Customer::count();
        $loyalCount = \App\Models\Customer::whereIn('tier', ['gold', 'platinum'])->count();
        $avgPoints  = \App\Models\Customer::avg('loyalty_points') ?? 0;

        return view('pages.customers.index', compact('customers', 'totalCount', 'loyalCount', 'avgPoints'));
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
