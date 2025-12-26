## 3. Design Pattern

### 3.1 Description of Design Pattern

The Observer Pattern is a behavioral design pattern that defines a one-to-many dependency between objects so that when one object changes state, all its dependents are notified and updated automatically. This pattern promotes loose coupling between the subject and its observers. Also known as the Publish-Subscribe pattern, it was first documented by the Gang of Four and is widely used in event-driven systems.

#### 3.1.1 Pattern Overview and Event-Driven Architecture

The Observer Pattern is fundamental to event-driven architecture, where components communicate through events rather than direct method calls. This approach provides several architectural benefits:

- **Loose Coupling**: Publishers don't need to know about subscribers
- **Scalability**: New subscribers can be added without modifying publishers
- **Flexibility**: Subscribers can be dynamically attached and detached at runtime
- **Asynchronous Potential**: Events can be processed synchronously or queued for async processing

In modern applications, the Observer Pattern underpins notification systems, real-time updates, and reactive user interfaces.

#### 3.1.2 Application in FoodHunter Notifications

In the FoodHunter Cart, Checkout & Notifications Module, the Observer Pattern is used to send notifications when order events occur. The system handles three primary event types:

| Event | Trigger | Notification Content |
|-------|---------|---------------------|
| `order.created` | Customer completes checkout | "Your order #123 has been placed" |
| `order.status_changed` | Vendor updates order status | "Your order is now being prepared" |
| `order.completed` | Customer picks up order | "Thank you! Your order is complete" |

When an order is created, its status changes, or it's completed, the OrderSubject notifies all attached observers (like NotificationObserver) which then create appropriate in-app notifications. This decoupled design means the order processing code doesn't need to know anything about notifications.

#### 3.1.3 Why Observer Pattern is Ideal for Notifications

The Observer Pattern is ideal for this use case because:

- **Decoupling**: Order processing logic is completely decoupled from notification logic. The `OrderController` simply creates an order and triggers an event - it doesn't know or care how many observers are listening or what they do.

- **Extensibility**: New observers can be added without modifying existing code. Future enhancements could include:
  - `EmailObserver`: Send order confirmation emails
  - `SMSObserver`: Send text messages for ready orders
  - `PushNotificationObserver`: Send mobile push notifications
  - `AnalyticsObserver`: Track order events for business intelligence

- **Single Responsibility Principle (SRP)**: Each observer handles only its specific concern. The `NotificationObserver` creates database notifications; a future `EmailObserver` would handle only email sending.

- **Event-Driven**: Notifications are triggered automatically when events occur, eliminating the need for polling or manual notification triggers.

- **Testability**: Each component can be tested independently. The subject can be tested with mock observers, and observers can be tested with mock subjects.

#### 3.1.4 Pattern Components

The pattern consists of:

- **Subject Interface (`SubjectInterface`)**: Defines `attach()`, `detach()`, and `notify()` methods. Any class that wants to be observable must implement this interface.

- **Concrete Subject (`OrderSubject`)**: Wraps an Order model and maintains a collection of observers. Provides convenience methods like `orderCreated()`, `orderStatusChanged()`, and `orderCompleted()` that trigger the appropriate notifications.

- **Observer Interface (`ObserverInterface`)**: Defines the `update()` method that receives the subject, event type, and event data. All observers must implement this interface.

- **Concrete Observer (`NotificationObserver`)**: Implements `ObserverInterface` and creates in-app notifications based on event type. Uses PHP's `match` expression for clean event routing.

### 3.2 Implementation of Design Pattern

The Observer Pattern is implemented in the `app/Patterns/Observer` directory:

**File: `app/Patterns/Observer/SubjectInterface.php`**
```php
<?php

namespace App\Patterns\Observer;

/**
 * Observer Pattern - Subject Interface
 * Student 4: Cart, Checkout & Notifications Module
 * 
 * Defines the interface for objects that can be observed.
 */
interface SubjectInterface
{
    public function attach(ObserverInterface $observer): void;
    
    public function detach(ObserverInterface $observer): void;
    
    public function notify(string $event, array $data): void;
}
```

**File: `app/Patterns/Observer/ObserverInterface.php`**
```php
<?php

namespace App\Patterns\Observer;

/**
 * Observer Pattern - Observer Interface
 * Student 4: Cart, Checkout & Notifications Module
 * 
 * Defines the interface for objects that should be notified of changes.
 */
interface ObserverInterface
{
    public function update(SubjectInterface $subject, string $event, array $data): void;
}
```

