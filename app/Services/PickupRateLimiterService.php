<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Pickup Rate Limiter Service
 * 
 * [94] Limit the number of transactions a single user or device can perform in a given 
 * period of time. The transactions/time should be above the actual business requirement, 
 * but low enough to deter automated attacks.
 * 
 * Prevents queue gaming, spam pickups, and abuse of the queue management system.
 */
class PickupRateLimiterService
{
    /**
     * Maximum pickup status checks per minute
     */
    private const MAX_STATUS_CHECKS_PER_MINUTE = 10;
    
    /**
     * Maximum pickup updates per hour
     */
    private const MAX_PICKUP_UPDATES_PER_HOUR = 5;
    
    /**
     * Maximum queue position requests per minute
     */
    private const MAX_QUEUE_REQUESTS_PER_MINUTE = 15;
    
    /**
     * Maximum pickup cancellations per day
     */
    private const MAX_CANCELLATIONS_PER_DAY = 3;
    
    /**
     * Check if user can perform pickup status check
     * 
     * @param int $userId
     * @return array ['allowed' => bool, 'remaining' => int, 'reset_in' => int]
     */
    public function canCheckPickupStatus(int $userId): array
    {
        $key = "pickup_status_check:{$userId}";
        $ttl = 60; // 1 minute
        
        return $this->checkRateLimit(
            $key, 
            self::MAX_STATUS_CHECKS_PER_MINUTE, 
            $ttl,
            'pickup_status_check'
        );
    }
    
    /**
     * Check if user can update pickup information
     * 
     * @param int $userId
     * @return array
     */
    public function canUpdatePickup(int $userId): array
    {
        $key = "pickup_update:{$userId}";
        $ttl = 3600; // 1 hour
        
        return $this->checkRateLimit(
            $key, 
            self::MAX_PICKUP_UPDATES_PER_HOUR, 
            $ttl,
            'pickup_update'
        );
    }
    
    /**
     * Check if user can request queue position
     * 
     * @param int $userId
     * @return array
     */
    public function canCheckQueuePosition(int $userId): array
    {
        $key = "queue_position_check:{$userId}";
        $ttl = 60; // 1 minute
        
        return $this->checkRateLimit(
            $key, 
            self::MAX_QUEUE_REQUESTS_PER_MINUTE, 
            $ttl,
            'queue_position_check'
        );
    }
    
    /**
     * Check if user can cancel pickup
     * 
     * @param int $userId
     * @return array
     */
    public function canCancelPickup(int $userId): array
    {
        $key = "pickup_cancel:{$userId}";
        $ttl = 86400; // 24 hours
        
        return $this->checkRateLimit(
            $key, 
            self::MAX_CANCELLATIONS_PER_DAY, 
            $ttl,
            'pickup_cancellation'
        );
    }
    
    /**
     * Check if vendor can update queue
     * 
     * @param int $vendorId
     * @return array
     */
    public function canVendorUpdateQueue(int $vendorId): array
    {
        $key = "vendor_queue_update:{$vendorId}";
        $ttl = 60; // 1 minute
        $maxUpdates = 30; // Vendors need higher limits
        
        return $this->checkRateLimit(
            $key, 
            $maxUpdates, 
            $ttl,
            'vendor_queue_update'
        );
    }
    
    /**
     * Generic rate limit checker
     * 
     * @param string $key
     * @param int $maxAttempts
     * @param int $ttl
     * @param string $action
     * @return array
     */
    private function checkRateLimit(string $key, int $maxAttempts, int $ttl, string $action): array
    {
        $attempts = Cache::get($key, 0);
        
        if ($attempts >= $maxAttempts) {
            // Log rate limit violation
            Log::warning('Rate limit exceeded', [
                'action' => $action,
                'key' => $key,
                'attempts' => $attempts,
                'max_allowed' => $maxAttempts,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toIso8601String()
            ]);
            
            return [
                'allowed' => false,
                'remaining' => 0,
                'reset_in' => Cache::get($key . ':ttl', $ttl),
                'message' => 'Rate limit exceeded. Please try again later.'
            ];
        }
        
        return [
            'allowed' => true,
            'remaining' => $maxAttempts - $attempts - 1,
            'reset_in' => $ttl
        ];
    }
    
    /**
     * Record a pickup action
     * 
     * @param int $userId
     * @param string $action
     * @return void
     */
    public function recordAction(int $userId, string $action): void
    {
        $keyMap = [
            'status_check' => 'pickup_status_check',
            'update' => 'pickup_update',
            'queue_check' => 'queue_position_check',
            'cancel' => 'pickup_cancel',
        ];
        
        $ttlMap = [
            'status_check' => 60,
            'update' => 3600,
            'queue_check' => 60,
            'cancel' => 86400,
        ];
        
        if (!isset($keyMap[$action])) {
            return;
        }
        
        $key = $keyMap[$action] . ":{$userId}";
        $ttl = $ttlMap[$action];
        
        $attempts = Cache::get($key, 0);
        Cache::put($key, $attempts + 1, $ttl);
        Cache::put($key . ':ttl', $ttl, $ttl);
        
        // Log the action
        Log::info('Pickup action recorded', [
            'user_id' => $userId,
            'action' => $action,
            'attempts' => $attempts + 1,
            'ip_address' => request()->ip(),
            'timestamp' => now()->toIso8601String()
        ]);
    }
    
    /**
     * Record vendor queue action
     * 
     * @param int $vendorId
     * @return void
     */
    public function recordVendorQueueUpdate(int $vendorId): void
    {
        $key = "vendor_queue_update:{$vendorId}";
        $ttl = 60;
        
        $attempts = Cache::get($key, 0);
        Cache::put($key, $attempts + 1, $ttl);
        Cache::put($key . ':ttl', $ttl, $ttl);
    }
    
    /**
     * Reset rate limit for a user (admin function)
     * 
     * @param int $userId
     * @param string $action
     * @return void
     */
    public function resetUserLimit(int $userId, string $action): void
    {
        $keyMap = [
            'status_check' => 'pickup_status_check',
            'update' => 'pickup_update',
            'queue_check' => 'queue_position_check',
            'cancel' => 'pickup_cancel',
        ];
        
        if (isset($keyMap[$action])) {
            $key = $keyMap[$action] . ":{$userId}";
            Cache::forget($key);
            Cache::forget($key . ':ttl');
            
            Log::info('Rate limit reset', [
                'user_id' => $userId,
                'action' => $action,
                'reset_by' => auth()->id(),
                'timestamp' => now()->toIso8601String()
            ]);
        }
    }
    
    /**
     * Get current rate limit status for a user
     * 
     * @param int $userId
     * @return array
     */
    public function getUserLimitStatus(int $userId): array
    {
        return [
            'status_checks' => [
                'current' => Cache::get("pickup_status_check:{$userId}", 0),
                'max' => self::MAX_STATUS_CHECKS_PER_MINUTE,
                'window' => '1 minute'
            ],
            'updates' => [
                'current' => Cache::get("pickup_update:{$userId}", 0),
                'max' => self::MAX_PICKUP_UPDATES_PER_HOUR,
                'window' => '1 hour'
            ],
            'queue_checks' => [
                'current' => Cache::get("queue_position_check:{$userId}", 0),
                'max' => self::MAX_QUEUE_REQUESTS_PER_MINUTE,
                'window' => '1 minute'
            ],
            'cancellations' => [
                'current' => Cache::get("pickup_cancel:{$userId}", 0),
                'max' => self::MAX_CANCELLATIONS_PER_DAY,
                'window' => '24 hours'
            ]
        ];
    }
}
