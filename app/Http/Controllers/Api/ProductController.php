<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use AuthorizesRequests;
    public function __construct(protected ProductService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('view products');
        return response()->json($this->service->list(
            $request->only(['category_id','supplier_id','is_active','search','with_trashed','sort_by','sort_order','requires_age_verification']),
            $request->integer('per_page', 15)
        ));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create products');
        $validated = $request->validate([
            'sku'                       => ['required','string','max:50'],
            'name'                      => ['required','string','max:255'],
            'description'               => ['nullable','string'],
            'category_id'               => ['nullable','integer','exists:product_categories,id'],
            'supplier_id'               => ['nullable','integer','exists:suppliers,id'],
            'unit_cost'                 => ['required','numeric','min:0'],
            'selling_price'             => ['required','numeric','min:0'],
            'tax_rate'                  => ['sometimes','numeric','min:0','max:100'],
            'unit_of_measure'           => ['sometimes','string','max:20'],
            'reorder_level'             => ['sometimes','integer','min:0'],
            'requires_age_verification' => ['sometimes','boolean'],
            'is_active'                 => ['sometimes','boolean'],
        ]);
        return response()->json(['message' => 'Product created.', 'data' => $this->service->create($validated)], 201);
    }

    public function show(int $id): JsonResponse
    {
        $this->authorize('view products');
        return response()->json(['data' => $this->service->findOrFail($id)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $this->authorize('edit products');
        $validated = $request->validate([
            'sku'                       => ['sometimes','string','max:50'],
            'name'                      => ['sometimes','string','max:255'],
            'description'               => ['sometimes','nullable','string'],
            'category_id'               => ['sometimes','nullable','integer','exists:product_categories,id'],
            'supplier_id'               => ['sometimes','nullable','integer','exists:suppliers,id'],
            'unit_cost'                 => ['sometimes','numeric','min:0'],
            'selling_price'             => ['sometimes','numeric','min:0'],
            'tax_rate'                  => ['sometimes','numeric','min:0','max:100'],
            'unit_of_measure'           => ['sometimes','string','max:20'],
            'reorder_level'             => ['sometimes','integer','min:0'],
            'requires_age_verification' => ['sometimes','boolean'],
            'is_active'                 => ['sometimes','boolean'],
        ]);
        return response()->json(['message' => 'Product updated.', 'data' => $this->service->update($id, $validated)]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete products');
        $this->service->delete($id);
        return response()->json(['message' => 'Product deleted.']);
    }

    public function restore(int $id): JsonResponse
    {
        $this->authorize('delete products');
        $this->service->restore($id);
        return response()->json(['message' => 'Product restored.']);
    }

    public function lowStock(Request $request): JsonResponse
    {
        $this->authorize('view inventory');
        $request->validate(['branch_id' => ['required','integer','exists:branches,id']]);
        return response()->json(['data' => $this->service->getLowStock($request->integer('branch_id'))]);
    }
}
