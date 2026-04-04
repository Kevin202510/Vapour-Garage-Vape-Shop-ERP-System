<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\PayrollService;
use App\Services\BranchService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class PayrollWebController extends Controller
{
    use AuthorizesRequests;
    public function __construct(
        protected PayrollService $payrollService,
        protected BranchService  $branchService,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['employee_id', 'branch_id', 'status', 'period_start', 'period_end']);
        $user    = auth()->user();
        if (! $user->isSuperAdmin()) $filters['branch_id'] = $user->branch_id;

        $payrolls    = $this->payrollService->list($filters, 20);
        $branches    = $this->branchService->allActive();
        $periodLabel = now()->format('F Y');

        $stats = [
            'total_net'      => $payrolls->sum('net_pay'),
            'released_count' => \App\Models\Payroll::where('status', 'released')->count(),
            'pending_count'  => \App\Models\Payroll::where('status', 'draft')->count(),
        ];
        $totalCount = $payrolls->total();

        return view('pages.payroll.index', compact('payrolls', 'branches', 'periodLabel', 'stats', 'totalCount'));
    }

    public function show(int $id)
    {
        $payroll = $this->payrollService->findOrFail($id);
        return view('pages.payroll.show', compact('payroll'));
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
            $validated['period_label']
        );
        return redirect()->route('payroll.index')
                         ->with('success', count($records) . ' payroll records generated.');
    }

    public function approve(Request $request, int $id)
    {
        $this->authorize('approve payroll');
        $this->payrollService->approve($id, $request->user()->employee_id ?? $request->user()->id);
        return back()->with('success', 'Payroll approved.');
    }

    public function release(int $id)
    {
        $this->authorize('approve payroll');
        $this->payrollService->release($id);
        return back()->with('success', 'Payroll released.');
    }

    public function destroy(int $id)
    {
        $this->authorize('process payroll');
        $this->payrollService->delete($id);
        return redirect()->route('payroll.index')->with('success', 'Payroll record deleted.');
    }
}
