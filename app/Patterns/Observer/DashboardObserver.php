<?php

namespace App\Patterns\Observer;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Dashboard Observer - Updates vendor dashboard statistics
 */
class DashboardObserver implements QueueObserver
{
    public function update(Order $order, string $event): void
    {
        // Log queue event for dashboard analytics
        Log::channel('daily')->info("Queue Event: {$event}", [
            'order_id' => $order->order_id,
            'vendor_id' => $order->vendor_id,
            'queue_number' => $order->pickup?->queue_number,
            'timestamp' => now()->toDateTimeString(),
        ]);

        // Trigger dashboard cache refresh
        $this->refreshDashboardCache($order->vendor_id, $event);
    }

    private function refreshDashboardCache(int $vendorId, string $event): void
    {
        // Clear vendor dashboard cache
        $cacheKey = "vendor_dashboard_{$vendorId}";
        Cache::forget($cacheKey);

        // Cache specific metrics based on event
        if ($event === 'ready') {
            $readyOrders = Order::where('vendor_id', $vendorId)
                ->where('status', 'ready')
                ->count();
                
            Cache::put("vendor_ready_orders_{$vendorId}", $readyOrders, now()->addMinutes(5));
        }

        if ($event === 'collected') {
            $completedToday = Order::where('vendor_id', $vendorId)
                ->where('status', 'completed')
                ->whereDate('completed_at', today())
                ->count();
                
            Cache::put("vendor_completed_today_{$vendorId}", $completedToday, now()->addMinutes(5));
        }
    }

    public function getName(): string
    {
        return 'Dashboard Observer';
    }
}
