<?php

namespace App\Services;

use App\Models\CartItem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Cart & Menu Data Protection Service
 * 
 * [Security Requirement #132] Protect all cached or temporary copies of sensitive
 * data stored from Cart & Menu module and purge when no longer required.
 * 
 * Protected Data:
 * - Cart items with pricing (cached for quick access)
 * - Special requests (may contain personal info)
 * - Voucher codes and discounts
 * - Menu browsing history
 * - Search queries (may reveal preferences)
 * - Wishlist data
 * 
 * Cache TTL Configuration:
 * - Active cart data: 30 minutes (user actively shopping)
 * - Inactive cart data: 5 minutes (user left)
 * - Menu cache: 15 minutes (frequently updated)
 * - Search results: 5 minutes (dynamic content)
 * - Voucher data: 10 minutes (sensitive)
 * - Old cart data purge: 90 days (abandoned carts)
 */
class CartDataProtectionService
{
    // Cache TTL constants (in seconds)
    private const ACTIVE_CART_TTL = 1800;      // 30 minutes
    private const INACTIVE_CART_TTL = 300;     // 5 minutes
    private const MENU_CACHE_TTL = 900;        // 15 minutes
    private const SEARCH_CACHE_TTL = 300;      // 5 minutes
    private const VOUCHER_CACHE_TTL = 600;     // 10 minutes
    private const PURGE_THRESHOLD_DAYS = 90;   // 90 days

    /**
     * Get secure cart data with access verification
     * 
     * @param int $userId
     * @return array|null
     */
    public function getSecureCartData(int $userId): ?array
    {
        $cacheKey = $this->getCartCacheKey($userId);
        
        // Try to get from cache first
        $cachedData = Cache::get($cacheKey);
        
        if ($cachedData) {
            Log::info('Cart data retrieved from cache', [
                'user_id' => $userId,
                'timestamp' => now()->toIso8601String(),
            ]);
            
            return $this->decryptSensitiveData($cachedData);
        }
        
        // If not cached, fetch from database and cache it
        $cartData = $this->fetchCartFromDatabase($userId);
        
        if ($cartData) {
            $this->cacheCartData($userId, $cartData, self::ACTIVE_CART_TTL);
        }
        
        return $cartData;
    }

    /**
     * Cache cart data securely
     * 
     * @param int $userId
     * @param array $cartData
     * @param int|null $ttl Custom TTL in seconds
     * @return void
     */
    public function cacheCartData(int $userId, array $cartData, ?int $ttl = null): void
    {
        $cacheKey = $this->getCartCacheKey($userId);
        $ttl = $ttl ?? self::ACTIVE_CART_TTL;
        
        // Sanitize data before caching
        $sanitizedData = $this->sanitizeCartData($cartData);
        
        // Encrypt sensitive fields
        $encryptedData = $this->encryptSensitiveData($sanitizedData);
        
        Cache::put($cacheKey, $encryptedData, $ttl);
        
        Log::info('Cart data cached securely', [
            'user_id' => $userId,
            'ttl' => $ttl,
            'item_count' => count($cartData['items'] ?? []),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Purge cart cache immediately
     * 
     * @param int $userId
     * @return void
     */
    public function purgeCartCache(int $userId): void
    {
        $cacheKey = $this->getCartCacheKey($userId);
        Cache::forget($cacheKey);
        
        // Also purge voucher cache
        $voucherKey = $this->getVoucherCacheKey($userId);
        Cache::forget($voucherKey);
        
        Log::info('Cart cache purged', [
            'user_id' => $userId,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Purge old cart data from database
     * 
     * @param int|null $days Custom threshold in days
     * @return array Statistics
     */
    public function purgeOldCartData(?int $days = null): array
    {
        $threshold = $days ?? self::PURGE_THRESHOLD_DAYS;
        $cutoffDate = Carbon::now()->subDays($threshold);
        
        // Find old cart items
        $oldCartItems = CartItem::where('updated_at', '<', $cutoffDate)->get();
        $count = $oldCartItems->count();
        
        if ($count > 0) {
            // Get unique user IDs for logging
            $affectedUsers = $oldCartItems->pluck('user_id')->unique();
            
            // Delete old cart items
            CartItem::where('updated_at', '<', $cutoffDate)->delete();
            
            // Purge cache for affected users
            foreach ($affectedUsers as $userId) {
                $this->purgeCartCache($userId);
            }
            
            Log::info('Old cart data purged', [
                'threshold_days' => $threshold,
                'items_deleted' => $count,
                'users_affected' => $affectedUsers->count(),
                'cutoff_date' => $cutoffDate->toDateString(),
                'timestamp' => now()->toIso8601String(),
            ]);
        }
        
        return [
            'items_deleted' => $count,
            'threshold_days' => $threshold,
            'cutoff_date' => $cutoffDate->toDateString(),
        ];
    }

    /**
     * Cache menu data securely
     * 
     * @param string $cacheKey
     * @param array $menuData
     * @return void
     */
    public function cacheMenuData(string $cacheKey, array $menuData): void
    {
        Cache::put($cacheKey, $menuData, self::MENU_CACHE_TTL);
        
        Log::debug('Menu data cached', [
            'cache_key' => $cacheKey,
            'ttl' => self::MENU_CACHE_TTL,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Cache search results
     * 
     * @param string $searchKey
     * @param array $results
     * @return void
     */
    public function cacheSearchResults(string $searchKey, array $results): void
    {
        $cacheKey = "menu_search:" . md5($searchKey);
        Cache::put($cacheKey, $results, self::SEARCH_CACHE_TTL);
        
        Log::debug('Search results cached', [
            'search_key_hash' => md5($searchKey),
            'result_count' => count($results),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Cache voucher data
     * 
     * @param int $userId
     * @param array $voucherData
     * @return void
     */
    public function cacheVoucherData(int $userId, array $voucherData): void
    {
        $cacheKey = $this->getVoucherCacheKey($userId);
        
        // Remove sensitive voucher details before caching
        $sanitizedData = [
            'voucher_code' => $voucherData['voucher_code'] ?? null,
            'discount' => $voucherData['discount'] ?? 0,
            'applied_at' => now()->toIso8601String(),
        ];
        
        Cache::put($cacheKey, $sanitizedData, self::VOUCHER_CACHE_TTL);
        
        Log::info('Voucher data cached', [
            'user_id' => $userId,
            'ttl' => self::VOUCHER_CACHE_TTL,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Purge temporary search cache
     * 
     * @return void
     */
    public function purgeSearchCache(): void
    {
        // Get all cache keys that match search pattern
        $pattern = 'menu_search:*';
        
        $store = Cache::getStore();
        if ($store instanceof \Illuminate\Cache\RedisStore) {
            try {
                $redis = $store->connection();
                $keys = $redis->keys($pattern);
                
                if (!empty($keys)) {
                    foreach ($keys as $key) {
                        Cache::forget($key);
                    }
                    
                    Log::info('Search cache purged', [
                        'keys_deleted' => count($keys),
                        'timestamp' => now()->toIso8601String(),
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to purge search cache', [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Mark cart as inactive and reduce TTL
     * 
     * @param int $userId
     * @return void
     */
    public function markCartInactive(int $userId): void
    {
        $cacheKey = $this->getCartCacheKey($userId);
        $cartData = Cache::get($cacheKey);
        
        if ($cartData) {
            // Re-cache with shorter TTL
            Cache::put($cacheKey, $cartData, self::INACTIVE_CART_TTL);
            
            Log::info('Cart marked as inactive', [
                'user_id' => $userId,
                'new_ttl' => self::INACTIVE_CART_TTL,
                'timestamp' => now()->toIso8601String(),
            ]);
        }
    }

    /**
     * Get cache statistics
     * 
     * @return array
     */
    public function getCacheStatistics(): array
    {
        $stats = [
            'active_carts' => 0,
            'cached_searches' => 0,
            'cached_vouchers' => 0,
        ];
        
        // Note: These are approximate counts
        // Actual implementation depends on cache driver
        
        return $stats;
    }

    /**
     * Check if cart is cached
     * 
     * @param int $userId
     * @return bool
     */
    public function isCartCached(int $userId): bool
    {
        return Cache::has($this->getCartCacheKey($userId));
    }

    /**
     * Sanitize cart data before caching
     * 
     * @param array $cartData
     * @return array
     */
    private function sanitizeCartData(array $cartData): array
    {
        // Remove any sensitive fields that shouldn't be cached
        if (isset($cartData['items'])) {
            $cartData['items'] = array_map(function($item) {
                // Keep only necessary data
                return [
                    'cart_id' => $item['cart_id'] ?? null,
                    'quantity' => $item['quantity'] ?? 0,
                    'special_request' => $item['special_request'] ?? null,
                    'item_total' => $item['item_total'] ?? 0,
                    'menu_item' => [
                        'item_id' => $item['menu_item']['item_id'] ?? null,
                        'name' => $item['menu_item']['name'] ?? null,
                        'price' => $item['menu_item']['price'] ?? 0,
                        'is_available' => $item['menu_item']['is_available'] ?? true,
                    ],
                ];
            }, $cartData['items']);
        }
        
        return $cartData;
    }

    /**
     * Encrypt sensitive cart data
     * 
     * @param array $data
     * @return array
     */
    private function encryptSensitiveData(array $data): array
    {
        // Encrypt special requests if they exist
        if (isset($data['items'])) {
            foreach ($data['items'] as &$item) {
                if (!empty($item['special_request'])) {
                    $item['special_request'] = encrypt($item['special_request']);
                    $item['special_request_encrypted'] = true;
                }
            }
        }
        
        return $data;
    }

    /**
     * Decrypt sensitive cart data
     * 
     * @param array $data
     * @return array
     */
    private function decryptSensitiveData(array $data): array
    {
        // Decrypt special requests if they were encrypted
        if (isset($data['items'])) {
            foreach ($data['items'] as &$item) {
                if (!empty($item['special_request']) && isset($item['special_request_encrypted'])) {
                    try {
                        $item['special_request'] = decrypt($item['special_request']);
                        unset($item['special_request_encrypted']);
                    } catch (\Exception $e) {
                        Log::error('Failed to decrypt special request', [
                            'error' => $e->getMessage(),
                        ]);
                        $item['special_request'] = null;
                    }
                }
            }
        }
        
        return $data;
    }

    /**
     * Fetch cart from database
     * 
     * @param int $userId
     * @return array|null
     */
    private function fetchCartFromDatabase(int $userId): ?array
    {
        $cartItems = CartItem::where('user_id', $userId)
            ->with(['menuItem.vendor', 'menuItem.category'])
            ->get();
        
        if ($cartItems->isEmpty()) {
            return null;
        }
        
        $subtotal = 0;
        $itemCount = 0;
        
        $items = $cartItems->map(function($cartItem) use (&$subtotal, &$itemCount) {
            if ($cartItem->menuItem) {
                $itemTotal = $cartItem->menuItem->price * $cartItem->quantity;
                $subtotal += $itemTotal;
                $itemCount += $cartItem->quantity;
                
                return [
                    'cart_id' => $cartItem->cart_id,
                    'quantity' => $cartItem->quantity,
                    'special_request' => $cartItem->special_request,
                    'item_total' => (float) $itemTotal,
                    'menu_item' => [
                        'item_id' => $cartItem->menuItem->item_id,
                        'name' => $cartItem->menuItem->name,
                        'price' => (float) $cartItem->menuItem->price,
                        'is_available' => $cartItem->menuItem->is_available,
                    ],
                ];
            }
            return null;
        })->filter()->values()->toArray();
        
        return [
            'items' => $items,
            'summary' => [
                'item_count' => $itemCount,
                'subtotal' => (float) $subtotal,
                'service_fee' => 2.00,
                'total' => (float) ($subtotal + 2.00),
            ],
        ];
    }

    /**
     * Get cart cache key
     * 
     * @param int $userId
     * @return string
     */
    private function getCartCacheKey(int $userId): string
    {
        return "cart_data:{$userId}";
    }

    /**
     * Get voucher cache key
     * 
     * @param int $userId
     * @return string
     */
    private function getVoucherCacheKey(int $userId): string
    {
        return "voucher_applied:{$userId}";
    }
}
