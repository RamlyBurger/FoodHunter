# FoodHunter Development Commands

## Quick Start
```powershell
# Start the development server
php artisan serve

# Start on specific port
php artisan serve --port=8000
```

## Database Commands
```powershell
# Reset database (drop all tables, migrate, seed)
php artisan migrate:fresh --seed

# Just run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Seed database only
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=UserSeeder
```

## Cache & Config
```powershell
# Clear all caches
php artisan optimize:clear

# Clear specific caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Rebuild cache (production)
php artisan optimize
```

## Routes
```powershell
# List all routes
php artisan route:list

# List routes by path
php artisan route:list --path=api
php artisan route:list --path=vendor
```

## Debugging
```powershell
# Show application info
php artisan about

# Check for issues
php artisan config:show app
```

## Test Accounts (after seeding)

| Role | Email | Password |
|------|-------|----------|
| Customer | john@example.com | password123 |
| Customer | jane@example.com | password123 |
| Vendor (Mak Cik Kitchen) | lownl-jm22@student.tarc.edu.my | password123 |
| Vendor (Western Delight) | western@foodhunter.com | password123 |
| Vendor (Bubble Tea Corner) | bubble@foodhunter.com | password123 |

## Common URLs
- Home: http://localhost:8000
- Login: http://localhost:8000/login
- Menu: http://localhost:8000/menu
- Cart: http://localhost:8000/cart
- Orders: http://localhost:8000/orders
- Wishlist: http://localhost:8000/wishlist
- Rewards: http://localhost:8000/rewards
- Contact: http://localhost:8000/contact
- Vendor Dashboard: http://localhost:8000/vendor/dashboard
- Vendor Reports: http://localhost:8000/vendor/reports
- **API Tester**: http://localhost:8000/api-tester.html ⭐

## One-Liner Reset & Start
```powershell
php artisan migrate:fresh --seed; php artisan serve
```

---

## REST API Documentation

Base URL: `http://127.0.0.1:8000/api`

### Authentication APIs (Ng Wayne Xiang)

#### POST /api/auth/login - User Login
Authenticates user credentials and returns an access token.

**Request:**
```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response (200 OK):**
```json
{
    "success": true,
    "status": 200,
    "message": "Login successful",
    "data": {
        "token": "1|laravel_sanctum_abc123xyz789...",
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "role": "customer"
        }
    }
}
```

#### POST /api/auth/register - User Registration
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "+60123456789"
}
```

#### POST /api/auth/validate-token - Token Validation (Exposed Web Service)
Validates an API token. Used by other modules to verify authentication.

**Headers:** `Authorization: Bearer {token}`

**Response (200 OK):**
```json
{
    "success": true,
    "status": 200,
    "data": {
        "valid": true,
        "user_id": 1,
        "email": "john@example.com",
        "role": "customer"
    }
}
```

---

### Menu & Catalog APIs (Haerine Deepak Singh)

#### GET /api/menu/search?q={query} - Menu Search
Search menu items by name/description.

**Response (200 OK):**
```json
{
    "success": true,
    "status": 200,
    "data": [
        {
            "id": 5,
            "name": "Nasi Lemak Special",
            "price": 8.50,
            "image": "/images/menu/nasi-lemak.jpg",
            "is_available": true,
            "vendor": {
                "id": 1,
                "store_name": "Warung Makcik"
            }
        }
    ]
}
```

#### GET /api/menu/{menuItem}/availability - Item Availability (Exposed Web Service)
Checks if a menu item is available. Consumed by Cart module.

**Response (200 OK):**
```json
{
    "success": true,
    "status": 200,
    "data": {
        "item_id": 5,
        "name": "Nasi Lemak Special",
        "available": true,
        "is_available": true,
        "price": 8.50
    }
}
```

#### GET /api/menu/{menuItem}/related - Related Items (NEW)
Get related/similar menu items based on category or vendor. Useful for product recommendations.

