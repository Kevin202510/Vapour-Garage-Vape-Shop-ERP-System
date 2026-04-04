<?php

namespace App\Repositories;

use App\Contracts\Repositories\AuditLogRepositoryInterface;
use App\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AuditLogRepository extends BaseRepository implements AuditLogRepositoryInterface
{
    public function __construct(AuditLog $model)
    {
        parent::__construct($model);
    }

    public function findById(int $id, array $relations = []): ?AuditLog
    {
        return $this->model
            ->with($relations ?: ['employee', 'branch'])
            ->find($id);
    }

    public function log(
        string  $tableName,
        int     $recordId,
        string  $action,
        ?array  $oldValues = null,
        ?array  $newValues = null,
        ?int    $employeeId = null,
        ?int    $branchId = null,
        ?string $ipAddress = null
    ): AuditLog {
        return $this->model->create([
            'table_name'  => $tableName,
            'record_id'   => $recordId,
            'action'      => $action,
            'old_values'  => $oldValues,
            'new_values'  => $newValues,
            'employee_id' => $employeeId,
            'branch_id'   => $branchId,
            'ip_address'  => $ipAddress,
            'created_at'  => now(),
        ]);
    }

    public function getForRecord(string $tableName, int $recordId): Collection
    {
        return $this->model
            ->with(['employee'])
            ->where('table_name', $tableName)
            ->where('record_id', $recordId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->buildQuery($filters)->paginate($perPage);
    }

    protected function buildQuery(array $filters = [])
    {
        return $this->model
            ->with(['employee', 'branch'])
            ->when(isset($filters['employee_id']), fn ($q) => $q->where('employee_id', $filters['employee_id']))
            ->when(isset($filters['branch_id']),   fn ($q) => $q->where('branch_id', $filters['branch_id']))
            ->when(isset($filters['table_name']),  fn ($q) => $q->where('table_name', $filters['table_name']))
            ->when(isset($filters['action']),      fn ($q) => $q->where('action', $filters['action']))
            ->when(isset($filters['date_from']),   fn ($q) => $q->where('created_at', '>=', $filters['date_from'] . ' 00:00:00'))
            ->when(isset($filters['date_to']),     fn ($q) => $q->where('created_at', '<=', $filters['date_to'] . ' 23:59:59'))
            ->orderBy('created_at', 'desc');
    }
}
