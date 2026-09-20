<?php

namespace App\Services;

use App\Concerns\EnforcesBranchAccess;
use App\Contracts\Repositories\AuditLogRepositoryInterface;
use App\Contracts\Repositories\EmployeeRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class EmployeeService
{
    use EnforcesBranchAccess;

    /**
     * Employee role → Spatie permission role. Roles not listed here
     * (e.g. 'admin') get no automatic role grant on account creation.
     */
    private const ROLE_MAP = [
        'branch_manager'  => 'branch_manager',
        'supervisor'      => 'supervisor',
        'cashier'         => 'cashier',
        'inventory_clerk' => 'inventory_clerk',
    ];

    public function __construct(
        protected EmployeeRepositoryInterface $repo,
        protected UserRepositoryInterface $userRepo,
        protected AuditLogRepositoryInterface $auditRepo,
    ) {}

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repo->paginate($perPage, $filters);
    }

    public function findOrFail(int $id): Employee
    {
        return $this->repo->findById($id) ?? throw new ModelNotFoundException("Employee [{$id}] not found.");
    }

    public function create(array $data, ?int $actingEmployeeId = null, ?string $ipAddress = null): Employee
    {
        $this->guardDuplicateCode($data['employee_code'] ?? null);

        return DB::transaction(function () use ($data, $actingEmployeeId, $ipAddress) {
            $employee = $this->repo->create($data);

            $this->auditRepo->log(
                tableName:  'employees',
                recordId:   $employee->id,
                action:     'create',
                newValues:  $data,
                employeeId: $actingEmployeeId,
                branchId:   $employee->branch_id,
                ipAddress:  $ipAddress,
            );

            return $employee;
        });
    }

    /**
     * Create a login account for an existing employee and grant the role
     * mapped from their employee role. Runs in the same transaction scope
     * as the caller when called from within one.
     */
    public function createLoginAccount(Employee $employee, string $email, string $password, ?int $actingEmployeeId = null, ?string $ipAddress = null): User
    {
        return DB::transaction(function () use ($employee, $email, $password, $actingEmployeeId, $ipAddress) {
            $user = $this->userRepo->create([
                'name'              => $employee->full_name,
                'email'             => $email,
                'password'          => Hash::make($password),
                'employee_id'       => $employee->id,
                'branch_id'         => $employee->branch_id,
                'is_active'         => true,
                'email_verified_at' => now(),
            ]);

            if (isset(self::ROLE_MAP[$employee->role])) {
                $user = $this->userRepo->syncRoles($user, [self::ROLE_MAP[$employee->role]]);
            }

            $this->auditRepo->log(
                tableName:  'users',
                recordId:   $user->id,
                action:     'create',
                newValues:  ['email' => $email, 'employee_id' => $employee->id, 'roles' => $user->roles->pluck('name')->toArray()],
                employeeId: $actingEmployeeId,
                branchId:   $employee->branch_id,
                ipAddress:  $ipAddress,
            );

            return $user;
        });
    }

    public function update(int $id, array $data, ?int $actingEmployeeId, ?string $ipAddress, User $actor): Employee
    {
        $employee = $this->findOrFail($id);

        $this->assertBranchAccess($actor, $employee->branch_id);

        // Only a super-admin may move an employee to a different branch —
        // a branch manager editing their own staff should never be able to
        // reassign them elsewhere.
        if (isset($data['branch_id']) && ! $actor->isSuperAdmin()) {
            unset($data['branch_id']);
        }

        if (isset($data['employee_code']) && $data['employee_code'] !== $employee->employee_code) {
            $this->guardDuplicateCode($data['employee_code'], $id);
        }

        return DB::transaction(function () use ($employee, $data, $actingEmployeeId, $ipAddress) {
            $oldValues = $employee->only(array_keys($data));
            $updated   = $this->repo->update($employee, $data);

            $this->auditRepo->log(
                tableName:  'employees',
                recordId:   $employee->id,
                action:     'update',
                oldValues:  $oldValues,
                newValues:  $data,
                employeeId: $actingEmployeeId,
                branchId:   $employee->branch_id,
                ipAddress:  $ipAddress,
            );

            return $updated;
        });
    }

    public function delete(int $id, ?int $actingEmployeeId, ?string $ipAddress, User $actor): bool
    {
        $employee = $this->findOrFail($id);

        $this->assertBranchAccess($actor, $employee->branch_id);

        return DB::transaction(function () use ($employee, $actingEmployeeId, $ipAddress) {
            $result = $this->repo->delete($employee);

            $this->auditRepo->log(
                tableName:  'employees',
                recordId:   $employee->id,
                action:     'delete',
                oldValues:  ['employee_code' => $employee->employee_code, 'full_name' => $employee->full_name],
                employeeId: $actingEmployeeId,
                branchId:   $employee->branch_id,
                ipAddress:  $ipAddress,
            );

            return $result;
        });
    }

    public function restore(int $id): bool
    {
        return $this->repo->restore($id);
    }

    public function getByBranch(int $branchId, bool $activeOnly = true): Collection
    {
        return $this->repo->getByBranch($branchId, $activeOnly);
    }

    public function getByRole(string $role, ?int $branchId = null): Collection
    {
        return $this->repo->getByRole($role, $branchId);
    }

    private function guardDuplicateCode(?string $code, ?int $excludeId = null): void
    {
        if (! $code) return;

        $exists = Employee::where('employee_code', $code)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->withTrashed()
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages(['employee_code' => ['Employee code already exists.']]);
        }
    }
}
