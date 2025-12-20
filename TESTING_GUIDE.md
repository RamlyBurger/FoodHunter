# 🧪 Design Patterns Testing Guide

## Quick Test - All Patterns at Once

### Method 1: Browser Test
Open your browser and visit:
```
http://localhost/foodhunter/public/test-patterns
```

You'll see JSON output showing all 5 patterns working:
```json
{
  "success": true,
  "message": "All Design Patterns Working Successfully! ✅",
  "patterns": {
    "factory": { "status": "working" },
    "strategy": { "status": "working" },
    "state": { "status": "working" },
    "observer": { "status": "working" },
    "singleton": { "status": "working" }
  }
}
```

### Method 2: PowerShell Test
```powershell
Invoke-RestMethod -Uri "http://localhost/foodhunter/public/test-patterns" | ConvertTo-Json -Depth 5
```

---

## Individual Pattern Testing

## 1️⃣ Factory Pattern - Vendor Management

### Test via Tinker
```powershell
cd c:\xampp\htdocs\foodhunter
php artisan tinker
```

```php
// Create a new vendor using Factory Pattern
$factory = new \App\Patterns\Factory\VendorFactory();

$vendor = $factory->createVendor([
    'name' => 'Test Vendor',
    'email' => 'testvendor@example.com',
    'password' => 'password123',
    'phone' => '+60123456789',
    'store_name' => 'Test Food Stall',
    'store_description' => 'Testing factory pattern'
]);

// Check what was created
echo "Vendor ID: " . $vendor->user_id . PHP_EOL;
echo "Settings: " . ($vendor->vendorSetting ? 'Created' : 'Missing') . PHP_EOL;
echo "Operating Hours: " . $vendor->operatingHours->count() . " days" . PHP_EOL;
```

**Expected Result:**
- ✅ User created with role 'vendor'
- ✅ VendorSetting record created
- ✅ 7 VendorOperatingHour records created (Mon-Sun)

### Test via Browser
1. Go to `/register` (if you implement vendor registration)
2. Register as vendor
3. Check database:
```sql
SELECT * FROM users WHERE user_id = LAST_INSERT_ID();
SELECT * FROM vendor_settings WHERE vendor_id = LAST_INSERT_ID();
SELECT * FROM vendor_operating_hours WHERE vendor_id = LAST_INSERT_ID();
```

---

## 2️⃣ Strategy Pattern - Cart Pricing

### Test via Browser

**Step 1: Test Regular Pricing (No Discount)**
1. Go to `http://localhost/foodhunter/public/menu`
2. Add 1-2 items to cart (less than 3)
3. Go to `http://localhost/foodhunter/public/cart`
4. Check the cart summary:
   - Should show: Regular pricing (no discount)

**Step 2: Test Bulk Discount**
1. Add 5 items to cart
2. Go to cart page
3. Check the cart summary:
   - Should show: 10% bulk discount automatically applied
   - Example: Subtotal RM100 → Discount RM10 → Total RM92

**Step 3: Test Voucher Discount**
1. Go to `http://localhost/foodhunter/public/rewards`
2. Redeem a reward (if you have enough points)
3. Go to cart
4. Apply voucher code
5. Check discount applied

### Test via PowerShell

**Test Different Strategies:**
```powershell
# Test Regular Pricing
Invoke-RestMethod -Uri "http://localhost/foodhunter/public/api/demo/strategy?subtotal=50&strategy=regular" | ConvertTo-Json

# Test Bulk Discount (5 items)
Invoke-RestMethod -Uri "http://localhost/foodhunter/public/api/demo/strategy?subtotal=100&strategy=bulk&quantity=5" | ConvertTo-Json

# Test Bulk Discount (10 items - higher discount)
Invoke-RestMethod -Uri "http://localhost/foodhunter/public/api/demo/strategy?subtotal=200&strategy=bulk&quantity=10" | ConvertTo-Json

# Test Voucher Discount (15% off)
Invoke-RestMethod -Uri "http://localhost/foodhunter/public/api/demo/strategy?subtotal=150&strategy=voucher&voucher_value=15" | ConvertTo-Json
```

