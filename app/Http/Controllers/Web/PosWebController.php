<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Services\ProductCategoryService;
use App\Services\BranchService;

class PosWebController extends Controller
{
    public function __construct(
        protected ProductService         $productService,
        protected ProductCategoryService $categoryService,
        protected BranchService          $branchService,
    ) {}

    public function index()
    {
        $products   = $this->productService->list(['is_active' => true]);
        $categories = $this->categoryService->all();
        // $categories = $this->categoryService->getRoots();
        $branches   = $this->branchService->allActive();

        // Flatten products with category name for the Alpine.js component
        $products = $products->map(function ($product) {
            return [
                'id'            => $product->id,
                'name'          => $product->name,
                'sku'           => $product->sku,
                'selling_price' => $product->selling_price,
                'unit_cost'     => $product->unit_cost,
                'tax_rate'      => $product->tax_rate,
                'category_id'   => $product->category_id,
                'category_name' => $product->category->name ?? 'Uncategorised',
                'is_active'     => $product->is_active,
                'stock'         => $product->inventory
                    ->where('branch_id', auth()->user()->branch_id)
                    ->first()?->available_quantity ?? 0,
                'reorder_level' => $product->reorder_level,
            ];
        });

        return view('pages.pos.index', compact('products', 'categories', 'branches'));
    }
}