**Response (200 OK):**
```json
{
    "success": true,
    "data": [
        {
            "id": 6,
            "name": "Nasi Goreng Kampung",
            "price": 7.50,
            "image": "/images/nasi-goreng.jpg",
            "category": {
                "id": 1,
                "name": "Malaysian"
            },
            "vendor": {
                "id": 2,
                "store_name": "Mak Cik Kitchen"
            }
        }
    ]
}
```

---

### Order & Pickup APIs (Low Nam Lee)

#### GET /api/orders - List Orders
**Headers:** `Authorization: Bearer {token}`

#### POST /api/orders - Create Order
**Headers:** `Authorization: Bearer {token}`
```json
{
    "payment_method": "online",
    "voucher_code": "SUMMER20",
    "notes": "Extra spicy please"
}
```

#### GET /api/orders/{order}/status - Order Status (Exposed Web Service)
Returns real-time order status and pickup info. Consumed by Notification module.

**Response (200 OK):**
```json
{
    "success": true,
    "status": 200,
    "data": {
        "order_id": 123,
        "order_number": "FH-20251222-A1B2",
        "status": "preparing",
        "total": 25.50,
        "pickup": {
            "queue_number": 105,
            "status": "waiting"
        },
        "updated_at": "2025-12-22T13:30:00+08:00"
    }
}
```

#### POST /api/orders/{order}/reorder - Reorder (NEW)
Quickly reorder items from a past order. Clears cart and adds all available items from the order.

**Headers:** `Authorization: Bearer {token}`

**Response (200 OK):**
```json
{
    "success": true,
    "data": {
        "message": "Added 3 items to cart",
        "items_added": 3,
        "items_unavailable": 0
    }
}
```

---

### Cart & Checkout APIs (Lee Song Yan)

#### GET /api/cart - Get Cart Items
**Headers:** `Authorization: Bearer {token}`

#### POST /api/cart - Add to Cart
```json
{
    "menu_item_id": 5,
    "quantity": 2,
    "special_instructions": "No onions"
}
```

#### PUT /api/cart/{cartItem} - Update Cart Item
```json
{
    "quantity": 3,
    "special_instructions": "Extra sauce"
}
```

#### DELETE /api/cart/{cartItem} - Remove from Cart

#### DELETE /api/cart - Clear Cart

#### GET /api/cart/summary - Cart Summary

#### GET /api/cart/count - Cart Item Count (NEW)
Get the total number of items in cart. Useful for shopping cart badge displays.

**Headers:** `Authorization: Bearer {token}`

**Response (200 OK):**
```json
{
    "success": true,
    "data": {
        "count": 5
    }
}
```

---

### Notification APIs (Lee Song Yan)

#### GET /api/notifications - List Notifications
**Headers:** `Authorization: Bearer {token}`

#### GET /api/notifications/unread-count - Unread Count

#### POST /api/notifications/{id}/read - Mark as Read

#### POST /api/notifications/read-all - Mark All as Read

#### POST /api/notifications/send - Send Notification (Exposed Web Service)
Sends notification to a user. Consumed by Auth and Order modules.

**Request:**
```json
{
    "user_id": 5,
    "type": "welcome",
    "title": "Welcome to FoodHunter!",
    "message": "Your account has been created successfully.",
    "data": {"action": "profile"}
}
```

**Response (201 Created):**
```json
{
    "success": true,
    "status": 201,
    "message": "Notification sent successfully",
    "data": {
        "notification_id": 42,
        "sent_at": "2025-12-22T14:30:00+08:00"
    }
}
```

---

### Vendor Management APIs (Lee Kin Hang)

#### GET /api/vendor/dashboard - Dashboard Stats
**Headers:** `Authorization: Bearer {token}` (Vendor only)

#### GET /api/vendor/menu - List Menu Items