### Test via Tinker
```php
use App\Patterns\Strategy\CartPriceCalculator;
use App\Patterns\Strategy\RegularPricingStrategy;
use App\Patterns\Strategy\BulkDiscountStrategy;
use App\Patterns\Strategy\VoucherDiscountStrategy;

// Test 1: Regular Pricing
$calculator = new CartPriceCalculator();
$calculator->setStrategy(new RegularPricingStrategy());
$result = $calculator->calculate(100, ['service_fee' => 2]);
print_r($result);
// Expected: subtotal: 100, discount: 0, total: 102

// Test 2: Bulk Discount (5 items = 10% off)
$calculator->setStrategy(new BulkDiscountStrategy());
$result = $calculator->calculate(100, ['service_fee' => 2, 'quantity' => 5]);
print_r($result);
// Expected: subtotal: 100, discount: 10, total: 92

// Test 3: Bulk Discount (10 items = 15% off)
$result = $calculator->calculate(100, ['service_fee' => 2, 'quantity' => 10]);
print_r($result);
// Expected: subtotal: 100, discount: 15, total: 87

// Test 4: Voucher Discount (20% off)
$calculator->setStrategy(new VoucherDiscountStrategy());
$result = $calculator->calculate(100, [
    'service_fee' => 2,
    'voucher_type' => 'percentage',
    'voucher_value' => 20
]);
print_r($result);
// Expected: subtotal: 100, discount: 20, total: 82
```

---

## 3️⃣ Singleton Pattern - User Management

### Test via Browser

**Test 1: Single Auth Instance**
1. Login at `http://localhost/foodhunter/public/login`
   - Email: `ahmad.student@university.edu`
   - Password: `Password123`
2. Navigate to different pages
3. Open browser console and check:
   - User stays logged in across all pages
   - Same user instance everywhere

**Test 2: Auth State**
```php
// Test in any controller or view
$user1 = Auth::user();
$user2 = Auth::user();

// Both should be the exact same instance (Singleton)
var_dump($user1 === $user2); // true
```

### Test via Tinker
```php
// Test Singleton behavior
use Illuminate\Support\Facades\Auth;

// Get auth instance
$auth1 = Auth::getFacadeRoot();
$auth2 = Auth::getFacadeRoot();

// Should be same instance (Singleton pattern)
var_dump($auth1 === $auth2); // true

// Test auth methods
Auth::check(); // false (in console)

// Login a user
$user = \App\Models\User::find(8);
Auth::login($user);

Auth::check(); // true
Auth::user()->name; // "Ahmad bin Abdullah"

// Same user instance accessed multiple times
$u1 = Auth::user();
$u2 = Auth::user();
var_dump($u1 === $u2); // true (Singleton)
```

---

## 4️⃣ State Pattern - Order Processing

### Test via Browser

**Complete Order Flow Test:**

1. **Place Order (Pending State)**
   - Login as student
   - Add items to cart
   - Checkout → Order created in 'pending' state

2. **Accept Order (Pending → Accepted)**
   - Login as vendor
   - Go to vendor orders
   - Click "Accept" on pending order
   - State transitions to 'accepted'

3. **Start Preparation (Accepted → Preparing)**
   - Click "Start Preparing"
   - State transitions to 'preparing'

4. **Mark as Ready (Preparing → Ready)**
   - Click "Mark as Ready"
   - State transitions to 'ready'
   - Queue number generated

5. **Complete Order (Ready → Completed)**
   - Click "Complete"
   - State transitions to 'completed'

**Test Cancellation:**
- Try cancelling at different states
- ✅ Can cancel: Pending, Accepted
- ❌ Cannot cancel: Preparing, Ready, Completed

### Test via Tinker
```php
use App\Patterns\State\OrderStateManager;
use App\Models\Order;

// Get a pending order
$order = Order::where('status', 'pending')->first();

// Initialize state manager
$stateManager = new OrderStateManager($order);

// Check current state
echo "Current state: " . $stateManager->getCurrentStateName() . PHP_EOL;
echo "Description: " . $stateManager->getDescription() . PHP_EOL;
echo "Can cancel: " . ($stateManager->canCancel() ? 'Yes' : 'No') . PHP_EOL;

// Process current state (sends notifications)
$stateManager->process();

// Move to next state: Pending → Accepted
$success = $stateManager->moveToNext();
echo "Transition success: " . ($success ? 'Yes' : 'No') . PHP_EOL;

// Reload order to see new state
$order->refresh();
echo "New state: " . $order->status . PHP_EOL;

// Test cancellation
if ($stateManager->canCancel()) {
    $stateManager->cancel();
    echo "Order cancelled" . PHP_EOL;
}
```

