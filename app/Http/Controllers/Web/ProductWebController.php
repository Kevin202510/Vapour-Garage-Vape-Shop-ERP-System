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
            $filters['is_active'] = false;   // archived = deactivated, not soft-deleted
        } elseif (($filters['filter'] ?? 'all') === 'low_stock') {
            $filters['is_active'] = true;
            $filters['low_stock'] = true;
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
            'is_new_arrival'            => 'sometimes|boolean',
            'is_best_seller'            => 'sometimes|boolean',
            'is_hot'                    => 'sometimes|boolean',
            'is_on_sale'                => 'sometimes|boolean',
            'sale_price'                => 'nullable|numeric|min:0',
            'is_wholesale'              => 'sometimes|boolean',
            'min_order_qty'             => 'nullable|integer|min:1',
        ]);

        $validated['requires_age_verification'] = $request->boolean('requires_age_verification');
        $validated['is_active']                 = $request->boolean('is_active');
        $validated['is_new_arrival']            = $request->boolean('is_new_arrival');
        $validated['is_best_seller']            = $request->boolean('is_best_seller');
        $validated['is_hot']                    = $request->boolean('is_hot');
        $validated['is_on_sale']                = $request->boolean('is_on_sale');
        $validated['is_wholesale']              = $request->boolean('is_wholesale');
        if (! $validated['is_on_sale'])  $validated['sale_price']    = null;
        if (! $validated['is_wholesale']) $validated['min_order_qty'] = null;

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
            'is_new_arrival'            => 'sometimes|boolean',
            'is_best_seller'            => 'sometimes|boolean',
            'is_hot'                    => 'sometimes|boolean',
            'is_on_sale'                => 'sometimes|boolean',
            'sale_price'                => 'nullable|numeric|min:0',
            'is_wholesale'              => 'sometimes|boolean',
            'min_order_qty'             => 'nullable|integer|min:1',
        ]);

        $validated['requires_age_verification'] = $request->boolean('requires_age_verification');
        $validated['is_active']                 = $request->boolean('is_active');
        $validated['is_new_arrival']            = $request->boolean('is_new_arrival');
        $validated['is_best_seller']            = $request->boolean('is_best_seller');
        $validated['is_hot']                    = $request->boolean('is_hot');
        $validated['is_on_sale']                = $request->boolean('is_on_sale');
        $validated['is_wholesale']              = $request->boolean('is_wholesale');
        if (! $validated['is_on_sale'])   $validated['sale_price']    = null;
        if (! $validated['is_wholesale']) $validated['min_order_qty'] = null;

        $this->productService->update($id, $validated);

        return redirect()->route('products.index')
                         ->with('success', 'Product updated successfully.');
    }

    public function archive(int $id)
    {
        $this->authorize('delete products');
        $product = $this->productService->findOrFail($id);
        $this->productService->update($id, ['is_active' => false]);
        return back()->with('success', "\"{$product->name}\" has been archived.");
    }

    public function restore(int $id)
    {
        $this->authorize('edit products');
        $product = $this->productService->findOrFail($id);
        $this->productService->update($id, ['is_active' => true]);
        return back()->with('success', "\"{$product->name}\" has been restored and is now active.");
    }

    public function destroy(int $id)
    {
        $this->authorize('delete products');
        $product = $this->productService->findOrFail($id);
        $this->productService->delete($id);
        return redirect()->route('products.index', ['filter' => 'archived'])
                         ->with('success', "\"{$product->name}\" permanently deleted.");
    }

    private function getLowStockCount(): int
    {
        return \Illuminate\Support\Facades\DB::table('branch_inventory')
            ->join('products', 'branch_inventory.product_id', '=', 'products.id')
            ->whereNull('products.deleted_at')
            ->where('products.is_active', true)
            ->whereRaw('(branch_inventory.quantity_on_hand - branch_inventory.quantity_reserved) <= products.reorder_level')
            ->distinct('products.id')
            ->count('products.id');
    }
}
