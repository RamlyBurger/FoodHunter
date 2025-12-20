# Design Patterns Implementation - FoodHunter System

This document explains how the five major design patterns have been implemented across the FoodHunter university canteen management system.

## 📋 Pattern-Module Mapping

| Module | Design Pattern | Location |
|--------|---------------|----------|
| Vendor Management | **Factory Pattern** | `app/Patterns/Factory/` |
| Menu & Cart Management | **Strategy Pattern** | `app/Patterns/Strategy/` |
| User Management | **Singleton Pattern** | Laravel Auth Facade (Built-in) |
| Payment & Order Processing | **State Pattern** | `app/Patterns/State/` |
| Pickup & Queue Management | **Observer Pattern** | `app/Patterns/Observer/` |

---

## 1. Factory Pattern - Vendor Management Module

### Purpose
Creates vendor-specific components and initializes vendor entities with all required dependencies.

### Implementation
**Location:** `app/Patterns/Factory/VendorFactory.php`

### Key Classes
- `VendorFactory` - Main factory class

### Methods
- `createVendor(array $data)` - Creates complete vendor with settings and operating hours
- `createVendorSettings(int $vendorId, array $data)` - Creates vendor settings component
- `createDefaultOperatingHours(int $vendorId)` - Creates default weekly schedule
- `createMenuItem(int $vendorId, array $data)` - Creates menu item for vendor
- `updateVendor(User $vendor, array $data)` - Updates vendor components

### Usage Example
```php
use App\Patterns\Factory\VendorFactory;

$factory = new VendorFactory();

// Create new vendor with all components
$vendor = $factory->createVendor([
    'name' => 'Nasi Lemak Stall',
    'email' => 'nasilemak@vendor.com',
    'password' => 'password123',
    'phone' => '+60123456789',
    'store_name' => 'Pak Mat Nasi Lemak',
    'store_description' => 'Authentic Malaysian nasi lemak',
]);

// Automatically creates:
// - User account (vendor role)
// - VendorSetting record
// - 7 VendorOperatingHour records (Mon-Sun)
```

### Benefits
- **Consistency**: All vendors created with standard structure
- **Maintainability**: Centralized vendor creation logic
- **Flexibility**: Easy to modify vendor initialization process

---

## 2. Strategy Pattern - Menu & Cart Management Module

### Purpose
Provides flexible pricing calculation strategies for cart totals, supporting different discount types.

### Implementation
**Location:** `app/Patterns/Strategy/`

### Key Classes
- `PricingStrategy` - Interface defining strategy contract
- `RegularPricingStrategy` - No discounts
- `VoucherDiscountStrategy` - Percentage or fixed vouchers
- `BulkDiscountStrategy` - Quantity-based discounts
- `CartPriceCalculator` - Context class using strategies

### Strategy Algorithms

#### Regular Pricing
```php
Total = Subtotal + Service Fee
```

#### Voucher Discount
```php
// Percentage: 10% off
Discount = Subtotal × (VoucherValue / 100)

// Fixed: RM5 off
Discount = VoucherValue

Total = Subtotal + ServiceFee - Discount
```

#### Bulk Discount
```php
Quantity >= 10: 15% off
Quantity >= 5:  10% off
Quantity >= 3:   5% off

Discount = Subtotal × (DiscountPercentage / 100)
Total = Subtotal + ServiceFee - Discount
```

### Usage in CartController
```php
use App\Patterns\Strategy\CartPriceCalculator;
use App\Patterns\Strategy\VoucherDiscountStrategy;

$calculator = new CartPriceCalculator();

// Apply voucher strategy
$calculator->setStrategy(new VoucherDiscountStrategy());
$result = $calculator->calculate($subtotal, [
    'service_fee' => 2.00,
    'voucher_type' => 'percentage',
    'voucher_value' => 10,
]);

// Returns:
// [
//     'subtotal' => 50.00,
//     'service_fee' => 2.00,
//     'discount' => 5.00,
//     'total' => 47.00,
//     'details' => '10% voucher discount applied'
// ]
```

### Integrated Controllers
- `CartController@index` - Displays cart with strategy-based pricing
- `PaymentController@showCheckout` - Checkout page with pricing

### Benefits
- **Runtime Flexibility**: Switch pricing strategies dynamically
- **Extensibility**: Easy to add new discount types
- **Testability**: Each strategy can be tested independently
- **Separation of Concerns**: Pricing logic isolated from business logic

---

## 3. Singleton Pattern - User Management Module

### Purpose
Ensures single authentication instance per session, managing user state globally.

### Implementation
**Built-in:** Laravel's Auth Facade uses Singleton pattern

### Key Components
- `Auth` facade - Single authentication manager instance
- Session management - One active session per user
- User state - Globally accessible via `Auth::user()`

### Usage Examples
```php
// Get current user (same instance throughout request)
$user = Auth::user();

// Check authentication
if (Auth::check()) {
    // User is authenticated
}

// Login
Auth::login($user);

// Logout
Auth::logout();
```

