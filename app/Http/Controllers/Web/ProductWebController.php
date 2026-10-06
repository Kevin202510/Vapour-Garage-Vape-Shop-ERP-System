<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Services\ProductCategoryService;
use App\Services\SupplierService;
use App\Contracts\Repositories\BranchInventoryRepositoryInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

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

    public function export(Request $request)
    {
        $this->authorize('view products');

        $filters = $request->only(['search', 'category_id', 'supplier_id', 'filter']);
        if (($filters['filter'] ?? 'all') === 'archived') {
            $filters['is_active'] = false;
        } elseif (($filters['filter'] ?? 'all') === 'low_stock') {
            $filters['is_active'] = true;
            $filters['low_stock'] = true;
        } else {
            $filters['is_active'] = true;
        }

        $products = $this->productService->list($filters, 100000);
        $filename = 'products-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($products) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['SKU', 'Name', 'Category', 'Supplier', 'Unit Cost', 'Selling Price', 'Tax Rate %', 'Reorder Level', 'Status']);
            foreach ($products as $product) {
                fputcsv($handle, [
                    $product->sku,
                    $product->name,
                    $product->category->name ?? 'Uncategorised',
                    $product->supplier->company_name ?? 'N/A',
                    number_format($product->unit_cost, 2),
                    number_format($product->selling_price, 2),
                    number_format($product->tax_rate, 2),
                    $product->reorder_level,
                    $product->is_active ? 'Active' : 'Archived',
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function create()
    {
        $categories = $this->categoryService->all();
        $suppliers  = $this->supplierService->allActive();
        return view('pages.products.form', compact('categories', 'suppliers'));
    }

    public function importForm()
    {
        $this->authorize('create products');

        return view('pages.products.import');
    }

    public function importTemplate()
    {
        $this->authorize('create products');

        $filename = 'products-import-template.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ProductService::importTemplateHeaders());
            fputcsv($handle, ProductService::importTemplateExampleRow());
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function import(Request $request)
    {
        $this->authorize('create products');

        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120',
        ]);

        try {
            $rows = $this->parseImportFile($request->file('file'));
        } catch (\Throwable $e) {
            return back()->withErrors(['file' => 'Could not read that file: ' . $e->getMessage()]);
        }

        if (empty($rows)) {
            return back()->withErrors(['file' => 'The file has no data rows to import.']);
        }

        $result = $this->productService->import($rows, auth()->user()->employee_id, $request->ip());

        return redirect()->route('products.import.form')->with('importResult', $result);
    }

    /**
     * Parse an uploaded CSV or Excel file into rows keyed by canonical
     * product field name (see ProductService::normalizeImportHeader()).
     * Blank rows are skipped; unrecognised columns are ignored.
     */
    private function parseImportFile(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        $table = in_array($extension, ['xlsx', 'xls'], true)
            ? $this->readSpreadsheet($file)
            : $this->readCsv($file);

        if (empty($table)) {
            return [];
        }

        $headerRow = array_shift($table);
        $fields    = array_map(
            fn ($header) => ProductService::normalizeImportHeader((string) $header),
            $headerRow
        );

        $rows = [];
        foreach ($table as $line) {
            if (count(array_filter($line, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue; // fully blank row
            }

            $row = [];
            foreach ($fields as $i => $field) {
                if ($field !== null) {
                    $row[$field] = $line[$i] ?? null;
                }
            }
            $rows[] = $row;
        }

        return $rows;
    }

    private function readCsv(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        $rows   = [];
        while (($line = fgetcsv($handle)) !== false) {
            $rows[] = $line;
        }
        fclose($handle);

        return $rows;
    }

    private function readSpreadsheet(UploadedFile $file): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet       = $spreadsheet->getActiveSheet();

        return $sheet->toArray(null, true, true, false);
    }

    public function store(Request $request)
    {
        $this->authorize('create products');

        $validated = $request->validate([
            'sku'                       => 'required|string|max:50|unique:products,sku',
            'name'                      => 'required|string|max:255',
            'description'               => 'nullable|string',
            'image'                     => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
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

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }

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
        $this->authorize('edit products');

        $product   = $this->productService->findOrFail($id);
        $validated = $request->validate([
            'sku'                       => "sometimes|string|max:50|unique:products,sku,{$id}",
            'name'                      => 'sometimes|string|max:255',
            'description'               => 'nullable|string',
            'image'                     => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
            'remove_image'              => 'sometimes|boolean',
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

        if ($request->hasFile('image')) {
            // Delete old image before storing the new one
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        } elseif ($request->boolean('remove_image') && $product->image_path) {
            Storage::disk('public')->delete($product->image_path);
            $validated['image_path'] = null;
        }

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
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }
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
