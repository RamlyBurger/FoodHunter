<?php

namespace App\Patterns\Observer;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Analytics Observer - Tracks queue metrics and analytics
 */
class AnalyticsObserver implements QueueObserver
{
    public function update(Order $order, string $event): void
    {
        $this->trackQueueMetrics($order, $event);
    }

    private function trackQueueMetrics(Order $order, string $event): void
    {
        $metrics = [
            'event' => $event,
            'order_id' => $order->order_id,
            'vendor_id' => $order->vendor_id,
            'queue_number' => $order->pickup?->queue_number,
            'timestamp' => now()->toDateTimeString(),
        ];

        // Calculate wait times
        if ($event === 'ready' && $order->pickup) {
            $waitTime = now()->diffInMinutes($order->created_at);
            $metrics['preparation_time_minutes'] = $waitTime;
        }

        if ($event === 'collected' && $order->pickup) {
            $pickupWaitTime = $order->pickup->collected_at 
                ? now()->diffInMinutes($order->pickup->ready_at)
                : null;
            $metrics['pickup_wait_time_minutes'] = $pickupWaitTime;
        }

        // Log to analytics
        Log::channel('daily')->info('Queue Analytics', $metrics);

        // Store in cache for reporting
        $this->updateAnalyticsCache($order->vendor_id, $metrics);
    }

    private function updateAnalyticsCache(int $vendorId, array $metrics): void
    {
        $cacheKey = "vendor_queue_analytics_{$vendorId}_" . now()->format('Y-m-d');
        
        $analytics = Cache::get($cacheKey, []);
        $analytics[] = $metrics;
        
        Cache::put($cacheKey, $analytics, now()->addDays(7));
    }

    public function getName(): string
    {
        return 'Analytics Observer';
    }
}
