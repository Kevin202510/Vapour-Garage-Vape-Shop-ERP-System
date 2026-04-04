<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    public function definition(): array
    {
        $timeIn      = fake()->dateTimeBetween('07:45:00', '09:30:00');
        $timeOut     = fake()->dateTimeBetween('17:00:00', '19:00:00');
        $hoursWorked = round(($timeOut->getTimestamp() - $timeIn->getTimestamp()) / 3600, 2);
        $overtime    = max(0, $hoursWorked - 8);

        return [
            'employee_id'    => Employee::factory(),
            'branch_id'      => Branch::factory(),
            'work_date'      => fake()->dateTimeBetween('-30 days', 'now'),
            'time_in'        => $timeIn->format('H:i:s'),
            'time_out'       => $timeOut->format('H:i:s'),
            'hours_worked'   => min($hoursWorked, 8),
            'overtime_hours' => $overtime,
            'status'         => 'present',
            'remarks'        => null,
        ];
    }

    public function absent(): static
    {
        return $this->state(fn () => [
            'time_in'        => null,
            'time_out'       => null,
            'hours_worked'   => 0,
            'overtime_hours' => 0,
            'status'         => 'absent',
        ]);
    }

    public function late(): static
    {
        return $this->state(fn () => [
            'time_in' => fake()->dateTimeBetween('09:31:00', '11:00:00')->format('H:i:s'),
            'status'  => 'late',
        ]);
    }
}
