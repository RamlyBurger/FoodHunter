## 3. Design Pattern

### 3.1 Description of Design Pattern

The State Pattern is a behavioral design pattern that allows an object to alter its behavior when its internal state changes. The object will appear to change its class. This pattern encapsulates state-specific behavior and delegates state transitions to state objects.

In the FoodHunter Order & Pickup Module, the State Pattern is used to manage order status transitions. Each order can be in one of several states (pending, confirmed, preparing, ready, completed, cancelled), and each state defines which transitions are allowed and what actions can be performed.

The State Pattern is ideal for this use case because:

- **Encapsulation**: State-specific behavior is encapsulated in separate classes
- **Single Responsibility**: Each state class handles only its own transitions
- **Open/Closed**: New states can be added without modifying existing code
- **Explicit Transitions**: Invalid state transitions are prevented by design

The pattern consists of:

- **State Interface (`OrderStateInterface`)**: Defines methods for state transitions
- **Abstract State (`AbstractOrderState`)**: Provides default implementations
- **Concrete States**: `PendingState`, `ConfirmedState`, `PreparingState`, `ReadyState`
- **State Manager (`OrderStateManager`)**: Factory that creates and manages state objects

### 3.2 Implementation of Design Pattern

The State Pattern is implemented in the `app/Patterns/State` directory with the following classes:

**File: `app/Patterns/State/OrderStateInterface.php`**
```php
<?php

namespace App\Patterns\State;

use App\Models\Order;

/**
 * State Pattern - Order State Interface
 * Student 3: Order & Pickup Module
 * 
 * Defines the interface for order states.
 * Each state handles transitions and actions specific to that state.
 */
interface OrderStateInterface
{
    public function getStateName(): string;
    
    public function canTransitionTo(string $newState): bool;
    
    public function confirm(Order $order): bool;
    
    public function startPreparing(Order $order): bool;
    
    public function markReady(Order $order): bool;
    
    public function complete(Order $order): bool;
    
    public function cancel(Order $order, ?string $reason = null): bool;
}
```

**File: `app/Patterns/State/AbstractOrderState.php`**
```php
<?php

namespace App\Patterns\State;

use App\Models\Order;

/**
 * State Pattern - Abstract Order State
 * Student 3: Order & Pickup Module
 * 
 * Base class providing default implementations for state transitions.
 */
abstract class AbstractOrderState implements OrderStateInterface
{
    protected array $allowedTransitions = [];

    public function canTransitionTo(string $newState): bool
    {
        return in_array($newState, $this->allowedTransitions);
    }

    public function confirm(Order $order): bool { return false; }
    public function startPreparing(Order $order): bool { return false; }
    public function markReady(Order $order): bool { return false; }
    public function complete(Order $order): bool { return false; }
    public function cancel(Order $order, ?string $reason = null): bool { return false; }

    protected function updateOrderStatus(Order $order, string $status, array $extra = []): bool
    {
        $data = array_merge(['status' => $status], $extra);
        return $order->update($data);
    }
}
```

**File: `app/Patterns/State/PendingState.php`**
```php
<?php

namespace App\Patterns\State;

use App\Models\Order;

/**
 * State Pattern - Pending State
 * Student 3: Order & Pickup Module
 */
class PendingState extends AbstractOrderState
{
    protected array $allowedTransitions = ['confirmed', 'cancelled'];

    public function getStateName(): string
    {
        return 'pending';
    }

    public function confirm(Order $order): bool
    {
        return $this->updateOrderStatus($order, 'confirmed', [
            'confirmed_at' => now(),
        ]);
    }

    public function cancel(Order $order, ?string $reason = null): bool
    {
        return $this->updateOrderStatus($order, 'cancelled', [
            'cancelled_at' => now(),
            'cancel_reason' => $reason,
        ]);
    }
}
```

