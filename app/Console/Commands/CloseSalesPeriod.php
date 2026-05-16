<?php

namespace App\Console\Commands;

use App\Models\Branch;
use App\Models\SalesPeriodSummary;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CloseSalesPeriod extends Command
{
    protected $signature = 'sales:close-period
                            {period : daily|weekly|monthly|yearly}
                            {--branch= : Branch ID to close for (omit for all branches)}
                            {--by=system : Who triggered this close (system|manual)}
                            {--by-name= : Name of the user triggering a manual close}
                            {--force : Re-close even if a summary already exists for the period}';

    protected $description = 'Close a sales period: snapshot revenue metrics and reset the period view.';

    public function handle(): int
    {
        $period  = $this->argument('period');
        $by      = $this->option('by');
        $byName  = $this->option('by-name');
        $force   = $this->option('force');

        if (! in_array($period, ['daily', 'weekly', 'monthly', 'yearly'])) {
            $this->error("Invalid period. Use: daily, weekly, monthly, yearly.");
            return self::FAILURE;
        }

        [$dateFrom, $dateTo, $periodLabel] = $this->resolvePeriod($period);

        // Determine branches to close for
        $branchId = $this->option('branch');
        $branches = $branchId
            ? Branch::where('id', $branchId)->get()
            : Branch::where('is_active', true)->get();

        // Also close an all-branches aggregate (branch_id = null)
        $targets = $branches->push(null); // null = all-branches aggregate

        $closed = 0;
        foreach ($targets as $branch) {
            $bid = $branch?->id;

            // Skip if already closed for this period+branch (unless --force)
            $exists = SalesPeriodSummary::where('period_type', $period)
                ->where('date_from', $dateFrom)
                ->where('date_to',   $dateTo)
                ->where('branch_id', $bid)
                ->exists();

            if ($exists && ! $force) {
                $this->line("  Skipping " . ($branch ? $branch->name : 'All Branches') . " — already closed. Use --force to overwrite.");
                continue;
            }

            $summary = $this->buildSummary($dateFrom, $dateTo, $bid);

            SalesPeriodSummary::updateOrCreate(
                [
                    'period_type' => $period,
                    'date_from'   => $dateFrom,
                    'date_to'     => $dateTo,
                    'branch_id'   => $bid,
                ],
                array_merge($summary, [
                    'period_label'    => $periodLabel,
                    'closed_by'       => $by,
                    'closed_by_name'  => $byName,
                    'closed_at'       => now(),
                ])
            );

            $label = $branch ? $branch->name : 'All Branches';
            $this->info("  ✓ Closed {$period} period [{$dateFrom} → {$dateTo}] for {$label}  Revenue: ₱" . number_format($summary['total_revenue'], 2));
            $closed++;
        }

        $this->info("Period close complete — {$closed} record(s) written.");
        return self::SUCCESS;
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function buildSummary(string $dateFrom, string $dateTo, ?int $branchId): array
    {
        $base = DB::table('sales_transactions')
            ->whereBetween(DB::raw('DATE(transaction_date)'), [$dateFrom, $dateTo])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        $completed = (clone $base)->where('status', 'completed');
        $voided    = (clone $base)->where('status', 'voided');

        $totalRevenue = (float) $completed->sum('total_amount');
        $txnCount     = $completed->count();

        $itemsSold = (int) DB::table('sales_transaction_items as sti')
            ->join('sales_transactions as st', 'sti.transaction_id', '=', 'st.id')
            ->whereBetween(DB::raw('DATE(st.transaction_date)'), [$dateFrom, $dateTo])
            ->where('st.status', 'completed')
            ->when($branchId, fn ($q) => $q->where('st.branch_id', $branchId))
            ->sum('sti.quantity');

        // Payment method breakdown
        $paymentBreakdown = DB::table('sales_transactions')
            ->whereBetween(DB::raw('DATE(transaction_date)'), [$dateFrom, $dateTo])
            ->where('status', 'completed')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as revenue'))
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method')
            ->map(fn ($r) => ['count' => $r->count, 'revenue' => $r->revenue])
            ->toArray();

        // Top product
        $topProduct = DB::table('sales_transaction_items as sti')
            ->join('sales_transactions as st', 'sti.transaction_id', '=', 'st.id')
            ->join('products', 'sti.product_id', '=', 'products.id')
            ->whereNull('products.deleted_at')
            ->whereBetween(DB::raw('DATE(st.transaction_date)'), [$dateFrom, $dateTo])
            ->where('st.status', 'completed')
            ->when($branchId, fn ($q) => $q->where('st.branch_id', $branchId))
            ->select('products.name', DB::raw('SUM(sti.subtotal) as revenue'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('revenue')
            ->first();

        return [
            'total_revenue'        => $totalRevenue,
            'total_tax'            => (float) $completed->sum('tax_amount'),
            'total_discounts'      => (float) $completed->sum('discount_amount'),
            'total_transactions'   => $txnCount,
            'total_items_sold'     => $itemsSold,
            'avg_basket'           => $txnCount > 0 ? round($totalRevenue / $txnCount, 2) : 0,
            'voided_transactions'  => $voided->count(),
            'voided_amount'        => (float) $voided->sum('total_amount'),
            'payment_breakdown'    => $paymentBreakdown,
            'top_product_name'     => $topProduct?->name,
            'top_product_revenue'  => $topProduct?->revenue,
        ];
    }

    private function resolvePeriod(string $period): array
    {
        return match($period) {
            'daily'   => [
                now()->subDay()->toDateString(),
                now()->subDay()->toDateString(),
                now()->subDay()->format('F d, Y'),
            ],
            'weekly'  => [
                now()->subWeek()->startOfWeek()->toDateString(),
                now()->subWeek()->endOfWeek()->toDateString(),
                'Week of ' . now()->subWeek()->startOfWeek()->format('M d') . ' – ' . now()->subWeek()->endOfWeek()->format('M d, Y'),
            ],
            'monthly' => [
                now()->subMonth()->startOfMonth()->toDateString(),
                now()->subMonth()->endOfMonth()->toDateString(),
                now()->subMonth()->format('F Y'),
            ],
            'yearly'  => [
                now()->subYear()->startOfYear()->toDateString(),
                now()->subYear()->endOfYear()->toDateString(),
                'Year ' . now()->subYear()->format('Y'),
            ],
        };
    }
}