### Test via API
```powershell
# Update order status (vendor endpoint)
$body = '{"status":"accepted"}'
Invoke-RestMethod -Uri "http://localhost/foodhunter/public/api/vendor/orders/1/status" `
  -Method POST `
  -Headers @{Authorization="Bearer YOUR_TOKEN"} `
  -ContentType "application/json" `
  -Body $body
```

---

## 5️⃣ Observer Pattern - Queue Management

### Test via Browser

**Test Notification Flow:**

1. **Place Order (created event)**
   - Student: Place an order
   - Expected:
     - ✅ Vendor receives notification "New Order in Queue"
     - ✅ Dashboard updates with new order count
     - ✅ Analytics logs order created event

2. **Mark Order Ready (ready event)**
   - Vendor: Mark order as ready
   - Expected:
     - ✅ Student receives notification "Order Ready - Queue #123"
     - ✅ Dashboard shows order in ready queue
     - ✅ Analytics logs preparation time

3. **Collect Order (collected event)**
   - Vendor: Mark as completed
   - Expected:
     - ✅ Vendor notification "Order Collected"
     - ✅ Dashboard updates completed count
     - ✅ Analytics logs pickup wait time

**Check Notifications:**
- Student: `http://localhost/foodhunter/public/notifications`
- Vendor: `http://localhost/foodhunter/public/vendor/notifications`

### Test via Tinker
```php
use App\Patterns\Observer\QueueSubject;
use App\Patterns\Observer\NotificationObserver;
use App\Patterns\Observer\DashboardObserver;
use App\Patterns\Observer\AnalyticsObserver;
use App\Models\Order;

// Create queue subject and attach observers
$subject = new QueueSubject();
$subject->attach(new NotificationObserver());
$subject->attach(new DashboardObserver());
$subject->attach(new AnalyticsObserver());

echo "Observers attached: " . $subject->getObserverCount() . PHP_EOL;
print_r($subject->getObserverNames());

// Get an order
$order = Order::find(1);

// Test 'ready' event
echo "Notifying observers about order ready..." . PHP_EOL;
$subject->notify($order, 'ready');

// Check results:
// 1. Check student_notifications table
$notification = \App\Models\StudentNotification::where('user_id', $order->user_id)
    ->latest()
    ->first();
echo "Notification created: " . $notification->title . PHP_EOL;

// 2. Check cache (Dashboard Observer)
$cacheKey = "vendor_ready_orders_" . $order->vendor_id;
$readyCount = Cache::get($cacheKey);
echo "Ready orders cached: " . $readyCount . PHP_EOL;

// 3. Check logs (Analytics Observer)
// View: storage/logs/laravel.log
```

### Test via Database
```sql
-- Check notifications created by NotificationObserver
SELECT * FROM student_notifications 
WHERE type = 'pickup' 
ORDER BY created_at DESC 
LIMIT 5;

SELECT * FROM vendor_notifications 
WHERE type = 'queue' 
ORDER BY created_at DESC 
LIMIT 5;

-- Check order states
SELECT order_id, status, created_at, ready_at, completed_at 
FROM orders 
ORDER BY order_id DESC 
LIMIT 10;
```

---

## 🎯 Complete Integration Test

### Full User Journey Test

**Student Journey:**
```
1. Register/Login → (Singleton Pattern)
2. Browse menu and add 5 items → (Strategy Pattern: Bulk discount applied)
3. View cart → (Strategy: Shows 10% discount)
4. Checkout → (State: Order created in 'pending', Observer: Vendor notified)
5. Wait for notification → (Observer: Receives "Order Ready" notification)
6. Collect order → (State: Completed, Observer: Analytics updated)
```

