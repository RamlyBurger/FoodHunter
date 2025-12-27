## 3. Design Pattern

### 3.1 Description of Design Pattern

The State Pattern is a behavioral design pattern that allows an object to alter its behavior when its internal state changes. The object will appear to change its class. This pattern encapsulates state-specific behavior and delegates state transitions to state objects. Originally described by the Gang of Four (GoF), the State Pattern is particularly useful for objects whose behavior changes dramatically based on their current state.

#### 3.1.1 Pattern Overview and Finite State Machines

The State Pattern is closely related to the concept of Finite State Machines (FSM) in computer science. An FSM consists of:
- A finite set of states
- A set of inputs (events/triggers)
- A transition function that determines the next state
- An initial state
- A set of final states

In the context of order management, the FSM ensures that orders follow a predictable path from creation to completion, with clear rules about which transitions are valid at each stage.

#### 3.1.2 Application in FoodHunter Order Management

In the FoodHunter Order & Pickup Module, the State Pattern is used to manage order status transitions. Each order can be in one of several states, and each state defines:

| State | Description | Valid Transitions |
|-------|-------------|-------------------|
| **Pending** | Order created, awaiting vendor | confirmed, cancelled |
| **Confirmed** | Vendor accepted | preparing, cancelled |
| **Preparing** | Food being made | ready |
| **Ready** | Food ready for pickup | completed |
| **Completed** | Customer collected | (terminal) |
| **Cancelled** | Order cancelled | (terminal) |

Each state class encapsulates the logic for determining whether a transition is valid and executing the transition with appropriate side effects (timestamps, notifications, etc.).

#### 3.1.3 Why State Pattern is Ideal for Order Management

The State Pattern is ideal for this use case because:

- **Encapsulation**: State-specific behavior is encapsulated in separate classes. The `PendingState` class knows that it can only transition to `confirmed` or `cancelled`, and handles those transitions appropriately.

- **Single Responsibility Principle (SRP)**: Each state class handles only its own transitions. This makes the code easier to understand and maintain. Adding new behavior to a specific state doesn't affect other states.

- **Open/Closed Principle (OCP)**: New states can be added without modifying existing code. If FoodHunter adds a "delayed" state for orders waiting on ingredients, a new `DelayedState` class can be created without changing existing state classes.

- **Explicit Transitions**: Invalid state transitions are prevented by design. The pattern makes it impossible for an order to skip from `pending` directly to `ready` - the transition logic is built into the state classes themselves.

- **Eliminates Conditionals**: Without the State Pattern, order status logic would require extensive if-else or switch statements scattered throughout the codebase. The pattern centralizes this logic in dedicated classes.

- **Testability**: Each state class can be unit tested in isolation, verifying that it correctly handles allowed transitions and rejects invalid ones.

#### 3.1.4 Pattern Components

The pattern consists of:

- **State Interface (`OrderStateInterface`)**: Defines methods for state transitions including `confirm()`, `startPreparing()`, `markReady()`, `complete()`, and `cancel()`. Also defines `canTransitionTo()` for validation.

- **Abstract State (`AbstractOrderState`)**: Provides default implementations that return `false` for all transitions. Concrete states override only the transitions they support.

- **Concrete States**: `PendingState`, `ConfirmedState`, `PreparingState`, `ReadyState` - each implements the transitions valid for that state with appropriate database updates and timestamp recording.

- **State Manager (`OrderStateManager`)**: Factory class that creates the appropriate state object based on an order's current status and provides static methods for common operations like `confirm()`, `prepare()`, `ready()`, and `cancel()`.

### 3.2 Implementation of Design Pattern

The State Pattern is implemented in the `app/Patterns/State` directory with the following classes:

**File: `app/Patterns/State/OrderStateInterface.php`**
```php
<?php

namespace App\Patterns\State;

use App\Models\Order;

/**
 * State Pattern - Order State Interface
 * Low Nam Lee: Order & Pickup Module
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
 * Low Nam Lee: Order & Pickup Module
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
 * Low Nam Lee: Order & Pickup Module
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
 * Low Nam Lee: Order & Pickup Module
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
    ┌──────┴──────┐ ┌──────┴──────┐ ┌──┴───────────┐ ┌─┴────────────┐
    │ PendingState│ │ConfirmedState│ │PreparingState │ │  ReadyState  │
    ├─────────────┤ ├──────────────┤ ├───────────────┤ ├──────────────┤
    │+getStateName│ │+getStateName │ │+getStateName  │ │+getStateName │
    │+confirm()   │ │+startPreparing│ │+markReady()   │ │+complete()   │
    │+cancel()    │ │+cancel()     │ └───────────────┘ └──────────────┘
    └─────────────┘ └──────────────┘

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
