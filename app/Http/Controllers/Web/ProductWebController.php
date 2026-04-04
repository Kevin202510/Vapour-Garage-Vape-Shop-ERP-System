<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Services\ProductCategoryService;
use App\Services\SupplierService;
use App\Contracts\Repositories\BranchInventoryRepositoryInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ProductWebController extends Controller
{
    use AuthorizesRequests;
    public function __construct(
        protected ProductService                     $productService,
        protected ProductCategoryService             $categoryService,
        protected SupplierService                    $supplierService,
        protected BranchInventoryRepositoryInterface $inventoryRepo,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'category_id', 'supplier_id', 'filter']);

        if (($filters['filter'] ?? 'all') === 'archived') {
            $filters['is_active']    = false;
            $filters['with_trashed'] = true;
        } elseif (($filters['filter'] ?? 'all') === 'low_stock') {
            $filters['is_active'] = true;
        } else {
            $filters['is_active'] = true;
        }

        $products      = $this->productService->list($filters, 20);
        $categories    = $this->categoryService->all();
        $lowStockCount = $this->getLowStockCount();

        return view('pages.products.index', compact('products', 'categories', 'lowStockCount'));
    }

    public function create()
    {
        $categories = $this->categoryService->all();
        $suppliers  = $this->supplierService->allActive();
        return view('pages.products.form', compact('categories', 'suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku'                       => 'required|string|max:50|unique:products,sku',
            'name'                      => 'required|string|max:255',
            'description'               => 'nullable|string',
            'category_id'               => 'nullable|exists:product_categories,id',
            'supplier_id'               => 'nullable|exists:suppliers,id',
            'unit_cost'                 => 'required|numeric|min:0',
            'selling_price'             => 'required|numeric|min:0',
            'tax_rate'                  => 'nullable|numeric|min:0|max:100',
            'unit_of_measure'           => 'nullable|string|max:20',
            'reorder_level'             => 'nullable|integer|min:0',
            'requires_age_verification' => 'sometimes|boolean',
            'is_active'                 => 'sometimes|boolean',
        ]);

        $validated['requires_age_verification'] = $request->boolean('requires_age_verification');
        $validated['is_active']                 = $request->boolean('is_active');

        $this->productService->create($validated);

        return redirect()->route('products.index')
                         ->with('success', 'Product created successfully.');
    }

    public function show(int $id)
    {
        $product = $this->productService->findOrFail($id);
        return view('pages.products.show', compact('product'));
    }

    public function edit(int $id)
    {
        $product    = $this->productService->findOrFail($id);
        $categories = $this->categoryService->all();
        $suppliers  = $this->supplierService->allActive();
        return view('pages.products.form', compact('product', 'categories', 'suppliers'));
    }

    public function update(Request $request, int $id)
    {
        $product   = $this->productService->findOrFail($id);
        $validated = $request->validate([
            'sku'                       => "sometimes|string|max:50|unique:products,sku,{$id}",
            'name'                      => 'sometimes|string|max:255',
            'description'               => 'nullable|string',
            'category_id'               => 'nullable|exists:product_categories,id',
            'supplier_id'               => 'nullable|exists:suppliers,id',
            'unit_cost'                 => 'sometimes|numeric|min:0',
            'selling_price'             => 'sometimes|numeric|min:0',
            'tax_rate'                  => 'nullable|numeric|min:0|max:100',
            'unit_of_measure'           => 'nullable|string|max:20',
            'reorder_level'             => 'nullable|integer|min:0',
            'requires_age_verification' => 'sometimes|boolean',
            'is_active'                 => 'sometimes|boolean',
        ]);

        $validated['requires_age_verification'] = $request->boolean('requires_age_verification');
        $validated['is_active']                 = $request->boolean('is_active');

        $this->productService->update($id, $validated);

        return redirect()->route('products.index')
                         ->with('success', 'Product updated successfully.');
    }

    public function destroy(int $id)
    {
        $this->productService->delete($id);
        return redirect()->route('products.index')
                         ->with('success', 'Product deleted.');
    }

    private function getLowStockCount(): int
    {
        return \Illuminate\Support\Facades\DB::table('branch_inventory')
            ->join('products', 'branch_inventory.product_id', '=', 'products.id')
            ->whereRaw('(branch_inventory.quantity_on_hand - branch_inventory.quantity_reserved) <= products.reorder_level')
            ->where('products.is_active', true)
            ->distinct('products.id')
            ->count('products.id');
    }
}
