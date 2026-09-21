<?php

namespace App\Console\Commands;

use App\Services\OnlineOrderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class CancelStaleOnlineOrders extends Command
{
    protected $signature   = 'orders:cancel-stale {--hours=48 : Age in hours after which an unconfirmed pending order is cancelled}';
    protected $description = 'Cancel online orders still pending (never confirmed by staff) past the given age.';

    public function __construct(protected OnlineOrderService $service)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $hours = (int) $this->option('hours');

        try {
            $result = $this->service->cancelStale($hours);
        } catch (Throwable $e) {
            Log::error("orders:cancel-stale failed: {$e->getMessage()}", ['exception' => $e]);
            $this->error("Failed: {$e->getMessage()}");
            return self::FAILURE;
        }

        if ($result['cancelled'] === 0) {
            $this->info("No pending orders older than {$hours}h found.");
            return self::SUCCESS;
        }

        $this->info("Cancelled {$result['cancelled']} stale pending order(s):");
        foreach ($result['order_numbers'] as $number) {
            $this->line("  - {$number}");
        }

        return self::SUCCESS;
    }
}
