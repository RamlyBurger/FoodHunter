## 2. Module Description

### 2.1 Vendor Management Module

The Vendor Management Module serves as the business operations foundation of the FoodHunter Food Ordering System. This module enables vendors to manage their store profiles, menu items, vouchers, operating hours, and process customer orders. As the backend of the food service business, this module is critical for operational efficiency and vendor satisfaction.

#### 2.1.1 Module Architecture Overview

The module follows a layered architecture optimized for vendor operations:

- **Controllers Layer**: Separate Web and API controllers for vendor dashboard, menu management, and order processing
- **Services Layer**: Business logic for voucher calculations, sales analytics, and order management
- **Patterns Layer**: Factory Pattern implementation for voucher discount calculations
- **Models Layer**: Eloquent models (`Vendor`, `VendorHours`, `Voucher`, `UserVoucher`) for vendor data

This architecture ensures that complex business operations are handled efficiently while maintaining security and data integrity.

#### 2.1.2 Vendor Onboarding and Profile Management

When a user registers as a vendor, this module provides them with a comprehensive dashboard to manage all aspects of their food business:

1. **Profile Setup**: Store name, description, logo, and contact information
2. **Location Configuration**: Address and pickup location details
3. **Operating Hours**: Customizable hours for each day of the week
4. **Category Selection**: Food categories served (e.g., Malay, Chinese, Western)

Vendors can update their profile at any time, and changes are reflected immediately to customers browsing the platform.

#### 2.1.3 Menu Item Management

Vendors can create and update menu items with comprehensive details:

- **Basic Information**: Name, description, price, category assignment
- **Media**: Image upload with security validation (magic byte checking)
- **Availability Controls**: Toggle items as available/unavailable, set featured status
- **Bulk Operations**: Quick availability toggle, category-based filtering

The menu management interface is designed for efficiency, allowing vendors to quickly update prices or availability during peak hours.

#### 2.1.4 Factory Pattern for Voucher System

The module implements the Factory Pattern for voucher discount calculations. This design provides:

- **Voucher Types**: Fixed amount (e.g., RM5 off) and percentage (e.g., 10% off)
- **Constraints**: Minimum order requirements, maximum discount caps, usage limits
- **Validity Periods**: Start and end dates for promotional campaigns
- **User Limits**: Per-user redemption limits to prevent abuse

The `VoucherFactory` creates the appropriate voucher object based on type, encapsulating the calculation logic and ensuring correct discount application.

#### 2.1.5 Order Processing Workflow

Vendors receive orders through the dashboard and process them through the state machine:

1. **New Orders Alert**: Real-time notification of incoming orders
2. **Order Confirmation**: Review order details and confirm acceptance
3. **Preparation Tracking**: Mark orders as "preparing" when cooking begins
4. **Ready Notification**: Mark as "ready" to notify customer for pickup
5. **QR Verification**: Scan customer's QR code to complete the pickup

The interface shows order queue with color-coded status indicators for quick visual management.

#### 2.1.6 Security: File Upload Validation

A critical security feature is the secure file upload for menu item images. The system validates file headers (magic bytes) to prevent malicious file uploads disguised as images. This protects against:

- Executable files with renamed extensions
- PHP scripts uploaded as images
- Malware embedded in image files

#### 2.1.7 Web Service Integration

This module exposes two critical APIs:

- **Voucher Validation API** (`POST /api/vouchers/validate`): Consumed by Cart module during checkout
- **Vendor Availability API** (`GET /api/vendors/{id}/availability`): Returns store open/closed status and hours

**Sub-Modules Implemented:**

**Vendor Dashboard:** Displays real-time statistics including today's orders, revenue, pending orders, and ready orders. Shows top-selling items and recent order activity. Provides quick access to all vendor management functions with a modern, responsive interface.

**Menu Management:** Full CRUD operations for menu items with drag-and-drop image upload. Vendors can add items with name, description, price, category, image, and availability status. Supports bulk availability toggle and category filtering. Image uploads are validated using magic byte checking for security.

**Voucher Management:** Create and manage promotional vouchers using the Factory Pattern. Supports fixed amount (e.g., RM5 off) and percentage (e.g., 10% off with RM20 max) discount types. Configurable minimum order requirements, maximum discount caps, usage limits, and validity periods.

**Order Processing:** View and manage incoming orders with real-time updates. Update order status through the state machine (pending → confirmed → preparing → ready → completed). Integrated QR code scanner for pickup verification. Order history with search and filtering.

**Operating Hours:** Set store opening and closing times for each day of the week with an intuitive time picker. Quick toggle for store open/closed status that immediately affects customer visibility. Holiday scheduling for temporary closures.

**Sales Reports:** Comprehensive analytics dashboard showing daily/weekly/monthly revenue, order counts, average order value, and trending items. Exportable reports for accounting purposes. Customer insights and peak hour analysis.
