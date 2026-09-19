<?php

namespace App\Console\Commands;

use App\Mail\SalesReportMail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendSalesReport extends Command
{
    protected $signature   = 'sales:send-report {period : daily|weekly|monthly|yearly}';
    protected $description = 'Generate and email the sales report for the given period.';

    public function handle(): int
    {
        $period = $this->argument('period');

        if (! in_array($period, ['daily', 'weekly', 'monthly', 'yearly'])) {
            $this->error("Invalid period '{$period}'. Use: daily, weekly, monthly, yearly.");
            return self::FAILURE;
        }

        [$dateFrom, $dateTo, $periodLabel] = $this->resolvePeriod($period);

        $this->info("Generating {$period} report: {$dateFrom} → {$dateTo}");

        // ── Key metrics ────────────────────────────────────────────────────
        $revenue = (float) DB::table('sales_transactions')
            ->whereBetween(DB::raw('DATE(transaction_date)'), [$dateFrom, $dateTo])
            ->where('status', 'completed')
            ->sum('total_amount');

        $txnCount = DB::table('sales_transactions')
            ->whereBetween(DB::raw('DATE(transaction_date)'), [$dateFrom, $dateTo])
            ->where('status', 'completed')
            ->count();

        $itemsSold = (int) DB::table('sales_transaction_items')
            ->join('sales_transactions', 'sales_transaction_items.transaction_id', '=', 'sales_transactions.id')
            ->whereBetween(DB::raw('DATE(sales_transactions.transaction_date)'), [$dateFrom, $dateTo])
            ->where('sales_transactions.status', 'completed')
            ->sum('sales_transaction_items.quantity');

        $stats = [
            'revenue'    => $revenue,
            'txn_count'  => $txnCount,
            'avg_basket' => $txnCount > 0 ? round($revenue / $txnCount, 2) : 0,
            'items_sold' => $itemsSold,
        ];

        // ── Branch breakdown ───────────────────────────────────────────────
        $branchBreakdown = DB::table('sales_transactions')
            ->join('branches', 'sales_transactions.branch_id', '=', 'branches.id')
            ->whereNull('branches.deleted_at')
            ->whereBetween(DB::raw('DATE(transaction_date)'), [$dateFrom, $dateTo])
            ->where('sales_transactions.status', 'completed')
            ->select('branches.name', DB::raw('SUM(total_amount) as revenue'), DB::raw('COUNT(*) as txn_count'))
            ->groupBy('branches.id', 'branches.name')
            ->orderByDesc('revenue')
            ->get()
            ->toArray();

        // ── Top 10 products ────────────────────────────────────────────────
        $topProducts = DB::table('sales_transaction_items as sti')
            ->join('sales_transactions as st', 'sti.transaction_id', '=', 'st.id')
            ->join('products', 'sti.product_id', '=', 'products.id')
            ->whereNull('products.deleted_at')
            ->whereBetween(DB::raw('DATE(st.transaction_date)'), [$dateFrom, $dateTo])
            ->where('st.status', 'completed')
            ->select(
                'products.name',
                'products.sku',
                DB::raw('SUM(sti.quantity) as qty_sold'),
                DB::raw('SUM(sti.subtotal) as revenue'),
            )
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get()
            ->toArray();

        // ── Recipients: all users with 'export reports' permission ────────
        $recipients = User::permission('export reports')->where('is_active', true)->get();

        if ($recipients->isEmpty()) {
            $this->warn('No recipients found with "export reports" permission. Report not sent.');
            return self::SUCCESS;
        }

        $queued = 0;
        $failed = 0;

        foreach ($recipients as $user) {
            try {
                // Queued (SalesReportMail implements ShouldQueue) so a slow/down
                // mail provider doesn't block this command, and Laravel's queue
                // retry policy applies. One recipient's failure to queue doesn't
                // stop the rest.
                Mail::to($user->email, $user->name)->queue(new SalesReportMail(
                    period:          $period,
                    dateFrom:        $dateFrom,
                    dateTo:          $dateTo,
                    periodLabel:     $periodLabel,
                    stats:           $stats,
                    branchBreakdown: $branchBreakdown,
                    topProducts:     $topProducts,
                ));
                $queued++;
            } catch (Throwable $e) {
                $failed++;
                Log::error("sales:send-report failed to queue for {$user->email}: {$e->getMessage()}", ['exception' => $e]);
                $this->error("  ✗ Failed to queue report for {$user->email}: {$e->getMessage()}");
            }
        }

        $this->info("Report queued for {$queued} recipient(s)" . ($failed ? ", {$failed} failed." : '.'));
        return $failed > 0 ? self::FAILURE : self::SUCCESS;
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
                now()->subWeek()->startOfWeek()->format('M d') . ' – ' . now()->subWeek()->endOfWeek()->format('M d, Y'),
            ],
            'monthly' => [
                now()->subMonth()->startOfMonth()->toDateString(),
                now()->subMonth()->endOfMonth()->toDateString(),
                now()->subMonth()->format('F Y'),
            ],
            'yearly'  => [
                now()->subYear()->startOfYear()->toDateString(),
                now()->subYear()->endOfYear()->toDateString(),
                now()->subYear()->format('Y'),
            ],
        };
    }
}