**File: `app/Patterns/State/OrderStateManager.php`**
```php
<?php

namespace App\Patterns\State;

use App\Models\Order;

/**
 * State Pattern - Order State Manager
 * Student 3: Order & Pickup Module
 * 
 * Manages order state transitions using the State pattern.
 */
class OrderStateManager
{
    private static array $states = [
        'pending' => PendingState::class,
        'confirmed' => ConfirmedState::class,
        'preparing' => PreparingState::class,
        'ready' => ReadyState::class,
    ];

    public static function getState(Order $order): OrderStateInterface
    {
        $stateClass = self::$states[$order->status] ?? null;
        
        if (!$stateClass) {
            throw new \InvalidArgumentException("Unknown order state: {$order->status}");
        }

        return new $stateClass();
    }

    public static function confirm(Order $order): bool
    {
        return self::getState($order)->confirm($order);
    }

    public static function startPreparing(Order $order): bool
    {
        return self::getState($order)->startPreparing($order);
    }

    public static function markReady(Order $order): bool
    {
        return self::getState($order)->markReady($order);
    }

    public static function complete(Order $order): bool
    {
        return self::getState($order)->complete($order);
    }

    public static function cancel(Order $order, ?string $reason = null): bool
    {
        return self::getState($order)->cancel($order, $reason);
    }
}
```

**Usage in `app/Services/OrderService.php`:**
```php
public function updateStatus(Order $order, string $newStatus, ?string $reason = null): array
{
    $oldStatus = $order->status;

    if (!OrderStateManager::canTransitionTo($order, $newStatus)) {
        return [
            'success' => false,
            'message' => "Cannot transition from {$oldStatus} to {$newStatus}.",
        ];
    }

    $result = match ($newStatus) {
        'confirmed' => OrderStateManager::confirm($order),
        'preparing' => OrderStateManager::startPreparing($order),
        'ready' => OrderStateManager::markReady($order),
        'completed' => OrderStateManager::complete($order),
        'cancelled' => OrderStateManager::cancel($order, $reason),
        default => false,
    };

    return [
        'success' => $result,
        'message' => $result ? 'Status updated.' : 'Failed to update.',
        'new_status' => $order->fresh()->status,
    ];
}
```

### 3.3 Class Diagram

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                            State Pattern                                     │
│                       Order & Pickup Module                                  │
└─────────────────────────────────────────────────────────────────────────────┘

                    ┌────────────────────────────────────────┐
                    │      <<interface>>                     │
                    │      OrderStateInterface               │
                    ├────────────────────────────────────────┤
                    │ + getStateName(): string               │
                    │ + canTransitionTo(state): bool         │
                    │ + confirm(order): bool                 │
                    │ + startPreparing(order): bool          │
                    │ + markReady(order): bool               │
                    │ + complete(order): bool                │
                    │ + cancel(order, reason): bool          │
                    └────────────────────────────────────────┘
                                       △
                                       │ implements
                                       │
                    ┌────────────────────────────────────────┐
                    │      AbstractOrderState                │
                    ├────────────────────────────────────────┤
                    │ # allowedTransitions: array            │
                    │ + canTransitionTo(state): bool         │
                    │ # updateOrderStatus(order, status)     │
                    └────────────────────────────────────────┘
                                       △
                                       │ extends
           ┌───────────────┬───────────┼───────────┬───────────────┐
           │               │           │           │               │
    ┌──────┴──────┐ ┌──────┴──────┐ ┌──┴───────┐ ┌─┴────────┐
    │ PendingState│ │ConfirmedState│ │PreparingState│ │ ReadyState │
    ├─────────────┤ ├─────────────┤ ├─────────────┤ ├────────────┤
    │ confirm()   │ │ startPreparing()│ │ markReady()│ │ complete() │
    │ cancel()    │ │ cancel()    │ └─────────────┘ └────────────┘
    └─────────────┘ └─────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                          State Transitions                                   │
│   [pending] ──confirm──► [confirmed] ──prepare──► [preparing] ──ready──►    │
│       │                       │                                              │
│       └──cancel──►[cancelled]◄┘                  [ready] ──complete──► [completed]│
└─────────────────────────────────────────────────────────────────────────────┘
```

### 3.4 Justification for Using State Pattern

The State Pattern was chosen for the Order & Pickup Module for the following reasons:

1. **Clear State Transitions**: Orders have well-defined states and transitions. The pattern makes these explicit and prevents invalid transitions.

2. **Encapsulated Behavior**: Each state handles its own logic. For example, only `ReadyState` can mark an order as complete.

3. **Extensibility**: New states (e.g., "refunded", "disputed") can be added without modifying existing state classes.

4. **Maintainability**: State-specific logic is isolated, making it easier to understand and debug.

5. **Business Rules Enforcement**: The pattern naturally enforces business rules like "orders in preparing state cannot be cancelled".
