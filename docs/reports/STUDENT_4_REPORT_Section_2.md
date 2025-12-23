## 2. Module Description

### 2.1 Cart, Checkout & Notifications Module

The Cart, Checkout & Notifications Module serves as the shopping and notification foundation of the FoodHunter Food Ordering System. This module manages the complete shopping cart workflow, checkout process, voucher application, and in-app notification system.

When a customer browses menu items, this module handles adding items to their cart, updating quantities, and calculating totals. During checkout, it processes vouchers using the Factory Pattern and creates orders with associated payment and pickup records. The notification system uses the Observer Pattern to automatically notify users when order events occur.

The module implements the Observer Pattern for the notification system. When order events occur (order created, status changed, order completed), the OrderSubject notifies all attached observers, and the NotificationObserver creates appropriate in-app notifications for users.

A critical feature is the server-side price validation to prevent price manipulation attacks. All prices are fetched from the database during calculation - client-submitted prices are never trusted.

**Sub-Modules Implemented:**

**Shopping Cart:** Manages cart items including add, update, remove, and clear operations. Cart items are persisted in the database and associated with the authenticated user.

**Cart Summary Calculation:** Calculates subtotals, service fees, and discounts. Uses the Factory Pattern (VoucherFactory) to apply voucher discounts based on voucher type (fixed or percentage).

**Checkout Processing:** Converts cart items into orders grouped by vendor. Handles payment method selection (cash, card/Stripe, e-wallet, online banking). Creates order, payment, and pickup records within a database transaction.

**Voucher Application:** Validates and applies voucher codes during checkout. Supports fixed amount and percentage-based discounts with minimum order requirements and maximum discount caps.

**In-App Notifications:** Uses Observer Pattern to send notifications when order events occur. Supports notification types: order_created, order_status, order_completed.

**Notification Management:** Allows users to view, mark as read, and delete notifications. Provides unread count for UI badges.
