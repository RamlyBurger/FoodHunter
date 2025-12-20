# Pickup & Queue Management Security Implementation

## Security Requirements Implemented

### 1. Access Control [Requirement #94]
**Limit the number of transactions a single user or device can perform in a given period of time**

#### Implementation Details:

**Service Created:** `app/Services/PickupRateLimiterService.php`

This service prevents queue gaming, spam requests, and abuse of the pickup system by implementing intelligent rate limiting:

#### Rate Limits Configured:

| Action | Limit | Time Window | Purpose |
|--------|-------|-------------|---------|
| Pickup Status Checks | 10 | 1 minute | Prevent status polling spam |
| Pickup Updates | 5 | 1 hour | Prevent instruction spam |
| Queue Position Checks | 15 | 1 minute | Prevent queue gaming |
| Pickup Cancellations | 3 | 24 hours | Prevent abuse/manipulation |
| Vendor Queue Updates | 30 | 1 minute | Higher limit for vendors |

#### Key Methods:
```php
canCheckPickupStatus($userId)      // Check if user can view status
canUpdatePickup($userId)           // Check if user can modify pickup
canCheckQueuePosition($userId)     // Check if user can view queue
canCancelPickup($userId)           // Check if user can cancel
canVendorUpdateQueue($vendorId)    // Check if vendor can update
```

#### Security Features:
✅ Prevents automated bot attacks on queue system
✅ Stops users from gaming queue positions
✅ Limits excessive cancellations (max 3/day)
✅ Logs all rate limit violations with IP/timestamp
✅ Returns remaining attempts to client
✅ Different limits for different user types (vendor vs student)

---

### 2. Data Protection [Requirement #132]
**Protect all cached or temporary copies of sensitive data and purge when no longer required**

#### Implementation Details:

**Service Created:** `app/Services/PickupDataProtectionService.php`

This service manages secure caching, access control, and automatic purging of pickup-related sensitive data:

#### Cache Configuration:

| Data Type | TTL | Purge Timing |
|-----------|-----|--------------|
| Active Pickups | 15 minutes | On status change |
| Completed Pickups | 5 minutes | After completion |
| Old Pickup Data | 30 days | Daily scheduled job |
| Temporary Queue Data | Dynamic | Hourly cleanup |

#### Key Methods:
```php
getSecurePickupData($pickupId, $userId)        // Get with access verification
cachePickupData($pickupId, $userId, $data)     // Secure caching
purgePickupCache($pickupId, $userId)           // Immediate purge
purgeOldPickupData()                           // Scheduled purge
encryptInstructions($text)                     // Encrypt sensitive data
```

#### Security Features:
✅ User-specific cache keys prevent unauthorized access
✅ Automatic purging after pickup completion
✅ Encrypted storage of sensitive pickup instructions
✅ Access verification before returning data
✅ Removes payment details from cached data
✅ Scheduled purging of 30+ day old data
✅ Comprehensive logging of all data access

---

## Files Created

### 1. Services

**app/Services/PickupRateLimiterService.php** (317 lines)
- Rate limiting for all pickup operations
- Configurable limits per action type
- Violation logging with full context
- Admin reset functionality
- Real-time limit status checking

**app/Services/PickupDataProtectionService.php** (312 lines)
- Secure pickup data caching
- Access control verification
- Automatic data purging
- Instruction encryption
- Cache statistics tracking

### 2. Controllers

**app/Http/Controllers/PickupController.php** (NEW - 245 lines)
- Student-facing pickup management
- Integrated rate limiting
- Secure data handling
- Output encoding
- Cache control headers

**app/Http/Controllers/VendorOrderController.php** (UPDATED)
- Lines 18-19: Added service imports
- Lines 23-43: Dependency injection for security services
- Lines 133-143: Rate limiting for queue updates
- Lines 167-175: Cache purging on order completion

### 3. Console Commands

**app/Console/Commands/PurgeOldPickupData.php** (NEW - 105 lines)
- Automated data purging command
- Dry-run mode for testing
- Detailed purge statistics
- Configurable retention period

---

## Usage Examples

### Rate Limiting in Controllers:

```php
public function getStatus($pickupId)
{
    $user = Auth::user();
    
    // Check rate limit
    $rateCheck = $this->rateLimiter->canCheckPickupStatus($user->user_id);
    
    if (!$rateCheck['allowed']) {
        return response()->json([
            'message' => $rateCheck['message'],
            'retry_after' => $rateCheck['reset_in']
        ], 429);
    }
    
    // Proceed with action...
    $this->rateLimiter->recordAction($user->user_id, 'status_check');
}
```

### Secure Data Caching:

```php
public function getPickup($pickupId)
{
    $user = Auth::user();
    
    // Get secure data with access verification
    $pickupData = $this->dataProtection->getSecurePickupData(
        $pickupId, 
        $user->user_id
    );
    
    if (!$pickupData) {
        abort(404, 'Pickup not found or unauthorized');
    }
    
    return view('pickup.show', compact('pickupData'));
}
```

### Purging After Completion:

```php
public function completeOrder($orderId)
{
    $order = Order::findOrFail($orderId);
    $order->update(['status' => 'completed']);
    
    // Purge pickup cache immediately
    if ($order->pickup) {
        $this->dataProtection->purgePickupCache(
            $order->pickup->pickup_id,
            $order->user_id
        );
    }
}
```

---

## Testing

### Test Rate Limiting:

**1. Test Status Check Limit (10/minute):**
```bash
# Make 11 requests in 1 minute
for i in {1..11}; do
    curl -H "Authorization: Bearer TOKEN" \
         http://localhost:8000/api/pickup/1/status
done

# 11th request should return 429 (Too Many Requests)
```