**File: `app/Patterns/Observer/OrderSubject.php`**
```php
<?php

namespace App\Patterns\Observer;

use App\Models\Order;

/**
 * Observer Pattern - Order Subject
 * Student 4: Cart, Checkout & Notifications Module
 * 
 * Concrete subject that notifies observers about order events.
 */
class OrderSubject implements SubjectInterface
{
    private array $observers = [];
    private Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function attach(ObserverInterface $observer): void
    {
        $key = spl_object_hash($observer);
        $this->observers[$key] = $observer;
    }

    public function detach(ObserverInterface $observer): void
    {
        $key = spl_object_hash($observer);
        unset($this->observers[$key]);
    }

    public function notify(string $event, array $data = []): void
    {
        $data['order'] = $this->order;
        
        foreach ($this->observers as $observer) {
            $observer->update($this, $event, $data);
        }
    }

    public function orderCreated(): void
    {
        $this->notify('order.created', [
            'order_id' => $this->order->id,
            'user_id' => $this->order->user_id,
            'total' => $this->order->total,
        ]);
    }

    public function orderStatusChanged(string $oldStatus, string $newStatus): void
    {
        $this->notify('order.status_changed', [
            'order_id' => $this->order->id,
            'user_id' => $this->order->user_id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);
    }

    public function orderCompleted(): void
    {
        $this->notify('order.completed', [
            'order_id' => $this->order->id,
            'user_id' => $this->order->user_id,
            'total' => $this->order->total,
            'points_earned' => (int) floor((float) $this->order->total),
        ]);
    }
}
```

**File: `app/Patterns/Observer/NotificationObserver.php`**
```php
<?php

namespace App\Patterns\Observer;

use App\Models\Notification;

/**
 * Observer Pattern - Notification Observer
 * Student 4: Cart, Checkout & Notifications Module
 * 
 * Creates in-app notifications when order events occur.
 */
class NotificationObserver implements ObserverInterface
{
    public function update(SubjectInterface $subject, string $event, array $data): void
    {
        match ($event) {
            'order.created' => $this->handleOrderCreated($data),
            'order.status_changed' => $this->handleStatusChanged($data),
            'order.completed' => $this->handleOrderCompleted($data),
            default => null,
        };
    }

    private function handleOrderCreated(array $data): void
    {
        Notification::create([
            'user_id' => $data['user_id'],
            'type' => 'order_created',
            'title' => 'Order Placed Successfully',
            'message' => "Your order #{$data['order_id']} has been placed.",
            'data' => ['order_id' => $data['order_id']],
        ]);
    }

    private function handleStatusChanged(array $data): void
    {
        $messages = [
            'confirmed' => 'Your order has been confirmed by the vendor.',
            'preparing' => 'Your order is now being prepared.',
            'ready' => 'Your order is ready for pickup!',
            'cancelled' => 'Your order has been cancelled.',
        ];

        Notification::create([
            'user_id' => $data['user_id'],
            'type' => 'order_status',
            'title' => 'Order Update',
            'message' => $messages[$data['new_status']] ?? "Status: {$data['new_status']}",
            'data' => ['order_id' => $data['order_id'], 'status' => $data['new_status']],
        ]);
    }

    private function handleOrderCompleted(array $data): void
    {
        Notification::create([
            'user_id' => $data['user_id'],
            'type' => 'order_completed',
            'title' => 'Order Completed',
            'message' => "Thank you! You earned {$data['points_earned']} points.",
            'data' => ['order_id' => $data['order_id'], 'points_earned' => $data['points_earned']],
        ]);
    }
}
```

### 3.3 Class Diagram

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           Observer Pattern                                   │
│                  Cart, Checkout & Notifications Module                       │
└─────────────────────────────────────────────────────────────────────────────┘

┌────────────────────────────┐         ┌────────────────────────────┐
│    <<interface>>           │         │     <<interface>>          │
│    SubjectInterface        │         │     ObserverInterface      │
├────────────────────────────┤         ├────────────────────────────┤
│ + attach(observer): void   │         │ + update(subject, event,   │
│ + detach(observer): void   │◄────────│   data): void              │
│ + notify(event, data): void│         └────────────────────────────┘
└────────────────────────────┘                      △
            △                                       │ implements
            │ implements                            │
            │                         ┌────────────────────────────┐
┌────────────────────────────┐        │   NotificationObserver     │
│       OrderSubject         │        ├────────────────────────────┤
├────────────────────────────┤        │ + update(): void           │
│ - observers: array         │        │ - handleOrderCreated()     │
│ - order: Order             │        │ - handleStatusChanged()    │
├────────────────────────────┤        │ - handleOrderCompleted()   │
│ + attach(): void           │        └────────────────────────────┘
│ + detach(): void           │
│ + notify(): void           │────────notifies────────►
│ + orderCreated(): void     │
│ + orderStatusChanged(): void│
│ + orderCompleted(): void   │
└────────────────────────────┘
```

### 3.4 Justification for Using Observer Pattern

The Observer Pattern was chosen for the Notifications system for the following reasons:

1. **Loose Coupling**: Order processing doesn't need to know about notifications - it just triggers events.

2. **Extensibility**: New observers can be added (e.g., EmailObserver, PushNotificationObserver) without changing OrderSubject.

3. **Single Responsibility**: NotificationObserver only handles creating notifications; OrderSubject only handles order events.

4. **Event-Driven Architecture**: Notifications are automatically sent when events occur, no manual triggering needed.

5. **Testability**: Observers can be tested independently by mocking the SubjectInterface.
