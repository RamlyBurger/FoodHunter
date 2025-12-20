# FoodHunter REST API Documentation

## Overview

This document provides comprehensive documentation for the FoodHunter REST API. The API allows you to build mobile applications or integrate with the FoodHunter canteen ordering system.

**Base URL:** `http://localhost/foodhunter/api`

## Authentication

The API uses **Laravel Sanctum** for token-based authentication.

### Getting a Token

1. Register or login to get an authentication token
2. Include the token in the `Authorization` header for protected routes

```
Authorization: Bearer {your_token}
```

### Content Type

All requests should include:
```
Content-Type: application/json
Accept: application/json
```

---

## API Endpoints

### 🔐 Authentication

#### Register

```http
POST /api/auth/register
```

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "student"
}
```

**Response (201):**
```json
{
    "success": true,
    "message": "Registration successful",
    "data": {
        "user": {
            "user_id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "role": "student"
        },
        "token": "1|abc123...",
        "token_type": "Bearer"
    }
}
```

#### Login

```http
POST /api/auth/login
```

**Request Body:**
```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "user_id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "role": "student",
            "phone": null
        },
        "token": "2|xyz789...",
        "token_type": "Bearer"
    }
}
```

#### Logout (🔒 Auth Required)

```http
POST /api/auth/logout
```

**Response (200):**
```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

#### Get Current User (🔒 Auth Required)

```http
GET /api/auth/user
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "user_id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "role": "student",
        "phone": null,
        "created_at": "2025-01-01T00:00:00.000000Z",
        "loyalty_points": 150
    }
}
```

---

### 🍔 Menu

#### Get All Menu Items

```http
GET /api/menu
```

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| search | string | Search by name or description |
| category_id | integer | Filter by category |
| vendor_id | integer | Filter by vendor |
| min_price | decimal | Minimum price filter |
| max_price | decimal | Maximum price filter |
| sort | string | Sort by: `popular`, `price_low`, `price_high`, `newest`, `name` |
| per_page | integer | Items per page (default: 12) |

**Example Request:**
```
GET /api/menu?category_id=1&sort=price_low&per_page=10
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "items": [
            {
                "item_id": 1,
                "name": "Nasi Lemak",
                "description": "Traditional Malaysian dish",
                "price": 5.50,
                "image_url": "http://localhost/storage/menu/nasi-lemak.jpg",
                "is_available": true,
                "category": {
                    "category_id": 1,
                    "category_name": "Rice"
                },
                "vendor": {
                    "vendor_id": 2,
                    "name": "Nasi Lemak Stall"
                },
                "created_at": "2025-01-01T00:00:00.000000Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "last_page": 5,
            "per_page": 12,
            "total": 50
        }
    }
}
```

#### Get Menu Item Details

```http
GET /api/menu/{id}
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "item": {
            "item_id": 1,
            "name": "Nasi Lemak",
            "description": "Traditional Malaysian dish",
            "price": 5.50,
            "image_url": "http://localhost/storage/menu/nasi-lemak.jpg",
            "is_available": true,
            "category": {
                "category_id": 1,
                "category_name": "Rice"
            },
            "vendor": {
                "vendor_id": 2,
                "name": "Nasi Lemak Stall"
            }
        },
        "total_orders": 250,
        "in_wishlist": false,
        "related_items": [...],
        "vendor": {
            "vendor_id": 2,
            "name": "Nasi Lemak Stall",
            "email": "vendor@example.com",
            "store_name": "Nasi Lemak Stall",
            "phone": "+60123456789",
            "description": "Best Nasi Lemak in campus",
            "logo_url": "http://localhost/storage/vendor_logos/logo.png",
            "accepting_orders": true
        },
        "operating_hours": [
            {
                "day": "monday",
                "is_open": true,
                "opening_time": "08:00:00",
                "closing_time": "17:00:00"
            }
        ]
    }
}
```

#### Search Menu Items

