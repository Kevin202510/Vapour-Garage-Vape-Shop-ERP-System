<?php

namespace Tests\Feature\Commands;

use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutoCloseAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_closes_a_shift_left_open_from_a_previous_day(): void
    {
        $employee = Employee::factory()->create();

        $stale = Attendance::factory()->create([
            'employee_id' => $employee->id,
            'work_date'   => now()->subDays(2)->toDateString(),
            'time_in'     => '08:00:00',
            'time_out'    => null,
            'hours_worked'   => 0,
            'overtime_hours' => 0,
        ]);

        $this->artisan('attendance:auto-clockout')->assertExitCode(0);

        $stale->refresh();

        $this->assertSame('23:59:59', $stale->time_out);
        $this->assertSame(8.0, (float) $stale->hours_worked);
        $this->assertStringContainsString('Auto clocked-out by system', $stale->remarks);
        $this->assertDatabaseHas('audit_logs', [
            'table_name' => 'attendances',
            'record_id'  => $stale->id,
            'action'     => 'update',
        ]);
    }

    public function test_it_leaves_todays_open_shift_untouched(): void
    {
        $employee = Employee::factory()->create();

        $today = Attendance::factory()->create([
            'employee_id' => $employee->id,
            'work_date'   => now()->toDateString(),
            'time_in'     => '08:00:00',
            'time_out'    => null,
        ]);

        $this->artisan('attendance:auto-clockout')->assertExitCode(0);

        $today->refresh();

        $this->assertNull($today->time_out);
        $this->assertDatabaseMissing('audit_logs', [
            'table_name' => 'attendances',
            'record_id'  => $today->id,
        ]);
    }
}
