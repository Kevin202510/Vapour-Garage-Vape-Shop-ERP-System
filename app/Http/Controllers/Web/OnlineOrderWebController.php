<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Concerns\ResolvesActiveBranch;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\OnlineOrder;
use App\Models\SalesTransaction;
use App\Repositories\BranchInventoryRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OnlineOrderWebController extends Controller
{
    use AuthorizesRequests, ResolvesActiveBranch;

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'branch_id', 'date_from', 'date_to', 'search']);
        $user    = auth()->user();
        $effectiveBranchId = $this->resolveEffectiveBranchId($user);

        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $activeBranchLabel = $effectiveBranchId
            ? ($branches->firstWhere('id', $effectiveBranchId)?->name ?? 'Selected Branch')
            : 'All Branches';

        $orders = OnlineOrder::with(['items', 'branch'])
            ->when(isset($filters['status']) && $filters['status'] !== 'all', fn ($q) => $q->where('status', $filters['status']))
            ->when($effectiveBranchId,             fn ($q) => $q->where('pickup_branch_id', $effectiveBranchId))
            ->when(! empty($filters['date_from']), fn ($q) => $q->whereDate('created_at', '>=', $filters['date_from']))
            ->when(! empty($filters['date_to']),   fn ($q) => $q->whereDate('created_at', '<=', $filters['date_to']))
            ->when(! empty($filters['search']),    fn ($q) => $q->where(fn ($s) =>
                $s->where('order_number', 'like', "%{$filters['search']}%")
                  ->orWhere('first_name',  'like', "%{$filters['search']}%")
                  ->orWhere('last_name',   'like', "%{$filters['search']}%")
                  ->orWhere('email',       'like', "%{$filters['search']}%")
                  ->orWhere('phone',       'like', "%{$filters['search']}%")
            ))
            ->latest()
            ->paginate(20);

        $totalPending   = OnlineOrder::when($effectiveBranchId, fn ($q) => $q->where('pickup_branch_id', $effectiveBranchId))->where('status', 'pending')->count();
        $totalConfirmed = OnlineOrder::when($effectiveBranchId, fn ($q) => $q->where('pickup_branch_id', $effectiveBranchId))->where('status', 'confirmed')->count();
        $totalReady     = OnlineOrder::when($effectiveBranchId, fn ($q) => $q->where('pickup_branch_id', $effectiveBranchId))->where('status', 'ready')->count();
        $totalToday     = OnlineOrder::when($effectiveBranchId, fn ($q) => $q->where('pickup_branch_id', $effectiveBranchId))->whereDate('created_at', today())->count();
        $todayRevenue   = OnlineOrder::when($effectiveBranchId, fn ($q) => $q->where('pickup_branch_id', $effectiveBranchId))->whereDate('created_at', today())->whereNotIn('status', ['cancelled'])->sum('total_amount');

        return view('pages.online-orders.index', compact(
            'orders', 'branches', 'totalPending', 'totalConfirmed', 'totalReady', 'totalToday', 'todayRevenue',
            'effectiveBranchId', 'activeBranchLabel'
        ));
    }

    public function show(OnlineOrder $onlineOrder)
    {
        $onlineOrder->load(['items.product', 'branch', 'fulfiller']);
        return view('pages.online-orders.show', compact('onlineOrder'));
    }

    public function export(Request $request)
    {
        $this->authorize('view sales');

        $filters = $request->only(['status', 'branch_id', 'date_from', 'date_to', 'search']);
        $user    = auth()->user();
        $effectiveBranchId = $this->resolveEffectiveBranchId($user);

        $orders = OnlineOrder::with(['branch'])
            ->when(isset($filters['status']) && $filters['status'] !== 'all', fn ($q) => $q->where('status', $filters['status']))
            ->when($effectiveBranchId,             fn ($q) => $q->where('pickup_branch_id', $effectiveBranchId))
            ->when(! empty($filters['date_from']), fn ($q) => $q->whereDate('created_at', '>=', $filters['date_from']))
            ->when(! empty($filters['date_to']),   fn ($q) => $q->whereDate('created_at', '<=', $filters['date_to']))
            ->when(! empty($filters['search']),    fn ($q) => $q->where(fn ($s) =>
                $s->where('order_number', 'like', "%{$filters['search']}%")
                  ->orWhere('first_name',  'like', "%{$filters['search']}%")
                  ->orWhere('last_name',   'like', "%{$filters['search']}%")
                  ->orWhere('email',       'like', "%{$filters['search']}%")
                  ->orWhere('phone',       'like', "%{$filters['search']}%")
            ))
            ->latest()
            ->get();

        $filename = 'online-orders-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Order Number', 'Customer', 'Email', 'Phone', 'Pickup Branch', 'Payment Method', 'Status', 'Total Amount', 'Placed At']);
            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->order_number,
                    $order->full_name,
                    $order->email,
                    $order->phone,
                    $order->branch->name ?? 'N/A',
                    ucwords(str_replace('_', ' ', $order->payment_method)),
                    $order->status_label,
                    number_format($order->total_amount, 2),
                    optional($order->created_at)->format('Y-m-d H:i'),
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
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