```http
GET /api/menu/search?q={query}
```

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| q | string | Search query (min 2 characters) |

**Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "item_id": 1,
            "name": "Nasi Lemak",
            "description": "Traditional Malaysian dish",
            "price": 5.50,
            "image_url": "http://localhost/storage/menu/nasi-lemak.jpg",
            "is_available": true,
            "category": {...},
            "vendor": {...}
        }
    ]
}
```

#### Get Categories

```http
GET /api/menu/categories
```

**Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "category_id": 1,
            "category_name": "Rice",
            "items_count": 15
        },
        {
            "category_id": 2,
            "category_name": "Noodles",
            "items_count": 12
        }
    ]
}
```

#### Get Vendors

```http
GET /api/menu/vendors
```

**Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "vendor_id": 2,
            "name": "Nasi Lemak Stall",
            "store_name": "Nasi Lemak Stall",
            "logo_url": "http://localhost/storage/vendor_logos/logo.png",
            "accepting_orders": true,
            "items_count": 10
        }
    ]
}
```

#### Get Vendor Store

```http
GET /api/menu/vendors/{vendorId}
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "vendor": {
            "vendor_id": 2,
            "name": "Nasi Lemak Stall",
            "email": "vendor@example.com",
            "store_name": "Nasi Lemak Stall",
            "phone": "+60123456789",
            "description": "Best Nasi Lemak in campus",
            "logo_url": "http://localhost/storage/vendor_logos/logo.png",
            "accepting_orders": true,
            "payment_methods": ["cash", "online"]
        },
        "statistics": {
            "total_items": 10,
            "total_orders": 500
        },
        "operating_hours": [...],
        "categories": [...],
        "menu_items": [...],
        "pagination": {...}
    }
}
```

---

### 🛒 Cart (🔒 Auth Required)

#### Get Cart

```http
GET /api/cart
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "items": [
            {
                "cart_id": 1,
                "quantity": 2,
                "special_request": "No spicy",
                "item_total": 11.00,
                "menu_item": {
                    "item_id": 1,
                    "name": "Nasi Lemak",
                    "description": "Traditional Malaysian dish",
                    "price": 5.50,
                    "image_url": "http://localhost/storage/menu/nasi-lemak.jpg",
                    "is_available": true,
                    "category": {...},
                    "vendor": {...}
                }
            }
        ],
        "summary": {
            "item_count": 2,
            "subtotal": 11.00,
            "service_fee": 2.00,
            "discount": 0.00,
            "total": 13.00
        }
    }
}
```

#### Add to Cart

```http
POST /api/cart
```

**Request Body:**
```json
{
    "item_id": 1,
    "quantity": 2,
    "special_request": "No spicy"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Item added to cart successfully",
    "data": {
        "cart_id": 1,
        "cart_count": 2,
        "item_name": "Nasi Lemak"
    }
}
```

#### Update Cart Item

```http
PUT /api/cart/{cartId}
```

**Request Body:**
```json
{
    "quantity": 3,
    "special_request": "Extra sambal"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Cart updated successfully",
    "data": {
        "item_subtotal": 16.50,
        "summary": {
            "item_count": 3,
            "subtotal": 16.50,
            "service_fee": 2.00,
            "discount": 0.00,
            "total": 18.50
        }
    }
}
```

#### Remove Cart Item

```http
DELETE /api/cart/{cartId}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Item removed from cart",
    "data": {
        "summary": {...},
        "cart_empty": false
    }
}
```

#### Clear Cart

```http
DELETE /api/cart
```

**Response (200):**
```json
{
    "success": true,
    "message": "Cart cleared successfully"
}
```

#### Get Cart Count

```http
GET /api/cart/count
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "count": 5
    }
}
```

#### Apply Voucher

```http
POST /api/cart/voucher
```

**Request Body:**
```json
{
    "voucher_code": "FH-ABC12345"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Voucher applied successfully!",
    "data": {
        "voucher_code": "FH-ABC12345",
        "reward_name": "RM5 Off",
        "reward_type": "voucher",
        "discount": 5.00,
        "summary": {
            "subtotal": 20.00,
            "service_fee": 2.00,
            "discount": 5.00,
            "total": 17.00
        }
    }
}
```

---

### 📦 Orders (🔒 Auth Required)

#### Get Orders

```http
GET /api/orders
```

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| status | string | Filter by: `pending`, `accepted`, `preparing`, `ready`, `completed`, `cancelled`, `all` |
| date_range | string | Filter by: `7days`, `30days`, `3months`, `1year` |
| per_page | integer | Items per page (default: 10) |

**Response (200):**
```json
{
    "success": true,
    "data": {
        "orders": [
            {
                "order_id": 1,
                "total_price": 15.50,
                "status": "completed",
                "created_at": "2025-01-01T10:00:00.000000Z",
                "vendor": {
                    "vendor_id": 2,
                    "name": "Nasi Lemak Stall"
                },
                "items_count": 3
            }
        ],
        "pagination": {...}
    }
}
```

#### Get Order Details

```http
GET /api/orders/{orderId}
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "order_id": 1,
        "total_price": 15.50,
        "status": "completed",
        "created_at": "2025-01-01T10:00:00.000000Z",
        "vendor": {...},
        "items_count": 3,
        "items": [
            {
                "order_item_id": 1,
                "quantity": 2,
                "price_at_time": 5.50,
                "special_request": "No spicy",
                "subtotal": 11.00,
                "menu_item": {
                    "item_id": 1,
                    "name": "Nasi Lemak",
                    "image_url": "..."
                }
            }
        ],
        "payment": {
            "payment_id": 1,
            "amount": 15.50,
            "payment_method": "online",
            "status": "completed",
            "paid_at": "2025-01-01T10:00:00.000000Z"
        },
        "pickup": {
            "pickup_id": 1,
            "queue_number": 15,
            "status": "picked_up",
            "pickup_instructions": "Call when ready",
            "picked_up_at": "2025-01-01T10:30:00.000000Z"
        }
    }
}
```

#### Get Active Order

```http
GET /api/orders/active
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "order_id": 5,
        "total_price": 12.00,
        "status": "preparing",
        ...
    }
}
```

#### Create Order (Checkout)

```http
POST /api/orders
```

**Request Body:**
```json
{
    "payment_method": "online",
    "pickup_instructions": "Please call when ready",
    "voucher_code": "FH-ABC12345"
}
```

**Payment Methods:** `online`, `ewallet`, `cash`

**Response (201):**
```json
{
    "success": true,
    "message": "Order placed successfully",
    "data": {
        "orders": [
            {
                "order_id": 6,
                "total_price": 15.50,
                "status": "pending",
                ...
            }
        ],
        "points_earned": 15,
        "total_paid": 15.50
    }
}
```

#### Reorder

```http
POST /api/orders/{orderId}/reorder
```

**Response (200):**
```json
{
    "success": true,
    "message": "Items added to cart",
    "data": {
        "added_items": ["Nasi Lemak", "Teh Tarik"],
        "unavailable_items": [],
        "cart_count": 2
    }
}
```

#### Cancel Order

```http
POST /api/orders/{orderId}/cancel
```

**Response (200):**
```json
{
    "success": true,
    "message": "Order cancelled successfully"
}
```

---

### ❤️ Wishlist (🔒 Auth Required)

#### Get Wishlist

```http
GET /api/wishlist
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "items": [
            {
                "wishlist_id": 1,
                "added_at": "2025-01-01T00:00:00.000000Z",
                "menu_item": {
                    "item_id": 1,
                    "name": "Nasi Lemak",
                    "price": 5.50,
                    "image_url": "...",
                    "is_available": true,
                    "category": {...},
                    "vendor": {...}
                }
            }
        ],
        "total": 5
    }
}
```

#### Add to Wishlist

```http
POST /api/wishlist
```

**Request Body:**
```json
{
    "item_id": 1
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Added to wishlist",
    "data": {
        "wishlist_id": 1,
        "wishlist_count": 5
    }
}
```

#### Remove from Wishlist

```http
DELETE /api/wishlist/{wishlistId}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Removed from wishlist",
    "data": {
        "wishlist_count": 4
    }
}
```

#### Toggle Wishlist

```http
POST /api/wishlist/toggle
```

**Request Body:**
```json
{
    "item_id": 1
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Added to wishlist",
    "data": {
        "in_wishlist": true,
        "wishlist_count": 5
    }
}
```

#### Check Item in Wishlist

```http
GET /api/wishlist/check/{itemId}
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "in_wishlist": true
    }
}
```

#### Get Wishlist Count

```http
GET /api/wishlist/count
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "count": 5
    }
}
```

#### Clear Wishlist

```http
DELETE /api/wishlist
```

**Response (200):**
```json
{
    "success": true,
    "message": "Wishlist cleared successfully"
}
```

---

### 🔔 Notifications (🔒 Auth Required)

#### Get Notifications

```http
GET /api/notifications
```

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| per_page | integer | Items per page (default: 20) |

**Response (200):**
```json
{
    "success": true,
    "data": {
        "notifications": [
            {
                "notification_id": 1,
                "type": "order_ready",
                "title": "Order Ready!",
                "message": "Your order #5 is ready for pickup",
                "is_read": false,
                "read_at": null,
                "created_at": "2025-01-01T10:00:00.000000Z",
                "order": {
                    "order_id": 5,
                    "status": "ready"
                }
            }
        ],
        "unread_count": 3,
        "pagination": {...}
    }
}
```

#### Get Recent Notifications

```http
GET /api/notifications/recent?limit=10
```

#### Get Unread Count

```http
GET /api/notifications/unread-count
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "count": 3
    }
}
```

#### Mark as Read

```http
POST /api/notifications/{id}/read
```

**Response (200):**
```json
{
    "success": true,
    "message": "Notification marked as read"
}
```

#### Mark All as Read

```http
POST /api/notifications/read-all
```

**Response (200):**
```json
{
    "success": true,
    "message": "All notifications marked as read"
}
```

#### Delete Notification

```http
DELETE /api/notifications/{id}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Notification deleted successfully"
}
```

#### Clear All Notifications

```http
DELETE /api/notifications
```

**Response (200):**
```json
{
    "success": true,
    "message": "All notifications cleared"
}
```

---

### 👤 Profile (🔒 Auth Required)

#### Get Profile

```http
GET /api/profile
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "user": {
            "user_id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "+60123456789",
            "role": "student",
            "created_at": "2025-01-01T00:00:00.000000Z"
        },
        "statistics": {
            "total_orders": 25,
            "total_spent": 350.50,
            "total_favorites": 8,
            "loyalty_points": 150
        }
    }
}
```

#### Update Profile

```http
PUT /api/profile
```

**Request Body:**
```json
{
    "name": "John Updated",
    "email": "john.new@example.com",
    "phone": "+60198765432"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Profile updated successfully",
    "data": {
        "user": {...}
    }
}
```

#### Update Password

```http
PUT /api/profile/password
```

**Request Body:**
```json
{
    "current_password": "oldpassword",
    "new_password": "newpassword123",
    "new_password_confirmation": "newpassword123"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Password updated successfully"
}
```

#### Get Recent Orders

```http
GET /api/profile/orders?limit=5
```

#### Get Favorites

```http
GET /api/profile/favorites?limit=6
```

#### Get Loyalty Points

```http
GET /api/profile/points
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "current_points": 150,
        "redeemed_rewards": [...]
    }
}
```

#### Delete Account

```http
DELETE /api/profile
```

**Request Body:**
```json
{
    "password": "yourpassword"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Account deleted successfully"
}
```

---

### 🎁 Rewards (🔒 Auth Required)

#### Get Available Rewards

```http
GET /api/rewards
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "current_points": 150,
        "rewards": [
            {
                "reward_id": 1,
                "reward_name": "RM5 Off",
                "description": "Get RM5 off your next order",
                "points_required": 100,
                "reward_type": "voucher",
                "reward_value": 5.00,
                "min_spend": 15.00,
                "max_discount": null,
                "stock": 50,
                "is_active": true,
                "can_redeem": true
            }
        ]
    }
}
```

#### Get Reward Details

```http
GET /api/rewards/{id}
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "reward_id": 1,
        "reward_name": "RM5 Off",
        ...
        "can_redeem": true,
        "user_points": 150,
        "points_needed": 0
    }
}
```

#### Redeem Reward

```http
POST /api/rewards/{id}/redeem
```

**Response (200):**
```json
{
    "success": true,
    "message": "Reward redeemed successfully!",
    "data": {
        "voucher_code": "FH-ABC12345",
        "reward": {
            "reward_id": 1,
            "reward_name": "RM5 Off",
            "reward_type": "voucher",
            "reward_value": 5.00
        },
        "remaining_points": 50,
        "expires_in_days": 30
    }
}
```

#### Get Redeemed Rewards

```http
GET /api/rewards/redeemed
```

**Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "voucher_code": "FH-ABC12345",
            "is_used": false,
            "used_at": null,
            "redeemed_at": "2025-01-01T00:00:00.000000Z",
            "expires_at": "2025-01-31T00:00:00.000000Z",
            "is_expired": false,
            "is_valid": true,
            "reward": {...}
        }
    ]
}
```

