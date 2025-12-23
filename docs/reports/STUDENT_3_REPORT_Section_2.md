## 2. Module Description

### 2.1 Order & Pickup Module

The Order & Pickup Module serves as the order management foundation of the FoodHunter Food Ordering System. This module is responsible for managing the complete order lifecycle from creation to completion, including order status tracking, pickup queue management, and QR code verification for secure order collection.

When a customer places an order through the checkout process, this module takes over to manage the order through its various states. The order progresses through a well-defined state machine: pending → confirmed → preparing → ready → completed. Each state transition triggers appropriate actions and notifications.

The module implements the State Pattern for managing order status transitions. This design pattern encapsulates state-specific behavior and ensures that only valid transitions can occur. For example, an order cannot go directly from "pending" to "ready" - it must first be confirmed and then prepared.

A critical feature of this module is the QR code pickup system. Each order receives a unique queue number and a digitally signed QR code. When the order is ready, the customer shows the QR code to collect their food. The digital signature prevents tampering and ensures authenticity.

**Sub-Modules Implemented:**

**Order Creation:** Handles the creation of new orders from cart items. Groups items by vendor for multi-vendor orders. Creates associated payment and pickup records within a database transaction to ensure data consistency.

**Order Status Tracking:** Manages order state transitions using the State Pattern. Each state (PendingState, ConfirmedState, PreparingState, ReadyState) defines its own allowed transitions and behaviors.

**Pickup Queue Management:** Assigns queue numbers to orders and generates QR codes for pickup verification. Tracks pickup status (waiting, ready, collected) separately from order status.

**QR Code Verification:** Generates digitally signed QR codes using HMAC-SHA256. Verifies QR code authenticity when customers collect their orders, preventing forgery and tampering.

**Order History:** Allows customers to view their past and active orders. Vendors can view and manage orders assigned to their store.

**Order Cancellation:** Enables customers to cancel orders in pending or confirmed states. Cancelled orders cannot be reactivated.
