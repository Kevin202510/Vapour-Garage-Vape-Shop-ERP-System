<?php

namespace App\Services;

use App\Concerns\EnforcesBranchAccess;
use App\Contracts\Repositories\AttendanceRepositoryInterface;
use App\Contracts\Repositories\AuditLogRepositoryInterface;
use App\Contracts\Repositories\EmployeeRepositoryInterface;
use App\Contracts\Repositories\PayrollRepositoryInterface;
use App\Models\Payroll;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PayrollService
{
    use EnforcesBranchAccess;

    public function __construct(
        protected PayrollRepositoryInterface   $repo,
        protected EmployeeRepositoryInterface  $employeeRepo,
        protected AttendanceRepositoryInterface $attendanceRepo,
        protected AuditLogRepositoryInterface  $auditRepo,
    ) {}

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repo->paginate($perPage, $filters);
    }

    public function findOrFail(int $id): Payroll
    {
        return $this->repo->findById($id)
            ?? throw new ModelNotFoundException("Payroll record [{$id}] not found.");
    }

    public function getByEmployee(int $employeeId): Collection
    {
        return $this->repo->getByEmployee($employeeId);
    }

    /**
     * Generate payroll for all active employees in a branch for a given period.
     */
    public function generateForBranch(int $branchId, string $periodStart, string $periodEnd, string $periodLabel, ?int $actingEmployeeId = null, ?string $ipAddress = null): array
    {
        $employees = $this->employeeRepo->getByBranch($branchId, activeOnly: true);
        $created   = [];

        DB::transaction(function () use ($employees, $branchId, $periodStart, $periodEnd, $periodLabel, &$created) {
            foreach ($employees as $employee) {
                $existing = $this->repo->getByBranchAndPeriod($branchId, $periodStart, $periodEnd)
                    ->where('employee_id', $employee->id)
                    ->first();

                if ($existing) {
                    continue; // Skip if already generated
                }

                $summary    = $this->attendanceRepo->getMonthlySummary(
                    $employee->id,
                    (int) date('Y', strtotime($periodStart)),
                    (int) date('m', strtotime($periodStart))
                );

                $dailyRate  = round($employee->base_salary / 26, 4); // 26 pay days/month
                $hourlyRate = round($dailyRate / 8, 4);
                $otRate     = round($hourlyRate * 1.25, 4);           // DOLE OT: 125%

                $basePay    = round($dailyRate * $summary['present'] + ($dailyRate * 0.5 * $summary['half_day']), 2);
                $otPay      = round($otRate * $summary['overtime_hours'], 2);
                $allowance  = 500.00;
                $grossPay   = round($basePay + $otPay + $allowance, 2);

                // 2024 PH statutory deductions (simplified)
                $sss        = $this->computeSss($grossPay);
                $phic       = round($grossPay * 0.04 / 2, 2);  // 4% split employer/employee
                $pagibig    = 100.00;
                $wtax       = $this->computeWithholdingTax($grossPay);

                $netPay = round($grossPay - $sss - $phic - $pagibig - $wtax, 2);

                try {
                    $created[] = $this->repo->create([
                        'employee_id'             => $employee->id,
                        'period_label'            => $periodLabel,
                        'period_start'            => $periodStart,
                        'period_end'              => $periodEnd,
                        'base_pay'                => $basePay,
                        'overtime_pay'            => $otPay,
                        'allowances'              => $allowance,
                        'gross_pay'               => $grossPay,
                        'sss_contribution'        => $sss,
                        'philhealth_contribution' => $phic,
                        'pagibig_contribution'    => $pagibig,
                        'withholding_tax'         => $wtax,
                        'other_deductions'        => 0,
                        'net_pay'                 => $netPay,
                        'status'                  => 'draft',
                    ]);
                } catch (QueryException $e) {
                    // Unique (employee_id, period_start, period_end) violation — a
                    // concurrent generate request already created this record.
                    if ((string) $e->getCode() !== '23000') {
                        throw $e;
                    }
                }
            }
        });

        $this->auditRepo->log(
            tableName:  'payroll',
            recordId:   $branchId,
            action:     'create',
            newValues:  ['branch_id' => $branchId, 'period_label' => $periodLabel, 'period_start' => $periodStart, 'period_end' => $periodEnd, 'records_generated' => count($created)],
            employeeId: $actingEmployeeId,
            branchId:   $branchId,
            ipAddress:  $ipAddress,
        );

        return $created;
    }

    public function approve(int $id, int $processedBy, ?string $ipAddress, User $actor): Payroll
    {
        return DB::transaction(function () use ($id, $processedBy, $ipAddress, $actor) {
            $payroll = $this->repo->lockForUpdate($id)
                ?? throw new ModelNotFoundException("Payroll record [{$id}] not found.");

            $this->assertBranchAccess($actor, $payroll->employee?->branch_id);

            if ($payroll->status !== 'draft') {
                throw new RuntimeException("Only draft payroll records can be approved.");
            }

            $approved = $this->repo->approve($payroll, $processedBy);

            $this->auditRepo->log(
                tableName:  'payroll',
                recordId:   $payroll->id,
                action:     'approve',
                oldValues:  ['status' => 'draft'],
                newValues:  ['status' => 'approved', 'net_pay' => (string) $payroll->net_pay],
                employeeId: $processedBy,
                branchId:   $payroll->employee?->branch_id,
                ipAddress:  $ipAddress,
            );

            return $approved;
        });
    }

    public function release(int $id, ?int $actingEmployeeId, ?string $ipAddress, User $actor): Payroll
    {
        return DB::transaction(function () use ($id, $actingEmployeeId, $ipAddress, $actor) {
            $payroll = $this->repo->lockForUpdate($id)
                ?? throw new ModelNotFoundException("Payroll record [{$id}] not found.");

            $this->assertBranchAccess($actor, $payroll->employee?->branch_id);

            if ($payroll->status !== 'approved') {
                throw new RuntimeException("Only approved payroll records can be released.");
            }

            $released = $this->repo->release($payroll);

            $this->auditRepo->log(
                tableName:  'payroll',
                recordId:   $payroll->id,
                action:     'update',
                oldValues:  ['status' => 'approved'],
                newValues:  ['status' => 'released', 'net_pay' => (string) $payroll->net_pay],
                employeeId: $actingEmployeeId,
                branchId:   $payroll->employee?->branch_id,
                ipAddress:  $ipAddress,
            );

            return $released;
        });
    }

    public function delete(int $id, ?int $actingEmployeeId, ?string $ipAddress, User $actor): bool
    {
        return DB::transaction(function () use ($id, $actingEmployeeId, $ipAddress, $actor) {
            $payroll = $this->repo->lockForUpdate($id)
                ?? throw new ModelNotFoundException("Payroll record [{$id}] not found.");

            $this->assertBranchAccess($actor, $payroll->employee?->branch_id);

            if ($payroll->status !== 'draft') {
                throw new RuntimeException("Only draft payroll records can be deleted.");
            }

            $result = $this->repo->delete($payroll);

            $this->auditRepo->log(
                tableName:  'payroll',
                recordId:   $payroll->id,
                action:     'delete',
                oldValues:  ['employee_id' => $payroll->employee_id, 'period_label' => $payroll->period_label, 'net_pay' => (string) $payroll->net_pay],
                employeeId: $actingEmployeeId,
                branchId:   $payroll->employee?->branch_id,
                ipAddress:  $ipAddress,
            );

            return $result;
        });
    }

    // -------------------------------------------------------------------------
    // PH Statutory computation helpers
    // -------------------------------------------------------------------------

    private function computeSss(float $grossPay): float
    {
        // SSS 2024 contribution table (simplified monthly brackets)
        return match (true) {
            $grossPay < 4250  => 180.00,
            $grossPay < 4750  => 202.50,
            $grossPay < 5250  => 225.00,
            $grossPay < 5750  => 247.50,
            $grossPay < 6250  => 270.00,
            $grossPay < 6750  => 292.50,
            $grossPay < 7250  => 315.00,
            $grossPay < 7750  => 337.50,
            $grossPay < 8250  => 360.00,
            $grossPay < 8750  => 382.50,
            $grossPay < 9250  => 405.00,
            $grossPay < 9750  => 427.50,
            $grossPay < 10250 => 450.00,
            $grossPay < 15000 => 562.50,
            $grossPay < 20000 => 900.00,
            default           => 1125.00,
        };
    }

    private function computeWithholdingTax(float $monthlyGross): float
    {
        // BIR 2023 withholding tax table (monthly)
        return match (true) {
            $monthlyGross <= 20833  => 0,
            $monthlyGross <= 33332  => round(($monthlyGross - 20833) * 0.15, 2),
            $monthlyGross <= 66666  => round(1875 + ($monthlyGross - 33333) * 0.20, 2),
            $monthlyGross <= 166666 => round(8541.80 + ($monthlyGross - 66667) * 0.25, 2),
            $monthlyGross <= 666666 => round(33541.80 + ($monthlyGross - 166667) * 0.30, 2),
            default                 => round(183541.80 + ($monthlyGross - 666667) * 0.35, 2),
        };
    }
}