**2. Test Cancellation Limit (3/day):**
```php
// In Tinker
$user = User::find(1);

// Try to cancel 4 times
for($i = 0; $i < 4; $i++) {
    $result = app(PickupRateLimiterService::class)
        ->canCancelPickup($user->user_id);
    
    if($result['allowed']) {
        app(PickupRateLimiterService::class)
            ->recordAction($user->user_id, 'cancel');
    } else {
        echo "Limit reached: " . $result['message'];
    }
}
```

### Test Data Protection:

**1. Test Cache Purging:**
```php
// In Tinker
$service = app(PickupDataProtectionService::class);

// Cache some data
$service->cachePickupData(1, 8, ['test' => 'data']);

// Verify it's cached
$service->isPickupCached(1, 8); // true

// Purge it
$service->purgePickupCache(1, 8);

// Verify it's gone
$service->isPickupCached(1, 8); // false
```

**2. Test Automatic Purge (Schedule):**
```bash
# Run purge command manually
php artisan pickup:purge-old-data

# Dry run to see what would be purged
php artisan pickup:purge-old-data --dry-run

# Custom threshold
php artisan pickup:purge-old-data --days=15
```

**3. Test Access Control:**
```php
// Try to access another user's pickup
$service = app(PickupDataProtectionService::class);

$data = $service->getSecurePickupData(
    $pickupId = 1,
    $wrongUserId = 999
);

// Should return null and log warning
```

---

## Scheduled Tasks

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // [132] Purge old pickup data daily at 2 AM
    $schedule->command('pickup:purge-old-data')
             ->daily()
             ->at('02:00')
             ->appendOutputTo(storage_path('logs/pickup-purge.log'));
}
```

---

## API Endpoints

### Student Endpoints:

```
GET    /api/pickup/{id}/status          - Get pickup status (Rate Limited: 10/min)
GET    /api/pickup/{id}/queue           - Get queue position (Rate Limited: 15/min)
PUT    /api/pickup/{id}                 - Update pickup info (Rate Limited: 5/hour)
DELETE /api/pickup/{id}                 - Cancel pickup (Rate Limited: 3/day)
GET    /api/pickup/rate-limit-status    - Check rate limit status
```

### Vendor Endpoints:

```
PUT    /vendor/orders/{id}/status       - Update order status (Rate Limited: 30/min)
```

---

## Security Checklist

✅ **Rate Limiting [94]**
- [x] Status checks limited to 10/minute
- [x] Updates limited to 5/hour
- [x] Queue checks limited to 15/minute
- [x] Cancellations limited to 3/day
- [x] Vendor updates limited to 30/minute
- [x] All violations logged with IP
- [x] Remaining attempts returned to client

✅ **Data Protection [132]**
- [x] Active pickups cached for 15 minutes
- [x] Completed pickups cached for 5 minutes
- [x] User-specific cache keys implemented
- [x] Access verification before data return
- [x] Automatic purge on completion
- [x] Scheduled purge of 30+ day data
- [x] Sensitive instructions encrypted
- [x] Payment details removed from cache

✅ **Additional Security**
- [x] Cache-Control headers on pickup pages
- [x] Output encoding for all user data
- [x] Comprehensive logging
- [x] Admin reset functionality

---

## Monitoring

### Log Events to Monitor:

**Rate Limit Violations:**
```log
[2025-12-20 10:30:45] local.WARNING: Rate limit exceeded 
{
    "action": "pickup_status_check",
    "key": "pickup_status_check:8",
    "attempts": 10,
    "max_allowed": 10,
    "ip_address": "127.0.0.1",
    "timestamp": "2025-12-20T10:30:45+00:00"
}
```

**Unauthorized Access Attempts:**
```log
[2025-12-20 10:31:20] local.WARNING: Unauthorized pickup data access attempt
{
    "pickup_id": 1,
    "user_id": 999,
    "ip_address": "127.0.0.1",
    "timestamp": "2025-12-20T10:31:20+00:00"
}
```

**Cache Purge Events:**
```log
[2025-12-20 10:32:15] local.INFO: Pickup cache purged
{
    "pickup_id": 1,
    "user_id": 8,
    "timestamp": "2025-12-20T10:32:15+00:00"
}
```

---

## Compliance

### OWASP Top 10 Coverage:
- **A01:2021 - Broken Access Control**: Access verification before data return
- **A04:2021 - Insecure Design**: Rate limiting prevents abuse
- **A05:2021 - Security Misconfiguration**: Proper cache management

### Security Best Practices:
- **Defense in Depth**: Multiple layers (rate limiting + access control + encryption)
- **Least Privilege**: User-specific cache keys
- **Data Minimization**: Automatic purging of old data
- **Audit Trail**: Comprehensive logging of all actions

---

## Maintenance

### Daily Tasks:
1. Review rate limit violation logs
2. Monitor cache hit ratios
3. Check purge job execution

### Weekly Tasks:
1. Review access denial logs
2. Analyze rate limit patterns
3. Adjust limits if needed

### Monthly Tasks:
1. Review data retention policies
2. Audit encryption keys
3. Test disaster recovery

---

## Implementation Summary

**Requirements Implemented:**
- ✅ [94] Rate Limiting - Prevent queue gaming and abuse
- ✅ [132] Data Protection - Secure caching and automatic purging

**Modules Protected:**
- Pickup Management Module
- Queue Management Module
- Vendor Order Management

**Security Level:** Production Ready ✅

**Performance Impact:** Minimal (caching improves performance)

**Maintenance:** Automated (scheduled purge command)
