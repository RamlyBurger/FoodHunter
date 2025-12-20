<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * Cart & Menu Rate Limiting Service
 * 
 * [Security Requirement #94] Limit the number of transactions a single user
 * or device can perform in a given period of time to prevent cart manipulation,
 * menu scraping, and abuse of voucher system.
 * 
 * Rate Limits:
 * - Cart Add: 30 additions per minute (prevents spam adding)
 * - Cart Update: 60 updates per minute (allows quick quantity changes)
 * - Cart Clear: 5 clears per hour (prevents abuse)
 * - Voucher Apply: 10 attempts per hour (prevents brute force)
 * - Menu Browse: 120 requests per minute (allows normal browsing)
 * - Menu Search: 30 searches per minute (prevents scraping)
 * - Wishlist Toggle: 20 toggles per minute (prevents spam)
 */
class CartRateLimiterService
{
    // Rate limit constants
    private const MAX_CART_ADDS_PER_MINUTE = 30;
    private const MAX_CART_UPDATES_PER_MINUTE = 60;
    private const MAX_CART_CLEARS_PER_HOUR = 5;
    private const MAX_VOUCHER_ATTEMPTS_PER_HOUR = 10;
    private const MAX_MENU_REQUESTS_PER_MINUTE = 120;
    private const MAX_MENU_SEARCHES_PER_MINUTE = 30;
    private const MAX_WISHLIST_TOGGLES_PER_MINUTE = 20;

    /**
     * Check if user can add items to cart
     * 
     * @param int $userId
     * @return array ['allowed' => bool, 'remaining' => int, 'reset_in' => int, 'message' => string]
     */
    public function canAddToCart(int $userId): array
    {
        return $this->checkRateLimit(
            "cart_add:{$userId}",
            self::MAX_CART_ADDS_PER_MINUTE,
            60,
            'Too many items added to cart. Please wait a moment.'
        );
    }

    /**
     * Check if user can update cart items
     * 
     * @param int $userId
     * @return array
     */
    public function canUpdateCart(int $userId): array
    {
        return $this->checkRateLimit(
            "cart_update:{$userId}",
            self::MAX_CART_UPDATES_PER_MINUTE,
            60,
            'Too many cart updates. Please slow down.'
        );
    }

    /**
     * Check if user can clear cart
     * 
     * @param int $userId
     * @return array
     */
    public function canClearCart(int $userId): array
    {
        return $this->checkRateLimit(
            "cart_clear:{$userId}",
            self::MAX_CART_CLEARS_PER_HOUR,
            3600,
            'Too many cart clear attempts. Maximum 5 per hour.'
        );
    }

    /**
     * Check if user can apply vouchers
     * 
     * @param int $userId
     * @return array
     */
    public function canApplyVoucher(int $userId): array
    {
        return $this->checkRateLimit(
            "voucher_apply:{$userId}",
            self::MAX_VOUCHER_ATTEMPTS_PER_HOUR,
            3600,
            'Too many voucher attempts. Please try again later.'
        );
    }

    /**
     * Check if user can browse menu
     * 
     * @param int|string $identifier (user_id or IP)
     * @return array
     */
    public function canBrowseMenu($identifier): array
    {
        return $this->checkRateLimit(
            "menu_browse:{$identifier}",
            self::MAX_MENU_REQUESTS_PER_MINUTE,
            60,
            'Too many menu requests. Please slow down.'
        );
    }

    /**
     * Check if user can search menu
     * 
     * @param int|string $identifier (user_id or IP)
     * @return array
     */
    public function canSearchMenu($identifier): array
    {
        return $this->checkRateLimit(
            "menu_search:{$identifier}",
            self::MAX_MENU_SEARCHES_PER_MINUTE,
            60,
            'Too many search requests. Please wait a moment.'
        );
    }

    /**
     * Check if user can toggle wishlist
     * 
     * @param int $userId
     * @return array
     */
    public function canToggleWishlist(int $userId): array
    {
        return $this->checkRateLimit(
            "wishlist_toggle:{$userId}",
            self::MAX_WISHLIST_TOGGLES_PER_MINUTE,
            60,
            'Too many wishlist actions. Please slow down.'
        );
    }

    /**
     * Generic rate limit checker
     * 
     * @param string $key Cache key
     * @param int $maxAttempts Maximum allowed attempts
     * @param int $decaySeconds Time window in seconds
     * @param string $errorMessage Message when limit exceeded
     * @return array
     */
    private function checkRateLimit(string $key, int $maxAttempts, int $decaySeconds, string $errorMessage): array
    {
        $attempts = Cache::get($key, 0);
        $remaining = max(0, $maxAttempts - $attempts);

        if ($attempts >= $maxAttempts) {
            $ttl = Cache::get("{$key}:ttl", $decaySeconds);
            
            // Log rate limit violation
            Log::warning('Cart/Menu rate limit exceeded', [
                'key' => $key,
                'attempts' => $attempts,
                'max_allowed' => $maxAttempts,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toIso8601String(),
            ]);

            return [
                'allowed' => false,
                'remaining' => 0,
                'reset_in' => $ttl,
                'message' => $errorMessage,
            ];
        }

        return [
            'allowed' => true,
            'remaining' => $remaining - 1,
            'reset_in' => $decaySeconds,
            'message' => 'Rate limit OK',
        ];
    }

    /**
     * Record a rate-limited action
     * 
     * @param int $userId
     * @param string $action Type of action (cart_add, cart_update, etc.)
     * @return void
     */
    public function recordAction(int $userId, string $action): void
    {
        $actionConfig = [
            'cart_add' => [
                'key' => "cart_add:{$userId}",
                'ttl' => 60,
                'max' => self::MAX_CART_ADDS_PER_MINUTE,
            ],
            'cart_update' => [
                'key' => "cart_update:{$userId}",
                'ttl' => 60,
                'max' => self::MAX_CART_UPDATES_PER_MINUTE,
            ],
            'cart_clear' => [
                'key' => "cart_clear:{$userId}",
                'ttl' => 3600,
                'max' => self::MAX_CART_CLEARS_PER_HOUR,
            ],
            'voucher_apply' => [
                'key' => "voucher_apply:{$userId}",
                'ttl' => 3600,
                'max' => self::MAX_VOUCHER_ATTEMPTS_PER_HOUR,
            ],
            'menu_browse' => [
                'key' => "menu_browse:{$userId}",
                'ttl' => 60,
                'max' => self::MAX_MENU_REQUESTS_PER_MINUTE,
            ],
            'menu_search' => [
                'key' => "menu_search:{$userId}",
                'ttl' => 60,
                'max' => self::MAX_MENU_SEARCHES_PER_MINUTE,
            ],
            'wishlist_toggle' => [
                'key' => "wishlist_toggle:{$userId}",
                'ttl' => 60,
                'max' => self::MAX_WISHLIST_TOGGLES_PER_MINUTE,
            ],
        ];

        if (!isset($actionConfig[$action])) {
            Log::warning("Unknown action type for rate limiting: {$action}");
            return;
        }

        $config = $actionConfig[$action];
        $key = $config['key'];
        $ttl = $config['ttl'];

        $attempts = Cache::get($key, 0);
        Cache::put($key, $attempts + 1, $ttl);
        
        // Store TTL for reset calculation
        if ($attempts === 0) {
            Cache::put("{$key}:ttl", $ttl, $ttl);
        }

        // Log action for audit trail
        Log::info('Cart/Menu action recorded', [
            'action' => $action,
            'user_id' => $userId,
            'attempts' => $attempts + 1,
            'max_allowed' => $config['max'],
            'ip_address' => request()->ip(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get rate limit status for debugging
     * 
     * @param int $userId
     * @return array
     */
    public function getRateLimitStatus(int $userId): array
    {
        return [
            'cart_add' => [
                'current' => Cache::get("cart_add:{$userId}", 0),
                'limit' => self::MAX_CART_ADDS_PER_MINUTE,
                'window' => '1 minute',
            ],
            'cart_update' => [
                'current' => Cache::get("cart_update:{$userId}", 0),
                'limit' => self::MAX_CART_UPDATES_PER_MINUTE,
                'window' => '1 minute',
            ],
            'cart_clear' => [
                'current' => Cache::get("cart_clear:{$userId}", 0),
                'limit' => self::MAX_CART_CLEARS_PER_HOUR,
                'window' => '1 hour',
            ],
            'voucher_apply' => [
                'current' => Cache::get("voucher_apply:{$userId}", 0),
                'limit' => self::MAX_VOUCHER_ATTEMPTS_PER_HOUR,
                'window' => '1 hour',
            ],
            'menu_browse' => [
                'current' => Cache::get("menu_browse:{$userId}", 0),
                'limit' => self::MAX_MENU_REQUESTS_PER_MINUTE,
                'window' => '1 minute',
            ],
            'menu_search' => [
                'current' => Cache::get("menu_search:{$userId}", 0),
                'limit' => self::MAX_MENU_SEARCHES_PER_MINUTE,
                'window' => '1 minute',
            ],
            'wishlist_toggle' => [
                'current' => Cache::get("wishlist_toggle:{$userId}", 0),
                'limit' => self::MAX_WISHLIST_TOGGLES_PER_MINUTE,
                'window' => '1 minute',
            ],
        ];
    }

    /**
     * Reset rate limit for a user (admin function)
     * 
     * @param int $userId
     * @param string|null $action Specific action to reset, or null for all
     * @return void
     */
    public function resetRateLimit(int $userId, ?string $action = null): void
    {
        $keys = $action 
            ? ["{$action}:{$userId}"]
            : [
                "cart_add:{$userId}",
                "cart_update:{$userId}",
                "cart_clear:{$userId}",
                "voucher_apply:{$userId}",
                "menu_browse:{$userId}",
                "menu_search:{$userId}",
                "wishlist_toggle:{$userId}",
            ];

        foreach ($keys as $key) {
            Cache::forget($key);
            Cache::forget("{$key}:ttl");
        }

        $adminId = null;
        if (Auth::check() && Auth::user()) {
            $adminId = Auth::user()->user_id ?? Auth::id();
        }

        Log::info('Rate limit reset', [
            'user_id' => $userId,
            'action' => $action ?? 'all',
            'admin_id' => $adminId,
        ]);
    }
}
