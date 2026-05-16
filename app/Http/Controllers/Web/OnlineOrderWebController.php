<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BranchInventory;
use App\Models\OnlineOrder;
use App\Models\OnlineOrderItem;
use App\Repositories\BranchInventoryRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OnlineOrderWebController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'branch_id', 'date_from', 'date_to']);

        $orders = OnlineOrder::with(['items', 'branch'])
            ->when(isset($filters['status']) && $filters['status'] !== 'all', fn ($q) => $q->where('status', $filters['status']))
            ->latest()
            ->paginate(20);

        $totalPending   = OnlineOrder::where('status', 'pending')->count();
        $totalConfirmed = OnlineOrder::where('status', 'confirmed')->count();
        $totalReady     = OnlineOrder::where('status', 'ready')->count();
        $totalToday     = OnlineOrder::whereDate('created_at', today())->count();
        $todayRevenue   = OnlineOrder::whereDate('created_at', today())
            ->whereNotIn('status', ['cancelled'])
            ->sum('total_amount');

        return view('pages.online-orders.index', compact(
            'orders', 'totalPending', 'totalConfirmed', 'totalReady', 'totalToday', 'todayRevenue'
        ));
    }

    public function update(Request $request, OnlineOrder $onlineOrder)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,processing,ready,completed,cancelled',
        ]);

        $onlineOrder->update([
            'status'       => $validated['status'],
            'fulfilled_by' => in_array($validated['status'], ['completed', 'ready'])
                ? auth()->user()->employee_id
                : $onlineOrder->fulfilled_by,
            'fulfilled_at' => $validated['status'] === 'completed' ? now() : $onlineOrder->fulfilled_at,
        ]);

        if($validated['status'] === 'completed') {
            DB::transaction(function () use ($onlineOrder) {
                OnlineOrderItem::where('online_order_id', $onlineOrder->id)->each(function ($item) use ($onlineOrder) {
                    app(BranchInventoryRepository::class)->decrementStock($onlineOrder->pickup_branch_id, $item->product_id, $item->quantity);
                });
            });
        }

        return back()->with('success', "Order {$onlineOrder->order_number} updated to " . ucfirst($validated['status']) . '.');
    }
}
