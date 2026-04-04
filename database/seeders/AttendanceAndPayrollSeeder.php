<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Database\Seeder;

class AttendanceAndPayrollSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::where('is_active', true)->get();

        foreach ($employees as $employee) {
            // Seed attendance for last 15 working days
            $workDate = now()->subDays(15);

            while ($workDate->lte(now())) {
                // Skip Sundays
                if ($workDate->dayOfWeek === 0) {
                    $workDate->addDay();
                    continue;
                }

                $status = fake()->randomElement(['present', 'present', 'present', 'present', 'late', 'absent']);

                $data = [
                    'employee_id'    => $employee->id,
                    'branch_id'      => $employee->branch_id,
                    'work_date'      => $workDate->toDateString(),
                    'time_in'        => null,
                    'time_out'       => null,
                    'hours_worked'   => 0,
                    'overtime_hours' => 0,
                    'status'         => $status,
                    'remarks'        => null,
                ];

                if (in_array($status, ['present', 'late'])) {
                    $inHour  = $status === 'late' ? rand(10, 11) : rand(8, 9);
                    $outHour = rand(17, 19);
                    $hours   = min(8, $outHour - $inHour);
                    $ot      = max(0, $outHour - 17);

                    $data['time_in']        = sprintf('%02d:00:00', $inHour);
                    $data['time_out']       = sprintf('%02d:00:00', $outHour);
                    $data['hours_worked']   = $hours;
                    $data['overtime_hours'] = $ot;
                }

                Attendance::create($data);
                $workDate->addDay();
            }

            // Seed 2 payroll periods (semi-monthly)
            $periods = [
                ['label' => now()->format('Y-m') . ' First Half',  'start' => now()->startOfMonth(),                  'end' => now()->startOfMonth()->addDays(14)],
                ['label' => now()->subMonth()->format('Y-m') . ' Second Half', 'start' => now()->subMonth()->addDays(15), 'end' => now()->subMonth()->endOfMonth()],
            ];

            foreach ($periods as $period) {
                $basePay   = round($employee->base_salary / 2, 2);
                $otPay     = round(($employee->base_salary / 2 / 13 / 8) * 1.25 * rand(0, 16), 2);
                $allowance = 500;
                $gross     = $basePay + $otPay + $allowance;
                $sss       = 581.30;
                $phic      = round($gross * 0.04 / 2, 2);
                $pagibig   = 100;
                $tax       = max(0, round(($gross - 10417) * 0.20, 2));
                $net       = round($gross - $sss - $phic - $pagibig - $tax, 2);

                Payroll::create([
                    'employee_id'             => $employee->id,
                    'period_label'            => $period['label'],
                    'period_start'            => $period['start'],
                    'period_end'              => $period['end'],
                    'base_pay'                => $basePay,
                    'overtime_pay'            => $otPay,
                    'allowances'              => $allowance,
                    'gross_pay'              => $gross,
                    'sss_contribution'        => $sss,
                    'philhealth_contribution' => $phic,
                    'pagibig_contribution'    => $pagibig,
                    'withholding_tax'         => $tax,
                    'other_deductions'        => 0,
                    'net_pay'                 => $net,
                    'status'                  => 'released',
                    'processed_by'            => null,
                    'processed_at'            => $period['end'],
                ]);
            }
        }

        $this->command->info('✔ Attendance and payroll seeded for ' . $employees->count() . ' employees.');
    }
}
