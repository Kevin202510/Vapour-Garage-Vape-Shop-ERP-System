<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AttendanceService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    use AuthorizesRequests;
    public function __construct(protected AttendanceService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('view attendance');
        return response()->json($this->service->list(
            $request->only(['employee_id','branch_id','status','date_from','date_to','work_date']),
            $request->integer('per_page', 15)
        ));
    }

    public function clockIn(Request $request): JsonResponse
    {
        $this->authorize('manage attendance');
        $validated = $request->validate([
            'employee_id' => ['required','integer','exists:employees,id'],
            'branch_id'   => ['required','integer','exists:branches,id'],
        ]);
        $record = $this->service->clockIn($validated['employee_id'], $validated['branch_id']);
        return response()->json(['message' => 'Clocked in.', 'data' => $record]);
    }

    public function clockOut(Request $request): JsonResponse
    {
        $this->authorize('manage attendance');
        $validated = $request->validate(['employee_id' => ['required','integer','exists:employees,id']]);
        $record = $this->service->clockOut($validated['employee_id']);
        return response()->json(['message' => 'Clocked out.', 'data' => $record]);
    }

    public function manualEntry(Request $request): JsonResponse
    {
        $this->authorize('manage attendance');
        $validated = $request->validate([
            'employee_id'    => ['required','integer','exists:employees,id'],
            'branch_id'      => ['required','integer','exists:branches,id'],
            'work_date'      => ['required','date'],
            'time_in'        => ['nullable','date_format:H:i:s'],
            'time_out'       => ['nullable','date_format:H:i:s','after:time_in'],
            'hours_worked'   => ['nullable','numeric','min:0','max:24'],
            'overtime_hours' => ['nullable','numeric','min:0'],
            'status'         => ['required','in:present,absent,late,half_day,rest_day,leave'],
            'remarks'        => ['nullable','string','max:255'],
        ]);
        $record = $this->service->manualEntry($validated);
        return response()->json(['message' => 'Attendance entry saved.', 'data' => $record]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $this->authorize('manage attendance');
        $validated = $request->validate([
            'time_in'        => ['sometimes','nullable','date_format:H:i:s'],
            'time_out'       => ['sometimes','nullable','date_format:H:i:s'],
            'hours_worked'   => ['sometimes','nullable','numeric','min:0'],
            'overtime_hours' => ['sometimes','nullable','numeric','min:0'],
            'status'         => ['sometimes','in:present,absent,late,half_day,rest_day,leave'],
            'remarks'        => ['sometimes','nullable','string','max:255'],
        ]);
        return response()->json(['message' => 'Attendance updated.', 'data' => $this->service->update($id, $validated)]);
    }

    public function summary(Request $request): JsonResponse
    {
        $this->authorize('view attendance');
        $request->validate([
            'employee_id' => ['required','integer','exists:employees,id'],
            'year'        => ['required','integer','min:2020'],
            'month'       => ['required','integer','min:1','max:12'],
        ]);
        $summary = $this->service->getMonthlySummary(
            $request->integer('employee_id'),
            $request->integer('year'),
            $request->integer('month')
        );
        return response()->json(['data' => $summary]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorize('manage attendance');
        $this->service->delete($id);
        return response()->json(['message' => 'Attendance record deleted.']);
    }
}
