<?php

namespace App\Services;

use App\Contracts\Repositories\DepartmentRepositoryInterface;
use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class DepartmentService
{
    public function __construct(protected DepartmentRepositoryInterface $repo) {}

    public function allWithCount(): Collection
    {
        return $this->repo->allWithEmployeeCount();
    }

    public function findOrFail(int $id): Department
    {
        return $this->repo->findById($id)
            ?? throw new ModelNotFoundException("Department [{$id}] not found.");
    }

    public function create(array $data): Department
    {
        return $this->repo->create($data);
    }

    public function update(int $id, array $data): Department
    {
        return $this->repo->update($this->findOrFail($id), $data);
    }

    public function delete(int $id): bool
    {
        $dept = $this->findOrFail($id);

        if ($dept->employees()->where('is_active', true)->exists()) {
            throw ValidationException::withMessages([
                'id' => ['Cannot delete a department with active employees.'],
            ]);
        }

        return $this->repo->delete($dept);
    }
}