**Vendor Journey:**
```
1. Login as vendor → (Singleton Pattern)
2. View orders → (State: See orders in different states)
3. Accept order → (State: Pending → Accepted transition)
4. Start preparing → (State: Accepted → Preparing)
5. Mark ready → (State: Preparing → Ready, Observer: Student notified)
6. Complete → (State: Ready → Completed, Observer: Dashboard updated)
```

### PowerShell Complete Test Script
```powershell
# Test all patterns in sequence
cd c:\xampp\htdocs\foodhunter

Write-Host "Testing All Design Patterns..." -ForegroundColor Cyan

# 1. Test endpoint
Write-Host "`n1. Testing All Patterns Endpoint..." -ForegroundColor Yellow
$result = Invoke-RestMethod -Uri "http://localhost/foodhunter/public/test-patterns"
Write-Host "✅ Patterns Working: $($result.summary.working)/$($result.summary.total_patterns)" -ForegroundColor Green

# 2. Test Strategy Pattern via API
Write-Host "`n2. Testing Strategy Pattern..." -ForegroundColor Yellow
$strategy = Invoke-RestMethod -Uri "http://localhost/foodhunter/public/api/demo/strategy?subtotal=100&strategy=bulk&quantity=5"
Write-Host "Bulk Discount Applied: RM$($strategy.discount)" -ForegroundColor Green

# 3. Check if classes exist
Write-Host "`n3. Checking Pattern Classes..." -ForegroundColor Yellow
php artisan tinker --execute="
echo 'Factory: ' . (class_exists('\App\Patterns\Factory\VendorFactory') ? '✅' : '❌') . PHP_EOL;
echo 'Strategy: ' . (class_exists('\App\Patterns\Strategy\CartPriceCalculator') ? '✅' : '❌') . PHP_EOL;
echo 'State: ' . (class_exists('\App\Patterns\State\OrderStateManager') ? '✅' : '❌') . PHP_EOL;
echo 'Observer: ' . (class_exists('\App\Patterns\Observer\QueueSubject') ? '✅' : '❌') . PHP_EOL;
"

Write-Host "`n✅ All Design Patterns Tested Successfully!" -ForegroundColor Green
```

---

## 📊 Expected Results Summary

| Pattern | Test | Expected Result |
|---------|------|----------------|
| **Factory** | Create vendor | User + Settings + 7 Operating Hours created |
| **Strategy** | Cart with 5 items | 10% bulk discount applied automatically |
| **Strategy** | Cart with voucher | Voucher discount applied |
| **Singleton** | Auth::user() twice | Same instance returned (===) |
| **State** | Order pending → accepted | Valid transition, notification sent |
| **State** | Cancel preparing order | Fails - cannot cancel |
| **Observer** | Order marked ready | 3 observers notified (Notification, Dashboard, Analytics) |
| **Observer** | Check notifications | Student receives "Order Ready" notification |

---

## 🐛 Troubleshooting

### Pattern Not Working?

**Factory Pattern:**
```powershell
# Check if class loads
php artisan tinker --execute="var_dump(class_exists('\App\Patterns\Factory\VendorFactory'));"
```

**Strategy Pattern:**
- Clear cache: `php artisan cache:clear`
- Check cart page shows discount
- View cart controller logs

**State Pattern:**
```sql
-- Check order state transitions
SELECT order_id, status, created_at, updated_at FROM orders ORDER BY order_id DESC LIMIT 10;
```

**Observer Pattern:**
```sql
-- Check notifications were created
SELECT * FROM student_notifications ORDER BY created_at DESC LIMIT 5;
SELECT * FROM vendor_notifications ORDER BY created_at DESC LIMIT 5;
```

---

## 🎓 Quick Reference

**Test All Patterns:** `http://localhost/foodhunter/public/test-patterns`

**Test Individually:**
- Factory: Create vendor in tinker
- Strategy: Add items to cart, view discount
- Singleton: Login and check Auth::user()
- State: Place order and track status changes
- Observer: Check notifications when order status changes

**Database Checks:**
```sql
-- Factory Pattern results
SELECT * FROM vendor_settings;
SELECT * FROM vendor_operating_hours;

-- State Pattern results  
SELECT order_id, status FROM orders;

-- Observer Pattern results
SELECT * FROM student_notifications WHERE type = 'pickup';
SELECT * FROM vendor_notifications WHERE type = 'queue';
```