#### Get User Points

```http
GET /api/rewards/points
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "current_points": 150,
        "unused_vouchers": 2
    }
}
```

---

## Error Responses

### Validation Error (422)

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password must be at least 8 characters."]
    }
}
```

### Unauthorized (401)

```json
{
    "message": "Unauthenticated."
}
```

### Not Found (404)

```json
{
    "success": false,
    "message": "Resource not found"
}
```

### Bad Request (400)

```json
{
    "success": false,
    "message": "Invalid operation"
}
```

### Server Error (500)

```json
{
    "success": false,
    "message": "An error occurred",
    "error": "Error details (debug mode only)"
}
```

---

## Rate Limiting

API requests are rate limited to prevent abuse:
- **60 requests per minute** for authenticated users
- **30 requests per minute** for unauthenticated users

---

## Notification Types

| Type | Description | For |
|------|-------------|-----|
| `new_order` | New order received | Vendor |
| `order_accepted` | Order has been accepted | Student |
| `order_preparing` | Order is being prepared | Student |
| `order_ready` | Order is ready for pickup | Student |
| `order_completed` | Order has been completed | Student |
| `order_cancelled` | Order has been cancelled | Student |

---

## Reward Types

| Type | Description |
|------|-------------|
| `voucher` | Fixed amount discount (e.g., RM5 off) |
| `percentage` | Percentage discount (e.g., 10% off) |
| `free_item` | Free item voucher |

---

## Order Status Flow

```
pending → accepted → preparing → ready → completed
    ↓         ↓
cancelled  cancelled (by vendor - rejected)
```

---

## Testing the API

You can test the API using tools like:
- **Postman** - Import collection
- **cURL** - Command line testing
- **Thunder Client** - VS Code extension

### Example cURL Commands

**Login:**
```bash
curl -X POST http://localhost/foodhunter/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'
```

**Get Menu (with token):**
```bash
curl -X GET http://localhost/foodhunter/api/menu \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

---

## Changelog

### Version 1.0.0 (December 2025)
- Initial API release
- Authentication with Sanctum
- Menu, Cart, Orders, Wishlist endpoints
- Notifications system
- Rewards and loyalty points
- Profile management