### Benefits
- **Global Access**: User state available everywhere
- **Performance**: No duplicate auth checks
- **Consistency**: Single source of truth for user state
- **Session Management**: Prevents multiple concurrent auth states

### Already Implemented In
- All controllers using `Auth::user()`
- Middleware: `auth`, `guest`
- Views: `@auth`, `@guest` directives

---

## 4. State Pattern - Payment & Order Processing Module

### Purpose
Manages order lifecycle through well-defined states with valid transitions and state-specific behaviors.

### Implementation
**Location:** `app/Patterns/State/`

### Key Classes
- `OrderState` - Interface defining state contract
- `PendingState` - Initial state, waiting for acceptance
- `AcceptedState` - Vendor accepted, ready to prepare
- `PreparingState` - Order being prepared
- `ReadyState` - Ready for pickup
- `CompletedState` - Order completed successfully
- `CancelledState` - Order cancelled
- `OrderStateManager` - Context managing state transitions

### State Transition Diagram
```
┌─────────┐
│ Pending │──accept──▶┌──────────┐
└─────────┘            │ Accepted │
     │                 └──────────┘
  cancel                    │
     │                   prepare
     ▼                      ▼
┌───────────┐         ┌───────────┐
│ Cancelled │         │ Preparing │
└───────────┘         └───────────┘
                            │
                          ready
                            ▼
                       ┌────────┐
                       │ Ready  │──collect──▶┌───────────┐
                       └────────┘             │ Completed │
                                              └───────────┘
```

### State Behaviors

| State | Can Cancel? | Next State | Notifications Sent |
|-------|------------|------------|-------------------|
| Pending | ✅ Yes | Accepted | Vendor: New order |
| Accepted | ✅ Yes | Preparing | Student: Accepted |
| Preparing | ❌ No | Ready | Student: Ready |
| Ready | ❌ No | Completed | Student: Collect |
| Completed | ❌ No | - | Student: Thank you |
| Cancelled | ❌ No | - | Both: Cancelled |

### Usage in VendorOrderController
```php
use App\Patterns\State\OrderStateManager;

// Initialize state manager
$stateManager = new OrderStateManager($order);

// Process current state
$stateManager->process();

// Move to next state
if ($stateManager->moveToNext()) {
    // Transition successful
}

// Cancel order (if allowed in current state)
if ($stateManager->canCancel()) {
    $stateManager->cancel();
}

// Get state information
$stateName = $stateManager->getCurrentStateName();
$description = $stateManager->getDescription();
```

### Integrated Controllers
- `VendorOrderController@updateStatus` - Uses state manager for transitions
- `PaymentController@processCheckout` - Initializes order in pending state

### Benefits
- **Valid Transitions**: Only allowed state changes permitted
- **State-Specific Logic**: Each state handles its own behavior
- **Maintainability**: Easy to add/modify states
- **Clarity**: Order lifecycle clearly defined

---

## 5. Observer Pattern - Pickup & Queue Management Module

### Purpose
Notifies multiple observers when queue status changes, enabling loose coupling between queue system and notification/analytics components.

### Implementation
**Location:** `app/Patterns/Observer/`

### Key Classes
- `QueueObserver` - Interface for observers
- `NotificationObserver` - Sends push notifications
- `DashboardObserver` - Updates vendor dashboard stats
- `AnalyticsObserver` - Tracks queue metrics
- `QueueSubject` - Manages and notifies observers

### Observer Responsibilities

#### 1. NotificationObserver
- Sends notifications to students and vendors
- Events handled: created, ready, collected, cancelled

```php
created    → Vendor: "New order in queue #123"
ready      → Student: "Order ready for pickup! Queue #123"
collected  → Vendor: "Order collected by customer"
cancelled  → Both: "Order cancelled"
```

#### 2. DashboardObserver
- Updates vendor dashboard cache
- Refreshes real-time statistics
- Tracks ready orders count, completed today

```php
ready     → Cache vendor ready orders count
collected → Cache completed orders for today
```

#### 3. AnalyticsObserver
- Tracks preparation times
- Measures pickup wait times
- Logs queue metrics for reporting

```php
Metrics tracked:
- Preparation time (order created → ready)
- Pickup wait time (ready → collected)
- Queue events log
```

### Usage in Controllers
```php
use App\Patterns\Observer\QueueSubject;
use App\Patterns\Observer\NotificationObserver;
use App\Patterns\Observer\DashboardObserver;
use App\Patterns\Observer\AnalyticsObserver;

// Initialize subject with observers
$queueSubject = new QueueSubject();
$queueSubject->attach(new NotificationObserver());
$queueSubject->attach(new DashboardObserver());
$queueSubject->attach(new AnalyticsObserver());

// Notify all observers when order ready
$queueSubject->notify($order, 'ready');

// All attached observers will be notified:
// - NotificationObserver sends push notification
// - DashboardObserver updates vendor stats
// - AnalyticsObserver logs metrics
```

