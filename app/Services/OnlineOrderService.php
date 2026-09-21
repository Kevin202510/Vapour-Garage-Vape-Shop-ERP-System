<?php

namespace App\Services;

use App\Contracts\Repositories\AuditLogRepositoryInterface;
use App\Models\OnlineOrder;
use Illuminate\Support\Facades\DB;

class OnlineOrderService
{
    public function __construct(protected AuditLogRepositoryInterface $auditRepo) {}

    /**
     * Cancel online orders still sitting in 'pending' — never confirmed by
     * staff — past the given age threshold. Safe to run unattended: a
     * pending order has not touched inventory (stock is only decremented on
     * fulfillment, see OnlineOrderWebController::update()), so cancelling
     * one here is a pure status change, not a reversal.
     *
     * @return array{cancelled: int, order_numbers: array<string>}
     */
    public function cancelStale(int $hours = 48): array
    {
        $cutoff = now()->subHours($hours);

        $stale = OnlineOrder::where('status', 'pending')
            ->where('created_at', '<', $cutoff)
            ->get();

        $orderNumbers = [];

        DB::transaction(function () use ($stale, &$orderNumbers) {
            foreach ($stale as $order) {
                $order->update(['status' => 'cancelled']);

                $this->auditRepo->log(
                    tableName: 'online_orders',
                    recordId:  $order->id,
                    action:    'update',
                    oldValues: ['status' => 'pending'],
                    newValues: ['status' => 'cancelled', 'reason' => 'auto-cancelled: never confirmed'],
                    branchId:  $order->pickup_branch_id,
                );

                $orderNumbers[] = $order->order_number;
            }
        });

        return [
            'cancelled'     => count($orderNumbers),
            'order_numbers' => $orderNumbers,
        ];
    }
}
