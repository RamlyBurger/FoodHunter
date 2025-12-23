# FoodHunter - Module Allocation for 5 Students

## System Overview
**FoodHunter** is a university canteen food ordering system for TARUMT that allows students to browse menus, order food, apply vouchers, and pick up orders efficiently.

---

## Module Allocation Summary

| Student | Module Name | Design Pattern | Entity Classes (Tables) |
|---------|-------------|----------------|-------------------------|
| 1 | User & Authentication | **Strategy** | User, Session, EmailVerification, PasswordResetToken, *Vendor* |
| 2 | Menu & Catalog | **Repository** | Category, MenuItem, Wishlist, *User*, *Vendor* |
| 3 | Order & Pickup | **State** | Order, OrderItem, Pickup, *User*, *Vendor*, *MenuItem*, *Payment* |
| 4 | Cart, Checkout & Notifications | **Observer** | CartItem, Payment, Notification, *User*, *MenuItem*, *Order*, *Voucher* |
| 5 | Vendor Management | **Factory** | Vendor, VendorHour, Voucher, UserVoucher, *User*, *MenuItem*, *Order* |

> **Note**: *Italicized* entity classes indicate shared/overlapped tables with other modules.

---

## Table Overlap Matrix

| Table | S1 | S2 | S3 | S4 | S5 | Description |
|-------|:--:|:--:|:--:|:--:|:--:|-------------|
| users | ✅ | 🔗 | 🔗 | 🔗 | 🔗 | All modules need user context |
| sessions | ✅ | | | | | Session management |
| email_verifications | ✅ | | | | | OTP verification |
| password_reset_tokens | ✅ | | | | | Password reset |
| categories | | ✅ | | | | Menu categories |
| menu_items | | ✅ | 🔗 | 🔗 | 🔗 | Core product entity |
| wishlists | | ✅ | | | | User favorites |
| orders | | | ✅ | 🔗 | 🔗 | Order processing |
| order_items | | | ✅ | | | Order line items |
| pickups | | | ✅ | | | Pickup queue |
| cart_items | | | | ✅ | | Shopping cart |
| payments | | | 🔗 | ✅ | | Payment processing |
| notifications | | | | ✅ | | In-app notifications |
| vendors | 🔗 | 🔗 | 🔗 | | ✅ | Vendor profiles |
| vendor_hours | | | | | ✅ | Operating hours |
| vouchers | | | | 🔗 | ✅ | Discount vouchers |
| user_vouchers | | | | 🔗 | ✅ | Redeemed vouchers |

**Legend:** ✅ = Primary Owner | 🔗 = Uses/Overlaps

---

## Student 1: User & Authentication Module

### Scope
- User registration with email verification (OTP)
- Login/logout with remember me functionality
- Profile management
- Password reset
- Session management
- Google OAuth integration

### Entity Classes (Database Tables)
| Table | Columns | Relationship |
|-------|---------|--------------|
| `users` | id, name, email, role, phone, avatar, email_verified_at, password, remember_token, google_id, pending_email | Has many: orders, cart_items, wishlists, notifications |
| `sessions` | id, user_id, ip_address, user_agent, payload, last_activity | Belongs to: users |
| `email_verifications` | id, email, code, type, user_id, expires_at, verified_at | Belongs to: users |
| `password_reset_tokens` | email, token, created_at | References: users.email |
| `vendors` *(shared)* | user_id FK | Used for role-based authentication |

### Design Pattern: **Strategy Pattern**
- **Use Case**: Different authentication methods (password login, token-based API, OAuth)
- **Implementation**:
  - `AuthStrategyInterface` - defines `authenticate()` method
  - `PasswordAuthStrategy` - email/password authentication
  - `TokenAuthStrategy` - API token authentication
  - `AuthContext` - selects and executes the appropriate strategy

### Security Threats & Practices
| Threat | Secure Coding Practice | OWASP Reference |
|--------|------------------------|-----------------|
| Brute Force Attack | Rate Limiting (max 5 attempts/15 min) | [41, 94] |
| Session Hijacking | Session Regeneration on login | [66-67] |
| Weak Password | Password Complexity Validation | [38-39] |

### Web Services
| Type | Endpoint | Description |
|------|----------|-------------|
| **Expose** | `POST /api/auth/validate-token` | Validates API token, returns user info |
| **Consume** | Student 4's `POST /api/notifications/send` | Send welcome notification on registration |

---

## Student 2: Menu & Catalog Module

