<?php

namespace App\Repositories;

use App\Contracts\Repositories\BranchRepositoryInterface;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Collection;

class BranchRepository extends BaseRepository implements BranchRepositoryInterface
{
    public function __construct(Branch $model)
    {
        parent::__construct($model);
    }

    public function findById(int $id, array $relations = []): ?Branch
    {
        return $this->model
            ->with($relations ?: ['employees', 'inventory.product'])
            ->withTrashed()
            ->find($id);
    }

    public function getActive(): Collection
    {
        return $this->model->where('is_active', true)->orderBy('name')->get();
    }

    public function withEmployeeCount(): Collection
    {
        return $this->model
            ->withCount(['employees' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('name')
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
            ->when(isset($filters['city']),         fn ($q) => $q->where('city', $filters['city']))
            ->when(isset($filters['with_trashed']), fn ($q) => $q->withTrashed())
            ->when(isset($filters['search']), fn ($q) => $q->where(fn ($s) =>
                $s->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('city', 'like', '%' . $filters['search'] . '%')
            ))
            ->orderBy('name');
    }
}
