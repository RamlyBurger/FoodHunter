# FoodHunter - University Canteen Food Ordering System

A PHP MVC web-based food ordering system for TARUMT university canteen, built with Laravel framework.

## Team Assignment Overview

This system is designed for **5 students** to develop, with each student responsible for one module implementing:
- MVC Architecture with ORM (Eloquent)
- One unique Design Pattern
- Two secure coding practices
- REST API web services (expose + consume)

---

## Module Allocation

| Student | Module | Design Pattern | Primary Tables | Web Services |
|---------|--------|----------------|----------------|---------------|
| 1 | User & Authentication | **Strategy** | users, sessions, email_verifications, password_reset_tokens | Exposes: Token Validation, User Stats |
| 2 | Menu & Catalog | **Repository** | categories, menu_items, wishlists | Exposes: Item Availability, Popular Items |
| 3 | Order & Pickup | **State** | orders, order_items, pickups | Exposes: Order Status, Pickup QR Validation |
| 4 | Cart, Checkout & Notifications | **Observer** | cart_items, payments, notifications | Exposes: Cart Summary, Send Notification |
| 5 | Vendor Management | **Factory** | vendors, vendor_hours, vouchers, user_vouchers | Exposes: Voucher Validation, Vendor Availability |

> **Note**: *Italicized* entity classes in detailed docs indicate shared/overlapped tables with other modules.

---

## Web Service Consume/Expose Chain

```
┌─────────────┐     consumes      ┌─────────────┐
│  Ng Wayne Xiang  │ ───────────────▶  │  Lee Song Yan  │
│    Auth     │                   │ Notification│
└─────────────┘                   └─────────────┘
      ▲                                 │
      │ consumes                        │ consumes
      │                                 ▼
┌─────────────┐                   ┌─────────────┐
│  Haerine Deepak Singh  │                   │  Low Nam Lee  │
│    Menu     │ ◀─────────────────│    Order    │
└─────────────┘     consumes      └─────────────┘
      ▲                                 ▲
      │                                 │
      │ consumes                        │ consumes
      │                                 │
      └───────────────┬─────────────────┘
                      │
                ┌─────────────┐
                │  Lee Kin Hang  │
                │   Vendor    │
                └─────────────┘
```

### Web Service Details

| Student | Module | Exposes | Consumes |
|---------|--------|---------|----------|
| 1 | User & Auth | `POST /api/auth/validate-token`, `GET /api/auth/user-stats` | Lee Song Yan's `POST /api/notifications/send` |
| 2 | Menu & Catalog | `GET /api/menu/{id}/availability`, `GET /api/menu/popular` | Ng Wayne Xiang's `POST /api/auth/validate-token` |
| 3 | Order & Pickup | `GET /api/orders/{id}/status`, `POST /api/orders/validate-pickup` | Haerine Deepak Singh's `GET /api/menu/{id}/availability` |
| 4 | Cart, Checkout & Notifications | `GET /api/cart/summary`, `POST /api/notifications/send` | Lee Kin Hang's `POST /api/vouchers/validate` |
| 5 | Vendor Management | `POST /api/vouchers/validate`, `GET /api/vendors/{id}/availability` | Lee Song Yan's `POST /api/notifications/send` |

---

## Technology Stack

- **Framework**: Laravel 12.x
- **PHP**: 8.2+
- **Database**: MySQL 8.0+
- **Authentication**: Laravel Sanctum (Token-based)
- **Architecture**: MVC with ORM

---

## Installation

### Prerequisites
- PHP 8.2+
- Composer
- MySQL 8.0+
- Node.js (optional, for frontend assets)

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd FoodHunter
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   copy .env file from https://drive.google.com/file/d/1noamh6UL4saGwrma91BFsHz4wwYXadAk/view?usp=drive_link
   php artisan key:generate
   ```

4. **Create database**
   ```sql
   CREATE DATABASE foodhunter CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

5. **Run migrations and seeders**
   ```bash
   php artisan migrate:fresh --seed
   ```

