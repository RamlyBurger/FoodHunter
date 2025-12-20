<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Pickup;
use Carbon\Carbon;

/**
 * Pickup Data Protection Service
 * 
 * [132] Protect all cached or temporary copies of sensitive data stored on the server 
 * from unauthorized access and purge those temporary working files as soon as they are 
 * no longer required.
 * 
 * Manages secure caching and automatic purging of pickup-related sensitive data.
 */
class PickupDataProtectionService
{
    /**
     * Cache TTL for active pickups (15 minutes)
     */
    private const ACTIVE_PICKUP_TTL = 900;
    
    /**
     * Cache TTL for completed pickups (5 minutes)
     */
    private const COMPLETED_PICKUP_TTL = 300;
    
    /**
     * Time threshold for purging old pickup data (30 days)
     */
    private const PURGE_THRESHOLD_DAYS = 30;
    
    /**
     * Get pickup data with secure caching
     * 
     * @param int $pickupId
     * @param int $userId
     * @return array|null
     */
    public function getSecurePickupData(int $pickupId, int $userId): ?array
    {
        // Check if user has access to this pickup
        if (!$this->verifyPickupAccess($pickupId, $userId)) {
            Log::warning('Unauthorized pickup data access attempt', [
                'pickup_id' => $pickupId,
                'user_id' => $userId,
                'ip_address' => request()->ip(),
                'timestamp' => now()->toIso8601String()
            ]);
            return null;
        }
        
        // Try to get from cache first
        $cacheKey = $this->getPickupCacheKey($pickupId, $userId);
        
        $pickupData = Cache::remember($cacheKey, self::ACTIVE_PICKUP_TTL, function () use ($pickupId) {
            $pickup = Pickup::with(['order', 'order.vendor'])
                ->where('pickup_id', $pickupId)
                ->first();
            
            if (!$pickup) {
                return null;
            }
            
            return $this->sanitizePickupData($pickup);
        });
        
        return $pickupData;
    }
    
    /**
     * Cache pickup data securely
     * 
     * @param int $pickupId
     * @param int $userId
     * @param array $data
     * @param bool $isCompleted
     * @return void
     */
    public function cachePickupData(int $pickupId, int $userId, array $data, bool $isCompleted = false): void
    {
        $cacheKey = $this->getPickupCacheKey($pickupId, $userId);
        $ttl = $isCompleted ? self::COMPLETED_PICKUP_TTL : self::ACTIVE_PICKUP_TTL;
        
        // Sanitize data before caching
        $sanitizedData = $this->sanitizePickupData($data);
        
        Cache::put($cacheKey, $sanitizedData, $ttl);
        
        Log::info('Pickup data cached', [
            'pickup_id' => $pickupId,
            'user_id' => $userId,
            'ttl' => $ttl,
            'is_completed' => $isCompleted,
            'timestamp' => now()->toIso8601String()
        ]);
    }
    
    /**
     * Purge pickup cache for a specific pickup
     * 
     * @param int $pickupId
     * @param int $userId
     * @return void
     */
    public function purgePickupCache(int $pickupId, int $userId): void
    {
        $cacheKey = $this->getPickupCacheKey($pickupId, $userId);
        
        if (Cache::has($cacheKey)) {
            Cache::forget($cacheKey);
            
            Log::info('Pickup cache purged', [
                'pickup_id' => $pickupId,
                'user_id' => $userId,
                'timestamp' => now()->toIso8601String()
            ]);
        }
    }
    
    /**
     * Purge all cached data for a user
     * 
     * @param int $userId
     * @return void
     */
    public function purgeUserPickupCache(int $userId): void
    {
        // Get all pickup IDs for the user
        $pickupIds = DB::table('pickups')
            ->join('orders', 'pickups.order_id', '=', 'orders.order_id')
            ->where('orders.user_id', $userId)
            ->pluck('pickups.pickup_id');
        
        foreach ($pickupIds as $pickupId) {
            $this->purgePickupCache($pickupId, $userId);
        }
        
        Log::info('All pickup cache purged for user', [
            'user_id' => $userId,
            'pickups_purged' => $pickupIds->count(),
            'timestamp' => now()->toIso8601String()
        ]);
    }
    
