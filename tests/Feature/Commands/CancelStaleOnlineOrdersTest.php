<?php

namespace Tests\Feature\Commands;

use App\Models\Branch;
use App\Models\OnlineOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancelStaleOnlineOrdersTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(array $overrides = []): OnlineOrder
    {
        static $counter = 1;

        $order = OnlineOrder::create(array_merge([
            'order_number'     => 'VGM-TEST' . str_pad((string) $counter++, 4, '0', STR_PAD_LEFT),
            'first_name'       => 'Test',
            'last_name'        => 'Customer',
            'email'            => 'test@example.com',
            'phone'            => '09171234567',
            'address'          => '123 Test St',
            'city'             => 'Quezon City',
            'pickup_branch_id' => Branch::factory()->create()->id,
            'payment_method'   => 'gcash',
            'subtotal'         => 100,
            'tax_amount'       => 12,
            'total_amount'     => 112,
            'status'           => 'pending',
        ], $overrides));

        // created_at has a fillable/timestamps guard against mass assignment
        // via create(), so backdate it explicitly for the "stale" scenario.
        if (isset($overrides['created_at'])) {
            $order->forceFill(['created_at' => $overrides['created_at']])->save();
        }

        return $order;
    }

    public function test_it_cancels_a_pending_order_older_than_the_threshold(): void
    {
        $stale = $this->makeOrder(['created_at' => now()->subHours(72)]);

        $this->artisan('orders:cancel-stale', ['--hours' => 48])->assertExitCode(0);

        $this->assertSame('cancelled', $stale->refresh()->status);
        $this->assertDatabaseHas('audit_logs', [
            'table_name' => 'online_orders',
            'record_id'  => $stale->id,
            'action'     => 'update',
        ]);
    }

    public function test_it_leaves_a_recent_pending_order_untouched(): void
    {
        $recent = $this->makeOrder(['created_at' => now()->subHours(2)]);

        $this->artisan('orders:cancel-stale', ['--hours' => 48])->assertExitCode(0);

        $this->assertSame('pending', $recent->refresh()->status);
    }

    public function test_it_leaves_non_pending_orders_untouched_regardless_of_age(): void
    {
        $confirmed = $this->makeOrder(['status' => 'confirmed', 'created_at' => now()->subHours(72)]);

        $this->artisan('orders:cancel-stale', ['--hours' => 48])->assertExitCode(0);

        $this->assertSame('confirmed', $confirmed->refresh()->status);
    }
}
