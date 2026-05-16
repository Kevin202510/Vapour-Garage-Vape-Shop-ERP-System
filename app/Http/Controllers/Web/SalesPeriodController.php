<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\SalesPeriodSummary;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SalesPeriodController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('view sales reports');

        $user      = auth()->user();
        $periodType = $request->input('period_type', 'daily');
        $branches  = Branch::where('is_active', true)->orderBy('name')->get();

        // Branch scoping: admins can pick any branch; others are locked to their branch
        $effectiveBranchId = $user->isSuperAdmin()
            ? ($request->input('branch_id') ?: $user->activeBranchId())
            : $user->branch_id;

        $summaries = SalesPeriodSummary::with('branch')
            ->where('period_type', $periodType)
            ->when($effectiveBranchId, fn ($q) => $q->where('branch_id', $effectiveBranchId))
            ->when(! $effectiveBranchId, fn ($q) => $q->whereNull('branch_id')) // all-branches aggregate
            ->orderByDesc('date_from')
            ->paginate(20)
            ->withQueryString();

        return view('pages.sales-periods.index', compact(
            'summaries', 'branches', 'periodType', 'effectiveBranchId'
        ));
    }

    public function close(Request $request)
    {
        $this->authorize('view sales reports');

        $validated = $request->validate([
            'period'    => 'required|in:daily,weekly,monthly,yearly',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $options = [
            '--by'      => 'manual',
            '--by-name' => auth()->user()->name,
            '--force'   => true,
        ];

        if ($validated['branch_id'] ?? null) {
            $options['--branch'] = $validated['branch_id'];
        }

        Artisan::call('sales:close-period', array_merge(
            ['period' => $validated['period']],
            $options
        ));

        $output = Artisan::output();

        return back()->with('success', "Period closed successfully. " . trim($output));
    }
}

