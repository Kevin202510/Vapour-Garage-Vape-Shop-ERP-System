<?php

namespace App\Services;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class ProductService
{
    public function __construct(protected ProductRepositoryInterface $repo) {}

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repo->paginate($perPage, $filters);
    }

    public function findOrFail(int $id): Product
    {
        return $this->repo->findById($id) ?? throw new ModelNotFoundException("Product [{$id}] not found.");
    }

    public function findBySkuOrFail(string $sku): Product
    {
        return $this->repo->findBySku($sku) ?? throw new ModelNotFoundException("Product SKU [{$sku}] not found.");
    }

    public function create(array $data): Product
    {
        $this->guardDuplicateSku($data['sku']);
        $product = $this->repo->create($data);

        // Create a zero-stock inventory record for every active branch so the
        // product appears in the inventory list and POS immediately after creation.
        Branch::where('is_active', true)->each(function (Branch $branch) use ($product) {
            BranchInventory::firstOrCreate(
                ['branch_id' => $branch->id, 'product_id' => $product->id],
                ['quantity_on_hand' => 0, 'quantity_reserved' => 0]
            );
        });

        return $product;
    }

    public function update(int $id, array $data): Product
    {
        $product = $this->findOrFail($id);

        if (isset($data['sku']) && $data['sku'] !== $product->sku) {
            $this->guardDuplicateSku($data['sku'], $id);
        }

        return $this->repo->update($product, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repo->delete($this->findOrFail($id));
    }

    public function restore(int $id): bool
    {
        return $this->repo->restore($id);
    }

    public function getLowStock(int $branchId): Collection
    {
        return $this->repo->getLowStock($branchId);
    }

    private function guardDuplicateSku(string $sku, ?int $excludeId = null): void
    {
        $exists = Product::where('sku', $sku)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->withTrashed()
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages(['sku' => ['This SKU already exists.']]);
        }
    }
}
