<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class ShareNavigationData
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (auth()->check()) {
            $user = auth()->user();

            // Use the session-active branch for admins, the assigned branch for everyone else.
            // When a non-admin has no branch (edge case), fall back to no filter (show all).
            $branchId = $user->isSuperAdmin()
                ? $user->activeBranchId()
                : $user->branch_id;

            $lowStockCount = DB::table('branch_inventory')
                ->join('products', 'branch_inventory.product_id', '=', 'products.id')
                ->whereNull('products.deleted_at')
                ->where('products.is_active', true)
                ->whereRaw('(branch_inventory.quantity_on_hand - branch_inventory.quantity_reserved) <= products.reorder_level')
                ->when($branchId, fn ($q) => $q->where('branch_inventory.branch_id', $branchId))
                ->distinct('products.id')
                ->count('products.id');

            $pendingTransfers = DB::table('stock_transfers')
                ->where('status', 'pending')
                ->when($branchId, fn ($q) => $q->where(fn ($s) =>
                    $s->where('from_branch_id', $branchId)->orWhere('to_branch_id', $branchId)
                ))
                ->count();

            $pendingOnlineOrders = \App\Models\OnlineOrder::where('status', 'pending')
                ->when($branchId, fn ($q) => $q->where('pickup_branch_id', $branchId))
                ->count();

            View::share('lowStockCount',       $lowStockCount);
            View::share('pendingTransfers',    $pendingTransfers);
            View::share('pendingOnlineOrders', $pendingOnlineOrders);
        }

        return $next($request);
    }
}
