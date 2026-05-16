<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\SupplierService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class SupplierWebController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected SupplierService $supplierService) {}

    public function index(Request $request)
    {
        $this->authorize('view suppliers');

        $filters = $request->only(['search', 'is_active']);

        // Default: only active; toggle with ?is_active=all
        if (($filters['is_active'] ?? 'active') === 'all') {
            $filters['with_trashed'] = true;
            unset($filters['is_active']);
        } elseif (($filters['is_active'] ?? 'active') === 'inactive') {
            $filters['is_active'] = false;
        } else {
            $filters['is_active'] = true;
        }

        $suppliers   = $this->supplierService->list($filters, 20);
        $totalActive = \App\Models\Supplier::where('is_active', true)->count();
        $totalAll    = \App\Models\Supplier::withTrashed()->count();

        return view('pages.suppliers.index', compact('suppliers', 'totalActive', 'totalAll'));
    }

    public function create()
    {
        $this->authorize('create suppliers');
        return view('pages.suppliers.form');
    }

    public function store(Request $request)
    {
        $this->authorize('create suppliers');

        $validated = $request->validate([
            'company_name'   => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string|max:1000',
            'tin'            => 'nullable|string|max:20',
        ]);
        $validated['is_active'] = true;

        $supplier = $this->supplierService->create($validated);
        return redirect()->route('suppliers.show', $supplier->id)
            ->with('success', "\"{$supplier->company_name}\" added as a supplier.");
    }

    public function show(int $id)
    {
        $this->authorize('view suppliers');
        $supplier = $this->supplierService->findOrFail($id);
        $supplier->load(['products' => fn ($q) => $q->where('is_active', true)->withCount(['salesItems'])]);

        $purchaseOrders = $supplier->purchaseOrders()
            ->with('branch')
            ->latest('order_date')
            ->paginate(10);

        $totalOrders   = $supplier->purchaseOrders()->count();
        $totalSpent    = $supplier->purchaseOrders()->where('status', 'received')->sum('total_amount');
        $productCount  = $supplier->products()->where('is_active', true)->count();

        return view('pages.suppliers.show', compact(
            'supplier', 'purchaseOrders', 'totalOrders', 'totalSpent', 'productCount'
        ));
    }

    public function edit(int $id)
    {
        $this->authorize('edit suppliers');
        $supplier = $this->supplierService->findOrFail($id);
        return view('pages.suppliers.form', compact('supplier'));
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('edit suppliers');

        $validated = $request->validate([
            'company_name'   => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string|max:1000',
            'tin'            => 'nullable|string|max:20',
            'is_active'      => 'sometimes|boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active');

        $supplier = $this->supplierService->update($id, $validated);
        return redirect()->route('suppliers.show', $id)
            ->with('success', "\"{$supplier->company_name}\" updated.");
    }

    public function destroy(int $id)
    {
        $this->authorize('delete suppliers');
        $supplier = $this->supplierService->findOrFail($id);
        $this->supplierService->delete($id);
        return redirect()->route('suppliers.index')
            ->with('success', "\"{$supplier->company_name}\" has been deactivated.");
    }
}