6. **Start the development server**
   ```bash
   php artisan serve
   ```

7. **Test API**
   ```bash
   http://127.0.0.1:8000/api-tester
   ```

---

## Test Credentials

| Role | Email | Password |
|------|-------|----------|
| Customer | john@example.com | password123 |
| Customer | haerineds-jm22@student.tarc.edu.my | password123 |
| Customer | ngwx-jm22@student.tarc.edu.my | password123 |
| Vendor | lownl-jm22@student.tarc.edu.my | password123 |
| Vendor | leekh-jm22@student.tarc.edu.my | password123 |
| Vendor | leesy-jm22@student.tarc.edu.my | password123 |

---

## API Endpoints

### Public Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/register` | Register new user |
| POST | `/api/auth/login` | User login |
| GET | `/api/categories` | List categories |
| GET | `/api/vendors` | List vendors |
| GET | `/api/menu/featured` | Featured items |
| GET | `/api/menu/search?q=` | Search menu |

### Protected Endpoints (Require Authentication)
| Method | Endpoint | Description | Module |
|--------|----------|-------------|--------|
| GET | `/api/auth/user` | Get current user | Ng Wayne Xiang |
| POST | `/api/auth/validate-token` | Validate token (Web Service) | Ng Wayne Xiang |
| GET | `/api/auth/user-stats` | User statistics (Web Service) | Ng Wayne Xiang |
| GET | `/api/menu/{id}/availability` | Item availability (Web Service) | Haerine Deepak Singh |
| GET | `/api/menu/popular` | Popular items (Web Service) | Haerine Deepak Singh |
| GET | `/api/orders` | List orders | Low Nam Lee |
| POST | `/api/orders` | Create order | Low Nam Lee |
| GET | `/api/orders/{id}/status` | Order status (Web Service) | Low Nam Lee |
| POST | `/api/orders/validate-pickup` | Validate pickup QR (Web Service) | Low Nam Lee |
| GET | `/api/cart` | Get cart items | Lee Song Yan |
| POST | `/api/cart` | Add to cart | Lee Song Yan |
| GET | `/api/cart/summary` | Cart summary (Web Service) | Lee Song Yan |
| GET | `/api/cart/validate` | Validate cart (Web Service) | Lee Song Yan |
| POST | `/api/notifications/send` | Send notification (Web Service) | Lee Song Yan |
| POST | `/api/vouchers/validate` | Validate voucher (Web Service) | Lee Kin Hang |
| GET | `/api/vendors/{id}/availability` | Vendor availability (Web Service) | Lee Kin Hang |

### Vendor Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/vendor/dashboard` | Vendor dashboard |
| GET | `/api/vendor/orders` | Vendor orders |
| PUT | `/api/vendor/orders/{id}/status` | Update order status |
| POST | `/api/vendor/menu` | Add menu item |

---

## Design Patterns Implementation

### Ng Wayne Xiang: Strategy Pattern (User & Authentication)
**Location**: `app/Patterns/Strategy/`
- `AuthStrategyInterface.php` - Strategy interface
- `PasswordAuthStrategy.php` - Email/password authentication
- `TokenAuthStrategy.php` - API token authentication
- `AuthContext.php` - Context that uses strategies

### Haerine Deepak Singh: Repository Pattern (Menu & Catalog)
**Location**: `app/Patterns/Repository/`
- `MenuItemRepositoryInterface.php` - Repository interface
- `EloquentMenuItemRepository.php` - Eloquent implementation

### Low Nam Lee: State Pattern (Order & Pickup)
**Location**: `app/Patterns/State/`
- `OrderStateInterface.php` - State interface
- `PendingState.php`, `ConfirmedState.php`, `PreparingState.php`, `ReadyState.php`
- `OrderStateManager.php` - Manages state transitions

### Lee Song Yan: Observer Pattern (Cart, Checkout & Notifications)
**Location**: `app/Patterns/Observer/`
- `SubjectInterface.php`, `ObserverInterface.php`
- `OrderSubject.php` - Subject for order events
- `NotificationObserver.php` - Concrete observer for notifications