    /**
     * Purge completed pickup data older than threshold
     * 
     * @return int Number of records purged
     */
    public function purgeOldPickupData(): int
    {
        $thresholdDate = Carbon::now()->subDays(self::PURGE_THRESHOLD_DAYS);
        
        // Get pickups to purge
        $pickupsToPurge = Pickup::where('pickup_status', 'completed')
            ->where('updated_at', '<', $thresholdDate)
            ->with('order')
            ->get();
        
        $purgedCount = 0;
        
        foreach ($pickupsToPurge as $pickup) {
            // Purge from cache
            if ($pickup->order) {
                $this->purgePickupCache($pickup->pickup_id, $pickup->order->user_id);
            }
            
            // Optionally anonymize sensitive data instead of deleting
            $pickup->update([
                'pickup_instructions' => null,
                'special_requests' => null,
            ]);
            
            $purgedCount++;
        }
        
        Log::info('Old pickup data purged', [
            'purged_count' => $purgedCount,
            'threshold_date' => $thresholdDate->toIso8601String(),
            'timestamp' => now()->toIso8601String()
        ]);
        
        return $purgedCount;
    }
    
    /**
     * Purge temporary queue data
     * 
     * @return void
     */
    public function purgeTemporaryQueueData(): void
    {
        // Clear all queue position caches
        $pattern = 'queue_position:*';
        
        // Note: This requires Redis. For file/database cache, implement differently
        if (config('cache.default') === 'redis') {
            $keys = Cache::getRedis()->keys($pattern);
            foreach ($keys as $key) {
                Cache::forget(str_replace(config('cache.prefix') . ':', '', $key));
            }
        }
        
        Log::info('Temporary queue data purged', [
            'timestamp' => now()->toIso8601String()
        ]);
    }
    
    /**
     * Sanitize pickup data for storage/caching
     * 
     * @param mixed $data
     * @return array
     */
    private function sanitizePickupData($data): array
    {
        if (is_object($data)) {
            $data = $data->toArray();
        }
        
        // Remove sensitive fields that shouldn't be cached
        $sensitiveFields = [
            'payment_details',
            'card_last_four',
            'transaction_id',
        ];
        
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                unset($data[$field]);
            }
        }
        
        // Ensure only necessary data is cached
        return [
            'pickup_id' => $data['pickup_id'] ?? null,
            'order_id' => $data['order_id'] ?? null,
            'pickup_time' => $data['pickup_time'] ?? null,
            'pickup_status' => $data['pickup_status'] ?? null,
            'queue_position' => $data['queue_position'] ?? null,
            'estimated_ready_time' => $data['estimated_ready_time'] ?? null,
            'vendor_name' => $data['order']['vendor']['name'] ?? null,
            'vendor_location' => $data['order']['vendor']['location'] ?? null,
        ];
    }
    
    /**
     * Verify user has access to pickup data
     * 
     * @param int $pickupId
     * @param int $userId
     * @return bool
     */
    private function verifyPickupAccess(int $pickupId, int $userId): bool
    {
        return DB::table('pickups')
            ->join('orders', 'pickups.order_id', '=', 'orders.order_id')
            ->where('pickups.pickup_id', $pickupId)
            ->where('orders.user_id', $userId)
            ->exists();
    }
    
    /**
     * Generate secure cache key for pickup
     * 
     * @param int $pickupId
     * @param int $userId
     * @return string
     */
    private function getPickupCacheKey(int $pickupId, int $userId): string
    {
        // Include user ID in cache key to prevent unauthorized access
        return "pickup_data:{$userId}:{$pickupId}";
    }
    
    /**
     * Get cache statistics
     * 
     * @return array
     */
    public function getCacheStatistics(): array
    {
        // This is a simplified version. Actual implementation depends on cache driver
        return [
            'active_pickup_ttl' => self::ACTIVE_PICKUP_TTL,
            'completed_pickup_ttl' => self::COMPLETED_PICKUP_TTL,
            'purge_threshold_days' => self::PURGE_THRESHOLD_DAYS,
            'cache_driver' => config('cache.default'),
        ];
    }
    
    /**
     * Check if pickup data is in cache
     * 
     * @param int $pickupId
     * @param int $userId
     * @return bool
     */
    public function isPickupCached(int $pickupId, int $userId): bool
    {
        $cacheKey = $this->getPickupCacheKey($pickupId, $userId);
        return Cache::has($cacheKey);
    }
    
    /**
     * Encrypt sensitive pickup instructions
     * 
     * @param string $instructions
     * @return string
     */
    public function encryptInstructions(string $instructions): string
    {
        return encrypt($instructions);
    }
    
    /**
     * Decrypt sensitive pickup instructions
     * 
     * @param string $encryptedInstructions
     * @return string
     */
    public function decryptInstructions(string $encryptedInstructions): string
    {
        try {
            return decrypt($encryptedInstructions);
        } catch (\Exception $e) {
            Log::error('Failed to decrypt pickup instructions', [
                'error' => $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ]);
            return '';
        }
    }
}