### Integrated Controllers
- `PaymentController` - Notifies on order creation
- `VendorOrderController` - Notifies on status changes

### Event Flow Example
```
Order Status: preparing → ready
            ↓
    QueueSubject.notify(order, 'ready')
            ↓
    ┌───────┴───────┬──────────────┬─────────────┐
    ↓               ↓              ↓             ↓
Notification   Dashboard      Analytics   (Future
 Observer       Observer       Observer   Observers)
    ↓               ↓              ↓
Send push      Update cache    Log metrics
notification   Refresh stats   Track time
to student     Ready: +1       Prep: 15min
```

### Benefits
- **Loose Coupling**: Queue system doesn't know about observers
- **Extensibility**: Easy to add new observers
- **Real-time Updates**: Multiple systems updated simultaneously
- **Separation of Concerns**: Each observer has single responsibility

---

## 🔄 Pattern Integration Flow

### Complete Order Lifecycle with All Patterns

```
1. VENDOR CREATION (Factory Pattern)
   ↓
   VendorFactory creates vendor + settings + hours
   
2. CUSTOMER ADDS TO CART (Strategy Pattern)
   ↓
   CartPriceCalculator applies appropriate pricing strategy
   (Regular / Voucher / Bulk Discount)
   
3. CUSTOMER PLACES ORDER (State + Observer Patterns)
   ↓
   OrderStateManager: Initialize Pending State
   QueueSubject: Notify observers (created event)
   
4. VENDOR ACCEPTS ORDER (State + Observer Patterns)
   ↓
   OrderStateManager: Pending → Accepted
   QueueSubject: Notify observers
   
5. ORDER PREPARATION (State Pattern)
   ↓
   OrderStateManager: Accepted → Preparing → Ready
   QueueSubject: Notify observers (ready event)
   
6. CUSTOMER COLLECTS (State + Observer Patterns)
   ↓
   OrderStateManager: Ready → Completed
   QueueSubject: Notify observers (collected event)
```

---

## 📊 Design Patterns Summary

| Pattern | Problem Solved | Key Benefit |
|---------|---------------|-------------|
| **Factory** | Complex vendor object creation | Consistent initialization |
| **Strategy** | Multiple pricing algorithms | Runtime flexibility |
| **Singleton** | Global auth state | Single source of truth |
| **State** | Order lifecycle management | Valid transitions only |
| **Observer** | Queue change notifications | Loose coupling |

---

## 🚀 Testing the Patterns

### Test Factory Pattern
```php
$factory = new VendorFactory();
$vendor = $factory->createVendor([...]);
// Check: User created, Settings exist, 7 operating hours
```

### Test Strategy Pattern
```php
$calculator = new CartPriceCalculator();
$calculator->setStrategy(new BulkDiscountStrategy());
$result = $calculator->calculate(100, ['quantity' => 5]);
// Expect: 10% discount applied
```

### Test State Pattern
```php
$stateManager = new OrderStateManager($pendingOrder);
$stateManager->moveToNext();
// Check: Order status changed to 'accepted'
```

### Test Observer Pattern
```php
$subject = new QueueSubject();
$subject->attach(new NotificationObserver());
$subject->notify($order, 'ready');
// Check: Notification created in database
```

---

## 📁 File Structure

```
app/
├── Patterns/
│   ├── Factory/
│   │   └── VendorFactory.php
│   ├── Strategy/
│   │   ├── PricingStrategy.php (interface)
│   │   ├── RegularPricingStrategy.php
│   │   ├── VoucherDiscountStrategy.php
│   │   ├── BulkDiscountStrategy.php
│   │   └── CartPriceCalculator.php
│   ├── State/
│   │   ├── OrderState.php (interface)
│   │   ├── PendingState.php
│   │   ├── AcceptedState.php
│   │   ├── PreparingState.php
│   │   ├── ReadyState.php
│   │   ├── CompletedState.php
│   │   ├── CancelledState.php
│   │   └── OrderStateManager.php
│   └── Observer/
│       ├── QueueObserver.php (interface)
│       ├── NotificationObserver.php
│       ├── DashboardObserver.php
│       ├── AnalyticsObserver.php
│       └── QueueSubject.php
├── Http/
│   └── Controllers/
│       ├── CartController.php (uses Strategy)
│       ├── OrderController.php (uses State, Observer)
│       ├── PaymentController.php (uses State, Observer)
│       └── VendorOrderController.php (uses State, Observer)
```

---

## 🎯 Conclusion

All five design patterns have been successfully implemented and integrated into the FoodHunter system:

✅ **Factory Pattern** - Vendor creation and initialization
✅ **Strategy Pattern** - Flexible cart pricing calculations  
✅ **Singleton Pattern** - Global authentication management (Laravel Auth)
✅ **State Pattern** - Order lifecycle and status transitions
✅ **Observer Pattern** - Queue notifications and analytics

Each pattern solves specific problems in its respective module while maintaining clean, maintainable, and extensible code architecture.
