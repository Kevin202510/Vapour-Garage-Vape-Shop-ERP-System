<?php

namespace App\Services;

use App\Contracts\Repositories\AuditLogRepositoryInterface;
use App\Contracts\Repositories\BranchRepositoryInterface;
use App\Models\Branch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BranchService
{
    public function __construct(
        protected BranchRepositoryInterface   $repo,
        protected AuditLogRepositoryInterface $auditRepo,
    ) {}

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repo->paginate($perPage, $filters);
    }

    public function allActive(): Collection
    {
        return $this->repo->getActive();
    }

    public function findOrFail(int $id): Branch
    {
        return $this->repo->findById($id) ?? throw new ModelNotFoundException("Branch [{$id}] not found.");
    }

    public function create(array $data, ?int $actingEmployeeId = null, ?string $ipAddress = null): Branch
    {
        $branch = $this->repo->create($data);

        $this->auditRepo->log(
            tableName:  'branches',
            recordId:   $branch->id,
            action:     'create',
            newValues:  $data,
            employeeId: $actingEmployeeId,
            branchId:   $branch->id,
            ipAddress:  $ipAddress,
        );

        return $branch;
    }

    public function update(int $id, array $data, ?int $actingEmployeeId = null, ?string $ipAddress = null): Branch
    {
        $branch    = $this->findOrFail($id);
        $oldValues = $branch->only(array_keys($data));
        $updated   = $this->repo->update($branch, $data);

        $this->auditRepo->log(
            tableName:  'branches',
            recordId:   $branch->id,
            action:     'update',
            oldValues:  $oldValues,
            newValues:  $data,
            employeeId: $actingEmployeeId,
            branchId:   $branch->id,
            ipAddress:  $ipAddress,
        );

        return $updated;
    }

    public function delete(int $id, ?int $actingEmployeeId = null, ?string $ipAddress = null): bool
    {
        $branch = $this->findOrFail($id);
        $result = $this->repo->delete($branch);

        $this->auditRepo->log(
            tableName:  'branches',
            recordId:   $branch->id,
            action:     'delete',
            oldValues:  ['name' => $branch->name],
            employeeId: $actingEmployeeId,
            branchId:   $branch->id,
            ipAddress:  $ipAddress,
        );

        return $result;
    }

    public function restore(int $id): bool
    {
        return $this->repo->restore($id);
    }

    public function withEmployeeCount(): Collection
    {
        return $this->repo->withEmployeeCount();
    }
}