### Scope
- Category management
- Menu item browsing & search
- Wishlist management (favorites)
- Featured items display

### Entity Classes (Database Tables)
| Table | Columns | Relationship |
|-------|---------|--------------|
| `categories` | id, name, slug, description, image, is_active, sort_order | Has many: menu_items |
| `menu_items` | id, vendor_id, category_id, name, slug, description, price, original_price, image, is_available, is_featured, prep_time, calories, total_sold | Belongs to: vendors, categories |
| `wishlists` | id, user_id, menu_item_id | Belongs to: users, menu_items |
| `users` *(shared)* | - | For wishlist ownership |
| `vendors` *(shared)* | - | Menu items belong to vendors |

### Design Pattern: **Repository Pattern**
- **Use Case**: Abstract database access for menu items
- **Implementation**:
  - `MenuItemRepositoryInterface` - defines data access methods
  - `EloquentMenuItemRepository` - concrete implementation using Eloquent ORM
  - Provides: `findById()`, `getAvailable()`, `getFeatured()`, `search()`, etc.

### Security Threats & Practices
| Threat | Secure Coding Practice | OWASP Reference |
|--------|------------------------|-----------------|
| SQL Injection | Parameterized Queries via Eloquent ORM | [167] |
| XSS Attack | Output Encoding for names & descriptions | [19-20] |
| Path Traversal | Input Path Validation for file access | [35] |

### Web Services
| Type | Endpoint | Description |
|------|----------|-------------|
| **Expose** | `GET /api/menu/items/{id}/availability` | Returns item availability status |
| **Consume** | Student 1's `POST /api/auth/validate-token` | Verify user token for wishlist operations |

---

## Student 3: Order & Pickup Module

### Scope
- Order creation and management
- Order status tracking (state transitions)
- Pickup queue management
- QR code generation for pickup verification
- Order history

### Entity Classes (Database Tables)
| Table | Columns | Relationship |
|-------|---------|--------------|
| `orders` | id, user_id, vendor_id, order_number, subtotal, service_fee, discount, total, status, notes, cancel_reason, confirmed_at, ready_at, completed_at, cancelled_at | Belongs to: users, vendors; Has many: order_items; Has one: payment, pickup |
| `order_items` | id, order_id, menu_item_id, item_name, unit_price, quantity, subtotal, special_instructions | Belongs to: orders, menu_items |
| `pickups` | id, order_id, queue_number, qr_code, status, ready_at, collected_at | Belongs to: orders |
| `users` *(shared)* | - | Orders belong to users |
| `vendors` *(shared)* | - | Orders belong to vendors |
| `menu_items` *(shared)* | - | Order items reference menu items |
| `payments` *(shared)* | - | Orders have payment records |

### Design Pattern: **State Pattern**
- **Use Case**: Order status transitions with different behaviors per state
- **Implementation**:
  - `OrderStateInterface` - defines state methods (`confirm()`, `prepare()`, `ready()`, `complete()`, `cancel()`)
  - `PendingState`, `ConfirmedState`, `PreparingState`, `ReadyState` - concrete states
  - `OrderStateManager` - manages state transitions
  - Valid transitions: pending → confirmed → preparing → ready → completed

