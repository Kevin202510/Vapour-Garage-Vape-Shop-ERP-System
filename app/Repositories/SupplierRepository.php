<?php

namespace App\Repositories;

use App\Contracts\Repositories\SupplierRepositoryInterface;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;

class SupplierRepository extends BaseRepository implements SupplierRepositoryInterface
{
    public function __construct(Supplier $model)
    {
        parent::__construct($model);
    }

    public function findById(int $id, array $relations = []): ?Supplier
    {
        return $this->model
            ->with($relations ?: ['products'])
            ->withTrashed()
            ->find($id);
    }

    public function getActive(): Collection
    {
        return $this->model
            ->where('is_active', true)
            ->orderBy('company_name')
            ->get();
    }

    public function restore(int $id): bool
    {
        return (bool) $this->model->withTrashed()->find($id)?->restore();
    }

    protected function buildQuery(array $filters = [])
    {
        return $this->model
            ->when(isset($filters['is_active']),    fn ($q) => $q->where('is_active', $filters['is_active']))
            ->when(isset($filters['with_trashed']), fn ($q) => $q->withTrashed())
            ->when(isset($filters['search']), fn ($q) => $q->where(fn ($s) =>
                $s->where('company_name',   'like', '%' . $filters['search'] . '%')
                  ->orWhere('contact_person','like', '%' . $filters['search'] . '%')
                  ->orWhere('email',         'like', '%' . $filters['search'] . '%')
            ))
            ->orderBy('company_name');
    }
}
