<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\OnlineOrder;
use App\Models\SalesTransaction;
use App\Repositories\BranchInventoryRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OnlineOrderWebController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'branch_id', 'date_from', 'date_to', 'search']);

        $orders = OnlineOrder::with(['items', 'branch'])
            ->when(isset($filters['status']) && $filters['status'] !== 'all', fn ($q) => $q->where('status', $filters['status']))
            ->when(! empty($filters['branch_id']),  fn ($q) => $q->where('pickup_branch_id', $filters['branch_id']))
            ->when(! empty($filters['date_from']),  fn ($q) => $q->whereDate('created_at', '>=', $filters['date_from']))
            ->when(! empty($filters['date_to']),    fn ($q) => $q->whereDate('created_at', '<=', $filters['date_to']))
            ->when(! empty($filters['search']),     fn ($q) => $q->where(fn ($s) =>
                $s->where('order_number', 'like', "%{$filters['search']}%")
                  ->orWhere('first_name',  'like', "%{$filters['search']}%")
                  ->orWhere('last_name',   'like', "%{$filters['search']}%")
                  ->orWhere('email',       'like', "%{$filters['search']}%")
                  ->orWhere('phone',       'like', "%{$filters['search']}%")
            ))
            ->latest()
            ->paginate(20);

        $branches       = Branch::where('is_active', true)->orderBy('name')->get();
        $totalPending   = OnlineOrder::where('status', 'pending')->count();
        $totalConfirmed = OnlineOrder::where('status', 'confirmed')->count();
        $totalReady     = OnlineOrder::where('status', 'ready')->count();
        $totalToday     = OnlineOrder::whereDate('created_at', today())->count();
        $todayRevenue   = OnlineOrder::whereDate('created_at', today())
            ->whereNotIn('status', ['cancelled'])
            ->sum('total_amount');

        return view('pages.online-orders.index', compact(
            'orders', 'branches', 'totalPending', 'totalConfirmed', 'totalReady', 'totalToday', 'todayRevenue'
        ));
    }

    public function show(OnlineOrder $onlineOrder)
    {
        $onlineOrder->load(['items.product', 'branch', 'fulfiller']);
        return view('pages.online-orders.show', compact('onlineOrder'));
    }

    public function update(Request $request, OnlineOrder $onlineOrder)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,processing,ready,completed,cancelled',
        ]);

        $newStatus = $validated['status'];

        if ($newStatus === 'completed') {
            if (! $onlineOrder->pickup_branch_id) {
                return back()->withErrors(['error' => "Cannot complete order {$onlineOrder->order_number}: no pickup branch assigned. Edit the order and assign a branch first."]);
            }

            if (! auth()->user()->employee_id) {
                return back()->withErrors(['error' => 'Your user account is not linked to an employee record. Contact an administrator.']);
            }

            try {
                DB::transaction(function () use ($onlineOrder) {
                    $branchId   = $onlineOrder->pickup_branch_id;
                    $employeeId = auth()->user()->employee_id;
                    $invRepo    = app(BranchInventoryRepository::class);
                    $items      = $onlineOrder->items()->get();

                    // Decrement branch inventory for each item
                    foreach ($items as $item) {
                        if (! $item->product_id) continue;
                        $invRepo->decrementStock($branchId, $item->product_id, $item->quantity);
                    }

                    // sales_transactions.payment_method has no 'cod' — map it to 'cash'
                    $paymentMethod = $onlineOrder->payment_method === 'cod' ? 'cash' : $onlineOrder->payment_method;

                    $txn = SalesTransaction::create([
                        'transaction_number' => SalesTransaction::generateTransactionNumber($branchId),
                        'branch_id'          => $branchId,
                        'cashier_id'         => $employeeId,
                        'customer_id'        => null,
                        'subtotal'           => $onlineOrder->subtotal,
                        'discount_amount'    => 0,
                        'tax_amount'         => $onlineOrder->tax_amount,
                        'total_amount'       => $onlineOrder->total_amount,
                        'amount_tendered'    => $onlineOrder->total_amount,
                        'change_amount'      => 0,
                        'payment_method'     => $paymentMethod,
                        'status'             => 'completed',
                        'transaction_date'   => now(),
                    ]);

                    foreach ($items as $item) {
                        if (! $item->product_id) continue;
                        $txn->items()->create([
                            'product_id'      => $item->product_id,
                            'quantity'        => $item->quantity,
                            'unit_price'      => $item->unit_price,
                            'discount_amount' => 0,
                            'tax_amount'      => round($item->subtotal * 0.12, 2),
                            'subtotal'        => $item->subtotal,
                        ]);
                    }

                    $onlineOrder->update([
                        'status'       => 'completed',
                        'fulfilled_by' => $employeeId,
                        'fulfilled_at' => now(),
                    ]);
                });
            } catch (RuntimeException $e) {
                return back()->withErrors(['error' => $e->getMessage()]);
            }
        } else {
            $onlineOrder->update([
                'status'       => $newStatus,
                'fulfilled_by' => $newStatus === 'ready' ? auth()->user()->employee_id : $onlineOrder->fulfilled_by,
            ]);
        }

        return back()->with('success', "Order {$onlineOrder->order_number} updated to " . ucfirst($newStatus) . '.');
    }
}