#### POST /api/vendor/menu - Create Menu Item
```json
{
    "name": "Nasi Goreng",
    "category_id": 1,
    "price": 12.50,
    "description": "Classic fried rice",
    "is_available": true
}
```

#### PUT /api/vendor/menu/{menuItem} - Update Menu Item

#### DELETE /api/vendor/menu/{menuItem} - Delete Menu Item

#### POST /api/vendor/menu/{menuItem}/toggle - Toggle Availability

---

### Voucher APIs (Lee Kin Hang)

#### GET /api/vendor/vouchers - List Vouchers

#### POST /api/vendor/vouchers - Create Voucher
```json
{
    "name": "Summer Sale",
    "code": "SUMMER20",
    "type": "percentage",
    "value": 20,
    "min_order": 30.00,
    "max_discount": 15.00,
    "expires_at": "2025-12-31T23:59:59"
}
```

#### PUT /api/vendor/vouchers/{voucher} - Update Voucher

#### DELETE /api/vendor/vouchers/{voucher} - Delete Voucher

#### POST /api/vouchers/validate - Validate Voucher (Exposed Web Service)
Validates a voucher code and returns discount info. Consumed by Cart module.

**Request:**
```json
{
    "code": "MAKC10OFF",
    "subtotal": 50.00
}
```

**Response (200 OK):**
```json
{
    "success": true,
    "status": 200,
    "message": "Voucher is valid",
    "data": {
        "voucher_id": 2,
        "code": "MAKC10OFF",
        "type": "percentage",
        "value": 10,
        "discount": 5.00,
        "description": "10% off (max RM15.00)",
        "min_order": 20.00,
        "expires_at": "2026-02-23T03:47:31+00:00"
    }
}
```

**Error Response (400 Bad Request):**
```json
{
    "success": false,
    "status": 400,
    "message": "Voucher not applicable",
    "error": "MIN_ORDER_NOT_MET",
    "data": {
        "min_order_required": 30.00,
        "current_subtotal": 20.00
    }
}
```

---

### Vendor Order APIs (Lee Kin Hang)

#### GET /api/vendor/orders - List Orders

#### GET /api/vendor/orders/pending - Pending Orders

#### GET /api/vendor/orders/{order} - Order Details

#### PUT /api/vendor/orders/{order}/status - Update Order Status
```json
{
    "status": "preparing"
}
```
Valid statuses: `confirmed`, `preparing`, `ready`, `completed`, `cancelled`

---

## Web Service Integration Summary

| Module | Exposes | Consumes |
|--------|---------|----------|
| Ng Wayne Xiang (Auth) | Login API, Token Validation API | Notification API |
| Haerine Deepak Singh (Menu) | Item Availability API, Search API | Token Validation API |
| Low Nam Lee (Order) | Order Status API | Item Availability API |
| Lee Song Yan (Cart & Notifications) | Send Notification API | Order Status API |
| Lee Kin Hang (Vendor) | Validate Voucher API | Notification API |

---

## Testing APIs with cURL

```powershell
# Login
curl -X POST http://127.0.0.1:8000/api/auth/login `
  -H "Content-Type: application/json" `
  -d '{"email":"customer@test.com","password":"password"}'

# Get Menu Items
curl http://127.0.0.1:8000/api/menu/search?q=nasi

# Check Item Availability
curl http://127.0.0.1:8000/api/menu/1/availability

# Add to Cart (with token)
curl -X POST http://127.0.0.1:8000/api/cart `
  -H "Authorization: Bearer YOUR_TOKEN" `
  -H "Content-Type: application/json" `
  -d '{"menu_item_id":1,"quantity":2}'

# Validate Voucher (with token)
curl -X POST http://127.0.0.1:8000/api/vouchers/validate `
  -H "Authorization: Bearer YOUR_TOKEN" `
  -H "Content-Type: application/json" `
  -d '{"code":"MAKC10OFF","subtotal":50.00}'
```

