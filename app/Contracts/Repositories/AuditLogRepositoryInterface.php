<?php

namespace App\Contracts\Repositories;

use App\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AuditLogRepositoryInterface extends BaseRepositoryInterface
{
    public function findById(int $id, array $relations = []): ?AuditLog;

    public function log(
        string $tableName,
        int    $recordId,
        string $action,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int   $employeeId = null,
        ?int   $branchId = null,
        ?string $ipAddress = null
    ): AuditLog;

    public function getForRecord(string $tableName, int $recordId): \Illuminate\Database\Eloquent\Collection;

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;
}
