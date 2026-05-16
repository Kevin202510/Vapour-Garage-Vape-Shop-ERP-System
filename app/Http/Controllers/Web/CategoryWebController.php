<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Services\ProductCategoryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CategoryWebController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected ProductCategoryService $categoryService) {}

    public function index()
    {
        $this->authorize('view categories');

        $categories = ProductCategory::withCount('products')
            ->with('parent', 'children')
            ->orderBy('name')
            ->get();

        $total        = $categories->count();
        $rootCount    = $categories->whereNull('parent_category_id')->count();
        $productTotal = $categories->sum('products_count');

        return view('pages.categories.index', compact('categories', 'total', 'rootCount', 'productTotal'));
    }

    public function create()
    {
        $this->authorize('create categories');
        $parents = ProductCategory::whereNull('parent_category_id')->orderBy('name')->get();
        return view('pages.categories.form', compact('parents'));
    }

    public function store(Request $request)
    {
        $this->authorize('create categories');

        $validated = $request->validate([
            'name'               => 'required|string|max:255|unique:product_categories,name',
            'description'        => 'nullable|string|max:500',
            'parent_category_id' => 'nullable|integer|exists:product_categories,id',
        ]);

        try {
            $category = $this->categoryService->create($validated);
            return redirect()->route('categories.index')
                ->with('success', "Category \"{$category->name}\" created.");
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        }
    }

    public function edit(int $id)
    {
        $this->authorize('edit categories');
        $category = $this->categoryService->findOrFail($id);
        $parents  = ProductCategory::whereNull('parent_category_id')
            ->where('id', '!=', $id)
            ->orderBy('name')
            ->get();
        return view('pages.categories.form', compact('category', 'parents'));
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('edit categories');

        $validated = $request->validate([
            'name'               => "required|string|max:255|unique:product_categories,name,{$id}",
            'description'        => 'nullable|string|max:500',
            'parent_category_id' => "nullable|integer|exists:product_categories,id|not_in:{$id}",
        ]);

        try {
            $category = $this->categoryService->update($id, $validated);
            return redirect()->route('categories.index')
                ->with('success', "Category \"{$category->name}\" updated.");
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        }
    }

    public function destroy(int $id)
    {
        $this->authorize('delete categories');
        try {
            $category = $this->categoryService->findOrFail($id);
            $this->categoryService->delete($id);
            return redirect()->route('categories.index')
                ->with('success', "Category \"{$category->name}\" deleted.");
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }
}
