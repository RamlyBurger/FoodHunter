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

| Student | Module | Design Pattern | Tables | Web Service |
|---------|--------|----------------|--------|-------------|
| 1 | User & Authentication | Strategy Pattern | users, password_reset_tokens, sessions | Exposes: Token Validation |
| 2 | Menu & Catalog | Repository Pattern | categories, menu_items, wishlists | Exposes: Item Availability |
| 3 | Cart & Checkout | Builder Pattern | cart_items, payments | Exposes: Cart Summary |
| 4 | Order & Pickup | State Pattern | orders, order_items, pickups | Exposes: Order Status |
| 5 | Rewards & Notifications | Observer Pattern | rewards, user_points, user_vouchers, notifications, point_transactions | Exposes: Send Notification |

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
   cp .env.example .env
   php artisan key:generate
   ```

4. **Update `.env` with database credentials**
   ```
   DB_DATABASE=foodhunter
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```

5. **Create database**
   ```sql
   CREATE DATABASE foodhunter CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

6. **Run migrations and seeders**
   ```bash
   php artisan migrate:fresh --seed
   ```

7. **Start the development server**
   ```bash
   php artisan serve
   ```

---

## Test Credentials

| Role | Email | Password |
|------|-------|----------|
| Customer | john@example.com | password123 |
| Customer | jane@example.com | password123 |
| Vendor | makcik@foodhunter.com | password123 |
| Vendor | western@foodhunter.com | password123 |
| Admin | admin@foodhunter.com | admin123 |

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
| GET | `/api/auth/user` | Get current user | Student 1 |
| POST | `/api/auth/validate-token` | Validate token (Web Service) | Student 1 |
| GET | `/api/cart` | Get cart items | Student 3 |
| POST | `/api/cart` | Add to cart | Student 3 |
| GET | `/api/cart/summary` | Cart summary (Web Service) | Student 3 |
| GET | `/api/orders` | List orders | Student 4 |
| POST | `/api/orders` | Create order | Student 4 |
| GET | `/api/orders/{id}/status` | Order status (Web Service) | Student 4 |
| GET | `/api/rewards` | List rewards | Student 5 |
| POST | `/api/rewards/{id}/redeem` | Redeem reward | Student 5 |
| POST | `/api/notifications/send` | Send notification (Web Service) | Student 5 |

### Vendor Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/vendor/dashboard` | Vendor dashboard |
| GET | `/api/vendor/orders` | Vendor orders |
| PUT | `/api/vendor/orders/{id}/status` | Update order status |
| POST | `/api/vendor/menu` | Add menu item |

---

## Design Patterns Implementation

### Student 1: Strategy Pattern
**Location**: `app/Patterns/Strategy/`
- `AuthStrategyInterface.php` - Strategy interface
- `PasswordAuthStrategy.php` - Email/password authentication
- `TokenAuthStrategy.php` - API token authentication
- `AuthContext.php` - Context that uses strategies

### Student 2: Repository Pattern
**Location**: `app/Patterns/Repository/`
- `MenuItemRepositoryInterface.php` - Repository interface
- `EloquentMenuItemRepository.php` - Eloquent implementation

### Student 3: Builder Pattern
**Location**: `app/Patterns/Builder/`
- `OrderBuilder.php` - Builds complex Order objects step-by-step

### Student 4: State Pattern
**Location**: `app/Patterns/State/`
- `OrderStateInterface.php` - State interface
- `PendingState.php`, `ConfirmedState.php`, `PreparingState.php`, `ReadyState.php`
- `OrderStateManager.php` - Manages state transitions

### Student 5: Observer Pattern
**Location**: `app/Patterns/Observer/`
- `SubjectInterface.php`, `ObserverInterface.php`
- `OrderSubject.php` - Subject for order events
- `NotificationObserver.php`, `PointsObserver.php` - Concrete observers

---

## Security Implementation

| Student | Threat 1 | Practice 1 | Threat 2 | Practice 2 |
|---------|----------|------------|----------|------------|
| 1 | Brute Force | Rate Limiting | Session Hijacking | Session Regeneration |
| 2 | SQL Injection | Parameterized Queries (ORM) | XSS | Output Encoding |
| 3 | Price Manipulation | Server-side Validation | CSRF | CSRF Tokens |
| 4 | IDOR | Authorization Checks | QR Forgery | Digital Signatures |
| 5 | Code Guessing | Crypto Random Codes | Points Fraud | Audit Logging |

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
│   │   ├── Strategy/              # Student 1
│   │   ├── Repository/            # Student 2
│   │   ├── Builder/               # Student 3
│   │   ├── State/                 # Student 4
│   │   └── Observer/              # Student 5
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

Total: **18 tables**

See `docs/MODULE_ALLOCATION.md` for complete ERD and table ownership.

---

## License

This project is for educational purposes at TARUMT.
