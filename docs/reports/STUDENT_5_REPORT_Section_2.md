## 2. Module Description

### 2.1 Vendor Management Module

The Vendor Management Module serves as the business operations foundation of the FoodHunter Food Ordering System. This module enables vendors to manage their store profiles, menu items, vouchers, operating hours, and process customer orders.

When a user registers as a vendor, this module provides them with a comprehensive dashboard to manage all aspects of their food business. Vendors can create and update menu items with images, set prices, manage availability, create promotional vouchers, and track their sales performance.

The module implements the Factory Pattern for voucher discount calculations. Different voucher types (fixed amount vs percentage) have different calculation logic, and the VoucherFactory creates the appropriate voucher object based on the type, encapsulating the creation logic.

A critical feature is the secure file upload for menu item images. The system validates file headers (magic bytes) to prevent malicious file uploads disguised as images.

**Sub-Modules Implemented:**

**Vendor Dashboard:** Displays real-time statistics including today's orders, revenue, pending orders, and ready orders. Provides quick access to all vendor management functions.

**Menu Management:** Full CRUD operations for menu items. Vendors can add items with name, description, price, category, image, and availability status. Supports image upload with security validation.

**Voucher Management:** Create and manage promotional vouchers using the Factory Pattern. Supports fixed amount (e.g., RM5 off) and percentage (e.g., 10% off) discount types with minimum order requirements and maximum discount caps.

**Order Processing:** View and manage incoming orders. Update order status through the state machine (pending → confirmed → preparing → ready → completed). QR code scanning for pickup verification.

**Operating Hours:** Set store opening and closing times for each day of the week. Toggle store open/closed status.

**Sales Reports:** View sales analytics, order history, and revenue reports.