### Lee Kin Hang: Factory Pattern (Vendor Management)
**Location**: `app/Patterns/Factory/`
- `VoucherFactory.php` - Creates voucher discount calculators
- `VoucherInterface.php` - Voucher interface
- `FixedVoucher.php`, `PercentageVoucher.php` - Concrete voucher types

---

## Security Implementation

| Student | Module | Threat 1 | Practice 1 | Threat 2 | Practice 2 | Threat 3 | Practice 3 |
|---------|--------|----------|------------|----------|------------|----------|------------|
| 1 | User & Auth | Brute Force Attack | Rate Limiting [OWASP 41, 94] | Session Hijacking | Session Regeneration [OWASP 66-67] | Weak Password | Password Complexity [OWASP 38-39] |
| 2 | Menu & Catalog | SQL Injection | Parameterized Queries [OWASP 167] | XSS Attack | Output Encoding [OWASP 19-20] | Path Traversal | Input Path Validation [OWASP 35] |
| 3 | Order & Pickup | IDOR | Authorization Checks [OWASP 86] | QR Code Tampering | Digital Signatures [OWASP 104] | Race Condition | Database Transactions [OWASP 89] |
| 4 | Cart & Notifications | Price Manipulation | Server-side Price Validation [OWASP 1] | CSRF Attack | CSRF Protection [OWASP 73] | Replay Attack | Idempotency Keys [OWASP 64] |
| 5 | Vendor Management | Malicious File Upload | File Header Validation [OWASP 104, 143] | Information Disclosure | Generic Error Messages [OWASP 107-130] | Unauthorized Access | Role-based Access Control [OWASP 119] |

> **Note**: Input validation is compulsory for ALL modules but does not count toward the 3 required practices.

---

## Project Structure

```
FoodHunter/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/       # API Controllers
│   │   ├── Middleware/            # Custom middleware
│   │   └── Requests/              # Form Request validation
│   ├── Models/                    # Eloquent models
│   ├── Patterns/                  # Design patterns
│   │   ├── Strategy/              # Ng Wayne Xiang - User & Auth
│   │   ├── Repository/            # Haerine Deepak Singh - Menu & Catalog
│   │   ├── State/                 # Low Nam Lee - Order & Pickup
│   │   ├── Observer/              # Lee Song Yan - Cart & Notifications
│   │   └── Factory/               # Lee Kin Hang - Vendor Management
│   └── Services/                  # Business logic services
├── database/
│   ├── migrations/                # Database migrations
│   └── seeders/                   # Sample data seeders
├── docs/
│   └── MODULE_ALLOCATION.md       # Detailed module docs
└── routes/
    └── api.php                    # API routes
```

---

## Database Schema

Total: **17 tables**

### Table Ownership Matrix

| Table | Owner | Shared With | Description |
|-------|-------|-------------|-------------|
| users | S1 | S2, S3, S4, S5 | All modules need user context |
| sessions | S1 | - | Session management |
| email_verifications | S1 | - | OTP verification |
| password_reset_tokens | S1 | - | Password reset |
| categories | S2 | - | Menu categories |
| menu_items | S2 | S3, S4, S5 | Core product entity |
| wishlists | S2 | - | User favorites |
| orders | S3 | S4, S5 | Order processing |
| order_items | S3 | - | Order line items |
| pickups | S3 | - | Pickup queue |
| cart_items | S4 | - | Shopping cart |
| payments | S4 | S3 | Payment processing |
| notifications | S4 | - | In-app notifications |
| vendors | S5 | S1, S2, S3 | Vendor profiles |
| vendor_hours | S5 | - | Operating hours |
| vouchers | S5 | S4 | Discount vouchers |
| user_vouchers | S5 | S4 | Redeemed vouchers |

### ERD Summary

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

See `docs/MODULE_ALLOCATION.md` for complete documentation.

---

## License

This project is for educational purposes at TARUMT.
