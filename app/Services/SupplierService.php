<?php

namespace App\Services;

use App\Contracts\Repositories\SupplierRepositoryInterface;
use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SupplierService
{
    public function __construct(protected SupplierRepositoryInterface $repo) {}

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repo->paginate($perPage, $filters);
    }

    public function allActive(): Collection
    {
        return $this->repo->getActive();
    }

    public function findOrFail(int $id): Supplier
    {
        return $this->repo->findById($id)
            ?? throw new ModelNotFoundException("Supplier [{$id}] not found.");
    }

    public function create(array $data): Supplier
    {
        return $this->repo->create($data);
    }

    public function update(int $id, array $data): Supplier
    {
        return $this->repo->update($this->findOrFail($id), $data);
    }

    public function delete(int $id): bool
    {
        return $this->repo->delete($this->findOrFail($id));
    }

    public function restore(int $id): bool
    {
        return $this->repo->restore($id);
    }
}
