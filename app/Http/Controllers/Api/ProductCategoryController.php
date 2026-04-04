<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProductCategoryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    use AuthorizesRequests;
    public function __construct(protected ProductCategoryService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('view categories');
        $tree = $request->boolean('tree');
        $data = $tree ? $this->service->tree() : $this->service->all($request->only(['parent_id','root_only','search']));
        return response()->json(['data' => $data]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create categories');
        $validated = $request->validate([
            'name'               => ['required','string','max:255'],
            'description'        => ['nullable','string'],
            'parent_category_id' => ['nullable','integer','exists:product_categories,id'],
        ]);
        return response()->json(['message' => 'Category created.', 'data' => $this->service->create($validated)], 201);
    }

    public function show(int $id): JsonResponse
    {
        $this->authorize('view categories');
        return response()->json(['data' => $this->service->findOrFail($id)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $this->authorize('edit categories');
        $validated = $request->validate([
            'name'               => ['sometimes','string','max:255'],
            'description'        => ['sometimes','nullable','string'],
            'parent_category_id' => ['sometimes','nullable','integer','exists:product_categories,id'],
        ]);
        return response()->json(['message' => 'Category updated.', 'data' => $this->service->update($id, $validated)]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete categories');
        $this->service->delete($id);
        return response()->json(['message' => 'Category deleted.']);
    }
}
