<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Concerns\ResolvesActiveBranch;
use App\Http\Controllers\Controller;
use App\Services\PayrollService;
use App\Services\BranchService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class PayrollWebController extends Controller
{
    use AuthorizesRequests, ResolvesActiveBranch;
    public function __construct(
        protected PayrollService $payrollService,
        protected BranchService  $branchService,
    ) {}

    public function index(Request $request)
    {
        $this->authorize('view payroll');
        $filters = $request->only(['employee_id', 'branch_id', 'status', 'period_start', 'period_end']);
        $user    = auth()->user();
        $effectiveBranchId = $this->resolveEffectiveBranchId($user);
        if (! $user->isSuperAdmin()) { $filters['branch_id'] = $user->branch_id; }
        elseif ($effectiveBranchId)  { $filters['branch_id'] = $effectiveBranchId; }
        else                         { unset($filters['branch_id']); }

        $payrolls    = $this->payrollService->list($filters, 20);
        $branches    = $this->branchService->allActive();
        $periodLabel = now()->format('F Y');
        $activeBranchLabel = $effectiveBranchId
            ? ($branches->firstWhere('id', $effectiveBranchId)?->name ?? 'Selected Branch')
            : 'All Branches';

        $stats = [
            'total_net'      => $payrolls->sum('net_pay'),
            'released_count' => \App\Models\Payroll::where('status', 'released')->count(),
            'pending_count'  => \App\Models\Payroll::where('status', 'draft')->count(),
        ];
        $totalCount = $payrolls->total();

        return view('pages.payroll.index', compact('payrolls', 'branches', 'periodLabel', 'stats', 'totalCount', 'effectiveBranchId', 'activeBranchLabel'));
    }

    public function show(int $id)
    {
        $payroll = $this->payrollService->findOrFail($id);
        $user    = auth()->user();

        // Everyone can open their own payslip regardless of the 'view payroll'
        // permission — that permission gates seeing OTHER employees' payroll.
        $isOwner = $user->employee_id !== null && $payroll->employee_id === $user->employee_id;
        if (! $isOwner) {
            $this->authorize('view payroll');
        }

        return view('pages.payroll.show', compact('payroll'));
    }

    /**
     * The currently logged-in user's own payslip history — no 'view payroll'
     * permission required, since this only ever shows the caller's own records.
     */
    public function mine()
    {
        $employeeId = auth()->user()->employee_id;

        $payrolls = $employeeId
            ? $this->payrollService->list(['employee_id' => $employeeId], 20)->withQueryString()
            : null;

        return view('pages.payroll.mine', compact('payrolls'));
    }

    public function export(Request $request)
    {
        $this->authorize('view payroll');

        $filters = $request->only(['employee_id', 'branch_id', 'status', 'period_start', 'period_end']);
        $user    = auth()->user();
        $effectiveBranchId = $this->resolveEffectiveBranchId($user);
        if (! $user->isSuperAdmin()) { $filters['branch_id'] = $user->branch_id; }
        elseif ($effectiveBranchId)  { $filters['branch_id'] = $effectiveBranchId; }
        else                         { unset($filters['branch_id']); }

        $payrolls = $this->payrollService->list($filters, 100000);
        $filename = 'payroll-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($payrolls) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Employee', 'Branch', 'Period', 'Base Pay', 'Overtime Pay', 'Allowances', 'Gross Pay',
                'SSS', 'PhilHealth', 'Pag-IBIG', 'Withholding Tax', 'Net Pay', 'Status',
            ]);
            foreach ($payrolls as $payroll) {
                fputcsv($handle, [
                    $payroll->employee->full_name ?? 'N/A',
                    $payroll->employee->branch->name ?? 'N/A',
                    $payroll->period_label,
                    number_format($payroll->base_pay, 2),
                    number_format($payroll->overtime_pay, 2),
                    number_format($payroll->allowances, 2),
                    number_format($payroll->gross_pay, 2),
                    number_format($payroll->sss_contribution, 2),
                    number_format($payroll->philhealth_contribution, 2),
                    number_format($payroll->pagibig_contribution, 2),
                    number_format($payroll->withholding_tax, 2),
                    number_format($payroll->net_pay, 2),
                    ucfirst($payroll->status),
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function generate(Request $request)
    {
        $this->authorize('process payroll');
        $validated = $request->validate([
            'branch_id'    => 'required|integer|exists:branches,id',
            'period_start' => 'required|date',
            'period_end'   => 'required|date|after_or_equal:period_start',
            'period_label' => 'required|string|max:100',
        ]);
        $records = $this->payrollService->generateForBranch(
            $validated['branch_id'],
            $validated['period_start'],
            $validated['period_end'],
            $validated['period_label'],
            $request->user()->employee_id,
            $request->ip()
        );
        return redirect()->route('payroll.index')
                         ->with('success', count($records) . ' payroll records generated.');
    }

    public function approve(Request $request, int $id)
    {
        $this->authorize('approve payroll');
        $this->payrollService->approve($id, $request->user()->employee_id ?? $request->user()->id, $request->ip());
        return back()->with('success', 'Payroll approved.');
    }

    public function release(Request $request, int $id)
    {
        $this->authorize('release payroll');
        $this->payrollService->release($id, $request->user()->employee_id, $request->ip());
        return back()->with('success', 'Payroll released.');
    }

    public function destroy(Request $request, int $id)
    {
        $this->authorize('process payroll');
        $this->payrollService->delete($id, $request->user()->employee_id, $request->ip());
        return redirect()->route('payroll.index')->with('success', 'Payroll record deleted.');
    }
}
