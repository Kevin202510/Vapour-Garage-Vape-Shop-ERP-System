<?php

namespace App\Repositories;

use App\Contracts\Repositories\DepartmentRepositoryInterface;
use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;

class DepartmentRepository extends BaseRepository implements DepartmentRepositoryInterface
{
    public function __construct(Department $model)
    {
        parent::__construct($model);
    }

    public function findById(int $id, array $relations = []): ?Department
    {
        return $this->model
            ->with($relations ?: ['employees'])
            ->find($id);
    }

    public function allWithEmployeeCount(): Collection
    {
        return $this->model
            ->withCount(['employees' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('name')
            ->get();
    }

    protected function buildQuery(array $filters = [])
    {
        return $this->model
            ->when(isset($filters['search']), fn ($q) => $q->where(fn ($s) =>
                $s->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%')
            ))
            ->orderBy('name');
    }
}
