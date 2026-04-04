<?php

namespace App\Services;

use App\Contracts\Repositories\DiscountRepositoryInterface;
use App\Models\Discount;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DiscountService
{
    public function __construct(protected DiscountRepositoryInterface $repo) {}

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repo->paginate($perPage, $filters);
    }

    public function getValidNow(): Collection
    {
        return $this->repo->getValidNow();
    }

    public function findOrFail(int $id): Discount
    {
        return $this->repo->findById($id)
            ?? throw new ModelNotFoundException("Discount [{$id}] not found.");
    }

    public function create(array $data): Discount
    {
        return $this->repo->create($data);
    }

    public function update(int $id, array $data): Discount
    {
        return $this->repo->update($this->findOrFail($id), $data);
    }

    public function delete(int $id): bool
    {
        return $this->repo->delete($this->findOrFail($id));
    }
}
