<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

/**
 * Shares badge counts with every authenticated view.
 *
 * Register in bootstrap/app.php:
 *   ->withMiddleware(function (Middleware $m) {
 *       $m->web(append: [\App\Http\Middleware\ShareNavigationData::class]);
 *   })
 */
class ShareNavigationData
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (auth()->check()) {
            $user = auth()->user();

            $lowStockCount = DB::table('branch_inventory')
                ->join('products', 'branch_inventory.product_id', '=', 'products.id')
                ->whereRaw('(branch_inventory.quantity_on_hand - branch_inventory.quantity_reserved) <= products.reorder_level')
                ->where('products.is_active', true)
                ->when(! $user->isSuperAdmin() && $user->branch_id,
                    fn ($q) => $q->where('branch_inventory.branch_id', $user->branch_id)
                )
                ->distinct('products.id')
                ->count('products.id');

            $pendingTransfers = DB::table('stock_transfers')
                ->where('status', 'pending')
                ->when(! $user->isSuperAdmin() && $user->branch_id,
                    fn ($q) => $q->where(fn ($s) => $s
                        ->where('from_branch_id', $user->branch_id)
                        ->orWhere('to_branch_id', $user->branch_id)
                    )
                )
                ->count();

            View::share('lowStockCount',    $lowStockCount);
            View::share('pendingTransfers', $pendingTransfers);
        }

        return $next($request);
    }
}
