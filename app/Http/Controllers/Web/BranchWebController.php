<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\BranchService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchWebController extends Controller
{
    use AuthorizesRequests;
    public function __construct(protected BranchService $branchService) {}

    public function index()
    {
        $branches = \App\Models\Branch::withCount(['employees' => fn ($q) => $q->where('is_active', true)])
            ->with(['salesTransactions' => fn ($q) => $q->whereDate('transaction_date', now())->where('status', 'completed')])
            ->get()
            ->map(function ($branch) {
                $branch->today_revenue    = $branch->salesTransactions->sum('total_amount');
                $branch->today_txn_count  = $branch->salesTransactions->count();
                return $branch;
            });

        return view('pages.branches.index', compact('branches'));
    }

    public function create()
    {
        return view('pages.branches.form');
    }

    public function store(Request $request)
    {
        $this->authorize('create branches');
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'address'        => 'required|string',
            'city'           => 'required|string|max:100',
            'contact_number' => 'nullable|string|max:20',
        ]);
        $validated['is_active'] = true;
        $this->branchService->create($validated);
        return redirect()->route('branches.index')->with('success', 'Branch created.');
    }

    public function show(int $id)
    {
        $branch = $this->branchService->findOrFail($id);

        // Eager-load relationships, excluding inventory rows whose product was deleted/archived.
        $branch->load([
            'inventory' => fn ($q) => $q->whereHas('product', fn ($p) => $p->where('is_active', true))
                                        ->with('product'),
            'employees' => fn ($q) => $q->where('is_active', true)
                                        ->with(['attendance' => fn ($a) => $a->where('work_date', now()->toDateString())]),
        ]);

        $branch->today_revenue   = $branch->salesTransactions()
            ->whereDate('transaction_date', today())->where('status', 'completed')->sum('total_amount');
        $branch->today_txn_count = $branch->salesTransactions()
            ->whereDate('transaction_date', today())->where('status', 'completed')->count();

        return view('pages.branches.show', compact('branch'));
    }

    public function edit(int $id)
    {
        $branch = $this->branchService->findOrFail($id);
        return view('pages.branches.form', compact('branch'));
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('edit branches');
        $validated = $request->validate([
            'name'           => 'sometimes|string|max:255',
            'address'        => 'sometimes|string',
            'city'           => 'sometimes|string|max:100',
            'contact_number' => 'nullable|string|max:20',
            'is_active'      => 'sometimes|boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        $this->branchService->update($id, $validated);
        return redirect()->route('branches.index')->with('success', 'Branch updated.');
    }

    public function destroy(int $id)
    {
        $this->authorize('delete branches');
        $this->branchService->delete($id);
        return redirect()->route('branches.index')->with('success', 'Branch deleted.');
    }
}
