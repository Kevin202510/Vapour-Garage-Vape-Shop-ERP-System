<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Concerns\ResolvesActiveBranch;
use App\Http\Controllers\Controller;
use App\Services\CustomerService;
use App\Services\BranchService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class CustomerWebController extends Controller
{
    use AuthorizesRequests, ResolvesActiveBranch;

    public function __construct(
        protected CustomerService $customerService,
        protected BranchService   $branchService,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'branch_id', 'tier']);
        $user    = auth()->user();
        $effectiveBranchId = $this->resolveEffectiveBranchId($user);
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

        return view('pages.customers.index', compact(
            'customers', 'branches', 'totalCount', 'loyalCount', 'avgPoints',
            'effectiveBranchId', 'activeBranchLabel'
        ));
    }

    public function export(Request $request)
    {
        $this->authorize('view customers');

        $filters = $request->only(['search', 'branch_id', 'tier']);
        $user    = auth()->user();
        $effectiveBranchId = $this->resolveEffectiveBranchId($user);
        if (! $user->isSuperAdmin()) { $filters['branch_id'] = $user->branch_id; }
        elseif ($effectiveBranchId)  { $filters['branch_id'] = $effectiveBranchId; }
        else                         { unset($filters['branch_id']); }
        if (($filters['tier'] ?? 'all') === 'all') unset($filters['tier']);

        $customers = $this->customerService->list($filters, 100000);
        $filename  = 'customers-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($customers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Email', 'Phone', 'Tier', 'Loyalty Points', 'Registered Branch', 'Registered Date']);
            foreach ($customers as $customer) {
                fputcsv($handle, [
                    $customer->full_name,
                    $customer->email,
                    $customer->phone,
                    ucfirst($customer->tier),
                    $customer->loyalty_points,
                    $customer->registeredBranch->name ?? 'N/A',
                    optional($customer->created_at)->format('Y-m-d'),
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function create()
    {
        $branches = $this->branchService->allActive();
        return view('pages.customers.form', compact('branches'));
    }

    public function store(Request $request)
    {
        $this->authorize('create customers');

        $validated = $request->validate([
            'first_name'           => 'required|string|max:100',
            'last_name'            => 'required|string|max:100',
            'email'                => 'nullable|email|max:255|unique:customers,email',
            'phone'                => 'nullable|string|max:20',
            'birth_date'           => 'nullable|date|before:-18 years',
            'registered_branch_id' => 'nullable|exists:branches,id',
        ]);

        $customer = $this->customerService->create($validated);
        return redirect()->route('customers.show', $customer)
            ->with('success', "{$customer->full_name} registered as a loyalty member.");
    }

    public function show(int $id)
    {
        $customer = $this->customerService->findOrFail($id);
        $customer->load(['registeredBranch']);

        $transactions = $customer->transactions()
            ->with(['branch', 'items'])
            ->latest('transaction_date')
            ->paginate(10);

        $totalSpent = (float) $customer->transactions()->where('status', 'completed')->sum('total_amount');
        $visitCount = $customer->transactions()->where('status', 'completed')->count();

        return view('pages.customers.show', compact(
            'customer', 'transactions', 'totalSpent', 'visitCount'
        ));
    }

    public function edit(int $id)
    {
        $customer = $this->customerService->findOrFail($id);
        $branches = $this->branchService->allActive();
        return view('pages.customers.form', compact('customer', 'branches'));
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('edit customers');

        $validated = $request->validate([
            'first_name'           => 'sometimes|string|max:100',
            'last_name'            => 'sometimes|string|max:100',
            'email'                => "nullable|email|max:255|unique:customers,email,{$id}",
            'phone'                => 'nullable|string|max:20',
            'birth_date'           => 'nullable|date|before:-18 years',
            'registered_branch_id' => 'nullable|exists:branches,id',
        ]);

        $customer = $this->customerService->update($id, $validated);
        return redirect()->route('customers.show', $id)
            ->with('success', "{$customer->full_name}'s profile updated.");
    }

    public function destroy(int $id)
    {
        $this->authorize('delete customers');
        $customer = $this->customerService->findOrFail($id);
        $this->customerService->delete($id);
        return redirect()->route('customers.index')
            ->with('success', "{$customer->full_name} has been removed.");
    }

    public function addPoints(Request $request, int $id)
    {
        $this->authorize('edit customers');

        $validated = $request->validate([
            'points' => 'required|integer|min:1|max:100000',
        ]);

        $customer = $this->customerService->addPoints($id, $validated['points']);
        return redirect()->route('customers.show', $id)
            ->with('success', number_format($validated['points']) . ' points added — now ' . ucfirst($customer->tier) . ' tier with ' . number_format($customer->loyalty_points) . ' pts.');
    }

    public function deductPoints(Request $request, int $id)
    {
        $this->authorize('edit customers');

        $validated = $request->validate([
            'points' => 'required|integer|min:1|max:100000',
        ]);

        try {
            $customer = $this->customerService->deductPoints($id, $validated['points']);
            return redirect()->route('customers.show', $id)
                ->with('success', number_format($validated['points']) . ' points deducted — ' . number_format($customer->loyalty_points) . ' pts remaining.');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['deduct' => $e->getMessage()]);
        }
    }
}
