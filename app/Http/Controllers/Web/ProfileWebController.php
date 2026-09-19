<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\EmployeeService;

class ProfileWebController extends Controller
{
    public function __construct(protected EmployeeService $employeeService) {}

    /**
     * The logged-in user's own profile — account info plus their linked
     * employee record, styled like the admin employee-details page. No
     * permission gate: this only ever shows the caller's own data.
     */
    public function show()
    {
        $user     = auth()->user();
        $employee = $user->employee_id ? $this->employeeService->findOrFail($user->employee_id) : null;

        return view('pages.profile.show', compact('user', 'employee'));
    }
}
