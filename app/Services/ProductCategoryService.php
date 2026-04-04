<?php

namespace App\Services;

use App\Contracts\Repositories\ProductCategoryRepositoryInterface;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class ProductCategoryService
{
    public function __construct(protected ProductCategoryRepositoryInterface $repo) {}

    public function all(array $filters = []): Collection
    {
        return $this->repo->all($filters);
    }

    public function tree(): Collection
    {
        return $this->repo->getWithChildren();
    }

    public function findOrFail(int $id): ProductCategory
    {
        return $this->repo->findById($id)
            ?? throw new ModelNotFoundException("Category [{$id}] not found.");
    }

    public function create(array $data): ProductCategory
    {
        if (isset($data['parent_category_id'])) {
            $this->repo->findById($data['parent_category_id'])
                ?? throw new ModelNotFoundException("Parent category [{$data['parent_category_id']}] not found.");
        }
        return $this->repo->create($data);
    }

    public function update(int $id, array $data): ProductCategory
    {
        $category = $this->findOrFail($id);

        if (isset($data['parent_category_id'])) {
            if ((int) $data['parent_category_id'] === $id) {
                throw ValidationException::withMessages([
                    'parent_category_id' => ['A category cannot be its own parent.'],
                ]);
            }
            $this->repo->findById($data['parent_category_id'])
                ?? throw new ModelNotFoundException("Parent category not found.");
        }

        return $this->repo->update($category, $data);
    }

    public function delete(int $id): bool
    {
        $category = $this->findOrFail($id);

        if ($category->children()->exists()) {
            throw ValidationException::withMessages([
                'id' => ['Cannot delete a category that has subcategories. Remove subcategories first.'],
            ]);
        }

        if ($category->products()->exists()) {
            throw ValidationException::withMessages([
                'id' => ['Cannot delete a category that still has products assigned to it.'],
            ]);
        }

        return $this->repo->delete($category);
    }
}
