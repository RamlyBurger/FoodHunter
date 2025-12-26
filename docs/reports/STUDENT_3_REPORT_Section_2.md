## 2. Module Description

### 2.1 Order & Pickup Module

The Order & Pickup Module serves as the order management foundation of the FoodHunter Food Ordering System. This module is responsible for managing the complete order lifecycle from creation to completion, including order status tracking, pickup queue management, and QR code verification for secure order collection. As the bridge between customers and vendors, this module ensures smooth order processing and secure food collection.

#### 2.1.1 Module Architecture Overview

The module follows a layered architecture with clear separation of concerns:

- **Controllers Layer**: API and Web controllers handling HTTP requests for order operations
- **Services Layer**: Contains `OrderService` with business logic for order processing and QR code generation
- **Patterns Layer**: Implements the State Pattern through `OrderStateManager` and individual state classes
- **Models Layer**: Eloquent models (`Order`, `OrderItem`, `Pickup`) representing order-related entities

This architecture ensures that complex order logic is centralized in services while controllers remain thin and focused on request/response handling.

#### 2.1.2 Order Lifecycle Management

When a customer places an order through the checkout process, this module takes over to manage the order through its various states. The order progresses through a well-defined state machine:

1. **Pending**: Order created, awaiting vendor confirmation
2. **Confirmed**: Vendor has accepted the order
3. **Preparing**: Food is being prepared
4. **Ready for Pickup**: Food is ready, customer notified
5. **Completed**: Customer has collected their order
6. **Cancelled**: Order was cancelled (terminal state)

Each state transition triggers appropriate actions including:
- Notification to customer via Observer Pattern (Student 4's module)
- Timestamp recording for analytics and tracking
- Queue number assignment (on confirmation)
- QR code generation (on ready status)

#### 2.1.3 State Pattern Implementation

The module implements the State Pattern for managing order status transitions. This design pattern encapsulates state-specific behavior and ensures that only valid transitions can occur. The pattern provides:

- **Explicit State Transitions**: An order cannot go directly from "pending" to "ready" - it must first be confirmed and then prepared
- **State-Specific Behavior**: Each state class handles its own valid operations
- **Transition Validation**: Invalid transitions are rejected with appropriate error messages
- **Audit Trail**: All state changes are logged with timestamps

#### 2.1.4 QR Code Pickup System

A critical feature of this module is the QR code pickup system designed for secure and efficient order collection:

1. **Queue Number Assignment**: Each order receives a unique daily queue number upon confirmation
2. **QR Code Generation**: A digitally signed QR code is generated containing order ID, queue number, and timestamp
3. **Digital Signature**: HMAC-SHA256 signature prevents tampering and forgery
4. **Verification Process**: When customers show their QR code, the signature is verified before completing pickup
5. **One-Time Use**: QR codes are invalidated after successful pickup

#### 2.1.5 Web Service Integration

This module exposes two critical APIs consumed by other modules:

- **Order Status API** (`GET /api/orders/{id}/status`): Real-time order status for frontend polling, consumed by the order tracking page
- **Pickup QR Validation API** (`POST /api/orders/validate-pickup`): Validates QR codes for the vendor's pickup scanner interface

The module also consumes:
- **Token Validation API** (Student 1): Verifies user authentication before order creation
- **Item Availability API** (Student 2): Validates all cart items are available before order creation

**Sub-Modules Implemented:**

**Order Creation:** Handles the creation of new orders from cart items. Groups items by vendor for multi-vendor orders. Creates associated payment and pickup records within a database transaction to ensure data consistency. Validates item availability and vendor status before processing.

**Order Status Tracking:** Manages order state transitions using the State Pattern. Each state (PendingState, ConfirmedState, PreparingState, ReadyState) defines its own allowed transitions and behaviors. Real-time status updates are provided via API polling.

**Pickup Queue Management:** Assigns sequential queue numbers to orders and generates QR codes for pickup verification. Tracks pickup status (waiting, ready, collected) separately from order status. Queue numbers reset daily for simplicity.

**QR Code Verification:** Generates digitally signed QR codes using HMAC-SHA256 with a secret key. Verifies QR code authenticity when customers collect their orders using timing-safe comparison to prevent timing attacks. Invalid or expired QR codes are rejected with appropriate error messages.

**Order History:** Allows customers to view their past and active orders with filtering and pagination. Provides order statistics including total orders, total spent, and favorite vendors. Vendors can view and manage orders assigned to their store.

**Order Cancellation:** Enables customers to cancel orders in pending or confirmed states only. Orders in preparing or later states cannot be cancelled. Cancelled orders record the cancellation reason and timestamp for analytics.

**Reorder Functionality:** Allows customers to quickly reorder previous orders by adding all items back to their cart. Validates item availability before adding to ensure all items are still orderable.