### Security Threats & Practices
| Threat | Secure Coding Practice | OWASP Reference |
|--------|------------------------|-----------------|
| IDOR (accessing others' orders) | Authorization Checks (verify user_id ownership) | [86] |
| QR Code Tampering | Digital Signature (HMAC) on QR codes | [104] |
| Race Condition (double order) | Database Transactions with Locking | [89] |

### Web Services
| Type | Endpoint | Description |
|------|----------|-------------|
| **Expose** | `GET /api/orders/{id}/status` | Returns real-time order status and pickup info |
| **Consume** | Student 2's `GET /api/menu/items/{id}/availability` | Validate items before order creation |

---

## Student 4: Cart, Checkout & Notifications Module

### Scope
- Shopping cart management (add/update/remove)
- Cart summary calculation
- Voucher application
- Payment processing (Cash, Card/Stripe, E-Wallet, Online Banking)
- In-app notifications

### Entity Classes (Database Tables)
| Table | Columns | Relationship |
|-------|---------|--------------|
| `cart_items` | id, user_id, menu_item_id, quantity, special_instructions | Belongs to: users, menu_items |
| `payments` | id, order_id, amount, method, status, transaction_id, paid_at | Belongs to: orders |
| `notifications` | id, user_id, type, title, message, data, is_read, read_at | Belongs to: users |
| `users` *(shared)* | - | Cart and notifications belong to users |
| `menu_items` *(shared)* | - | Cart items reference menu items |
| `orders` *(shared)* | - | Payments linked to orders; notifications reference orders |
| `vouchers` *(shared)* | - | Applied during checkout |
| `user_vouchers` *(shared)* | - | Track voucher usage |

### Design Pattern: **Observer Pattern**
- **Use Case**: Notify users when events occur (order status change, payment success)
- **Implementation**:
  - `SubjectInterface` - defines `attach()`, `detach()`, `notify()` methods
  - `OrderSubject` - concrete subject that triggers notifications
  - `ObserverInterface` - defines `update()` method
  - `NotificationObserver` - creates in-app notifications

### Security Threats & Practices
| Threat | Secure Coding Practice | OWASP Reference |
|--------|------------------------|-----------------|
| Price Manipulation | Server-side Price Validation (re-fetch from DB) | [1] |
| CSRF Attack | CSRF Token Protection on all forms | [73] |
| Replay Attack (payment) | Transaction Idempotency Keys | [64] |

### Web Services
| Type | Endpoint | Description |
|------|----------|-------------|
| **Expose** | `POST /api/notifications/send` | Accepts notification requests from other modules |
| **Consume** | Student 3's `GET /api/orders/{id}/status` | Get order status for notification content |

---

## Student 5: Vendor Management Module

### Scope
- Vendor profile management
- Vendor menu CRUD (create, update, delete menu items)
- Voucher creation & management
- Operating hours management
- Vendor dashboard
- Sales reports

### Entity Classes (Database Tables)
| Table | Columns | Relationship |
|-------|---------|--------------|
| `vendors` | id, user_id, store_name, slug, description, phone, logo, banner, is_open, is_active, min_order_amount, avg_prep_time, total_orders | Belongs to: users; Has many: menu_items, vendor_hours, vouchers, orders |
| `vendor_hours` | id, vendor_id, day_of_week, open_time, close_time, is_closed | Belongs to: vendors |
| `vouchers` | id, vendor_id, code, name, description, type, value, min_order, max_discount, usage_limit, usage_count, per_user_limit, starts_at, expires_at, is_active | Belongs to: vendors; Has many: user_vouchers |
| `user_vouchers` | id, user_id, voucher_id, usage_count, redeemed_at, used_at | Belongs to: users, vouchers |
| `users` *(shared)* | - | Vendor linked to user account |
| `menu_items` *(shared)* | - | Vendors manage their menu items |
| `orders` *(shared)* | - | Vendors receive and process orders |

### Design Pattern: **Factory Pattern**
- **Use Case**: Create different voucher types with different discount calculation logic
- **Implementation**:
  - `VoucherInterface` - defines `calculateDiscount()` method
  - `FixedVoucher` - fixed amount discount (e.g., RM5 off)
  - `PercentageVoucher` - percentage discount (e.g., 10% off)
  - `VoucherFactory` - creates appropriate voucher object based on type

### Security Threats & Practices
| Threat | Secure Coding Practice | OWASP Reference |
|--------|------------------------|-----------------|
| Malicious File Upload | File Header (Magic Byte) Validation + Secure Storage | [104, 143] |
| Information Disclosure | Generic Error Messages with Server-Side Logging | [107-130] |
| Authentication Attacks (Brute Force) | Authentication Failure Logging and Detection | [119] |

### Web Services
| Type | Endpoint | Description |
|------|----------|-------------|
| **Expose** | `GET /api/vendor/vouchers/validate` | Validates voucher code, returns discount info |
| **Consume** | Student 4's `POST /api/notifications/send` | Send notification when order received |

---

## Web Service Consume/Expose Chain

```
┌─────────────┐     consumes      ┌─────────────┐
│  Student 1  │ ───────────────▶  │  Student 4  │
│    Auth     │                   │ Notification│
└─────────────┘                   └─────────────┘
      ▲                                 │
      │ consumes                        │ consumes
      │                                 ▼
┌─────────────┐                   ┌─────────────┐
│  Student 2  │                   │  Student 3  │
│    Menu     │ ◀─────────────────│    Order    │
└─────────────┘     consumes      └─────────────┘
      ▲                                 ▲
      │                                 │
      │ consumes                        │ consumes
      │                                 │
      └───────────────┬─────────────────┘
                      │
                ┌─────────────┐
                │  Student 5  │
                │   Vendor    │
                └─────────────┘
```

---

## Design Patterns Summary

| Student | Module | Pattern | Key Classes |
|---------|--------|---------|-------------|
| 1 | User & Authentication | **Strategy** | AuthContext, AuthStrategyInterface, PasswordAuthStrategy, TokenAuthStrategy |
| 2 | Menu & Catalog | **Repository** | MenuItemRepositoryInterface, EloquentMenuItemRepository |
| 3 | Order & Pickup | **State** | OrderStateInterface, PendingState, ConfirmedState, PreparingState, ReadyState, OrderStateManager |
| 4 | Cart, Checkout & Notifications | **Observer** | SubjectInterface, ObserverInterface, OrderSubject, NotificationObserver |
| 5 | Vendor Management | **Factory** | VoucherFactory, VoucherInterface, FixedVoucher, PercentageVoucher |

---

## Security Practices Summary

| Student | Threat 1 | Practice 1 | Threat 2 | Practice 2 | Threat 3 | Practice 3 |
|---------|----------|------------|----------|------------|----------|------------|
| 1 | Brute Force Attack | Rate Limiting [OWASP 41, 94] | Session Hijacking | Session Regeneration [OWASP 66-67] | Weak Password | Password Complexity [OWASP 38-39] |
| 2 | SQL Injection | Parameterized Queries [OWASP 167] | XSS Attack | Output Encoding [OWASP 19-20] | Path Traversal | Input Path Validation [OWASP 35] |
| 3 | IDOR | Authorization Checks [OWASP 86] | QR Code Tampering | Digital Signatures [OWASP 104] | Race Condition | Database Transactions [OWASP 89] |
| 4 | Price Manipulation | Server-side Price Validation [OWASP 1] | CSRF Attack | CSRF Protection [OWASP 73] | Replay Attack | Idempotency Keys [OWASP 64] |
| 5 | Malicious File Upload | File Header Validation [OWASP 104, 143] | Information Disclosure | Generic Error Messages [OWASP 107-130] | Unauthorized Access | Role-based Access Control [OWASP 119] |

**Note**: Input validation is compulsory for ALL modules but does not count toward the 3 required practices.

---

## Database ERD Summary

```
users (1) ──────< orders (M) ─────> vendors (M)
  │                  │
  │                  ├──< order_items (M) ───> menu_items (M)
  │                  │
  │                  ├──< payments (1)
  │                  │
  │                  └──< pickups (1)
  │
  ├──< cart_items (M) ───> menu_items (M)
  │
  ├──< wishlists (M) ───> menu_items (M)
  │
  ├──< user_vouchers (M) ───> vouchers (M) ───> vendors (M)
  │
  ├──< notifications (M)
  │
  ├──< email_verifications (M)
  │
  └──< vendors (1) ──< menu_items (M)
                   │
                   └──< vendor_hours (M)

categories (1) ──< menu_items (M)
```

## Complete Table Count: 17 Tables

| # | Table | Primary Owner | Shared With |
|---|-------|---------------|-------------|
| 1 | users | Student 1 | S2, S3, S4, S5 |
| 2 | sessions | Student 1 | - |
| 3 | email_verifications | Student 1 | - |
| 4 | password_reset_tokens | Student 1 | - |
| 5 | categories | Student 2 | - |
| 6 | menu_items | Student 2 | S3, S4, S5 |
| 7 | wishlists | Student 2 | - |
| 8 | orders | Student 3 | S4, S5 |
| 9 | order_items | Student 3 | - |
| 10 | pickups | Student 3 | - |
| 11 | cart_items | Student 4 | - |
| 12 | payments | Student 4 | S3 |
| 13 | notifications | Student 4 | - |
| 14 | vendors | Student 5 | S1, S2, S3 |
| 15 | vendor_hours | Student 5 | - |
| 16 | vouchers | Student 5 | S4 |
| 17 | user_vouchers | Student 5 | S4 |

---

## Web Service Interface Agreement

All API responses must follow this format:

```json
{
  "timestamp": "2025-01-01T12:00:00Z",
  "request_id": "uuid-v4",
  "status": "success|error",
  "success": true|false,
  "message": "Human readable message",
  "data": { ... },
  "errors": { ... }
}
```

### Required Headers
- `Content-Type: application/json`
- `Authorization: Bearer {token}` (for authenticated endpoints)
- `X-Request-ID: {uuid}` (for tracing)
