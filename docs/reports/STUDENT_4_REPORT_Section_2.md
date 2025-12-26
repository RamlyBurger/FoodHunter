## 2. Module Description

### 2.1 Cart, Checkout & Notifications Module

The Cart, Checkout & Notifications Module serves as the shopping and notification foundation of the FoodHunter Food Ordering System. This module manages the complete shopping cart workflow, checkout process, voucher application, and in-app notification system. As the critical path between product discovery and order completion, this module directly impacts conversion rates and user satisfaction.

#### 2.1.1 Module Architecture Overview

The module follows a layered architecture designed for maintainability and security:

- **Controllers Layer**: Separate API and Web controllers for cart operations, checkout, and notifications
- **Services Layer**: Business logic for cart calculations, payment processing, and notification delivery
- **Patterns Layer**: Observer Pattern implementation for event-driven notifications
- **Models Layer**: Eloquent models (`CartItem`, `Payment`, `Notification`) for data persistence

This separation ensures that sensitive operations like payment processing are isolated from presentation logic.

#### 2.1.2 Shopping Cart Workflow

When a customer browses menu items, this module handles the complete cart lifecycle:

1. **Add to Cart**: Items are added with quantity and special instructions. The system validates item availability via Student 2's Item Availability API before adding.
2. **Update Quantity**: Users can increase/decrease quantities with real-time price recalculation
3. **Remove Items**: Individual items can be removed, or the entire cart can be cleared
4. **Persistence**: Cart items are stored in the database, allowing users to resume shopping across sessions and devices

The cart automatically handles edge cases such as:
- Items becoming unavailable after being added
- Vendor closing while items are in cart
- Price changes between adding and checkout

#### 2.1.3 Checkout Process

During checkout, the module orchestrates multiple operations within a database transaction to ensure data consistency:

1. **Cart Validation**: All items are verified for availability and price accuracy
2. **Voucher Application**: If a voucher code is applied, the Factory Pattern calculates the appropriate discount
3. **Order Creation**: Cart items are grouped by vendor, creating separate orders for multi-vendor carts
4. **Payment Processing**: Based on selected method (cash, Stripe, e-wallet), appropriate payment records are created
5. **Cart Clearing**: Successfully checked-out items are removed from the cart
6. **Notification Trigger**: Observer Pattern notifies the user of successful order creation

#### 2.1.4 Observer Pattern for Notifications

The module implements the Observer Pattern for the notification system, providing event-driven communication:

- **OrderSubject**: Wraps an Order model and maintains a list of observers
- **NotificationObserver**: Listens for order events and creates appropriate notifications
- **Event Types**: `order.created`, `order.status_changed`, `order.completed`

This decoupled design allows easy addition of new observers (e.g., EmailObserver, SMSObserver) without modifying existing code.

#### 2.1.5 Security: Server-Side Price Validation

A critical feature is the server-side price validation to prevent price manipulation attacks. The system enforces:

- All prices are fetched from the database during calculation
- Client-submitted prices are never trusted
- Voucher discounts are recalculated server-side
- Total amounts are computed at checkout time, not stored in the cart

This prevents attackers from using browser DevTools or proxy tools to modify prices.

#### 2.1.6 Web Service Integration

This module exposes and consumes several web services:

**Exposed APIs:**
- **Cart Summary API** (`GET /api/cart/summary`): Returns current cart totals for header display
- **Send Notification API** (`POST /api/notifications/send`): Allows other modules to send notifications

**Consumed APIs:**
- **Voucher Validation API** (Student 5): Validates voucher codes and calculates discounts

**Sub-Modules Implemented:**

**Shopping Cart:** Manages cart items including add, update, remove, and clear operations. Cart items are persisted in the database and associated with the authenticated user. Supports special instructions for each item (e.g., "no onions", "extra spicy").

**Cart Summary Calculation:** Calculates subtotals, service fees, and discounts with precision handling for currency. Uses the Factory Pattern (VoucherFactory from Student 5) to apply voucher discounts based on voucher type (fixed or percentage).

**Checkout Processing:** Converts cart items into orders grouped by vendor for efficient preparation. Handles multiple payment methods (cash on pickup, credit/debit card via Stripe, e-wallet, online banking). Creates order, payment, and pickup records within a database transaction ensuring atomicity.

**Voucher Application:** Validates and applies voucher codes during checkout by consuming Student 5's Voucher Validation API. Supports fixed amount and percentage-based discounts with minimum order requirements and maximum discount caps. Prevents double-application of vouchers.

**In-App Notifications:** Uses Observer Pattern to send real-time notifications when order events occur. Supports multiple notification types: order_created, order_status, order_completed, promotion, and system announcements.

**Notification Management:** Allows users to view notifications in a dropdown and dedicated page. Supports mark as read (individual and bulk), delete, and provides unread count for UI badge indicators. Notifications are paginated for performance.
