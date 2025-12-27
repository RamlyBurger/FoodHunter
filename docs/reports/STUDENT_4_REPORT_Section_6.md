## 6. Web Services

### 6.1 Web Service Exposed #1: Cart Summary API

#### 6.1.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Returns the current user's cart summary including item count, subtotal, service fee, and total. Used by Menu module to show cart status in navigation bar and for calculating checkout totals. |
| Source Module | Cart, Checkout & Notifications (Lee Song Yan) |
| Target Module | Menu & Catalog (Haerine Deepak Singh), Order & Pickup (Low Nam Lee) |
| URL | http://127.0.0.1:8000/api/cart/summary |
| Function Name | summary() |
| HTTP Method | GET |
| Authentication | Bearer Token Required |
| Design Pattern | Observer Pattern |

#### 6.1.2 Request Parameters

| Field Name | Field Type | Mandatory/Optional | Description | Format | Example |
|------------|------------|-------------------|-------------|--------|---------|
| Authorization | String | Mandatory | Bearer token | Header | Bearer 1\|abc123xyz789 |

#### 6.1.3 Example Request

```http
GET /api/cart/summary HTTP/1.1
Host: 127.0.0.1:8000
Authorization: Bearer 1|abc123xyz789...
Content-Type: application/json
Accept: application/json
```

#### 6.1.4 Response Parameters (Success)

| Field Name | Field Type | Mandatory/Optional | Description | Format | Example |
|------------|------------|-------------------|-------------|--------|---------|
| success | Boolean | Mandatory | Request success status | true/false | true |
| status | Integer | Mandatory | HTTP status code | 200 | 200 |
| message | String | Mandatory | Response message | Text | "Success" |
| request_id | String | Mandatory | Unique request identifier | UUID | a1b2c3d4-e5f6-7890 |
| timestamp | String | Mandatory | Response timestamp | ISO 8601 | 2025-12-22T13:30:00+08:00 |
| data.item_count | Integer | Mandatory | Total items in cart | Numeric | 3 |
| data.subtotal | Float | Mandatory | Cart subtotal before fees | Decimal | 25.50 |
| data.service_fee | Float | Mandatory | Platform service fee | Decimal | 2.00 |
| data.discount | Float | Mandatory | Applied voucher discount | Decimal | 5.00 |
| data.total | Float | Mandatory | Grand total after fees/discount | Decimal | 22.50 |

#### 6.1.5 Example Response (Success - 200 OK)

```json
{
    "success": true,
    "status": 200,
    "message": "Success",
    "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "timestamp": "2025-12-22T13:30:00+08:00",
    "data": {
        "item_count": 3,
        "subtotal": 25.50,
        "service_fee": 2.00,
        "discount": 0.00,
        "total": 27.50
    }
}
```

#### 6.1.6 Example Response (Error - 401 Unauthorized)

```json
{
    "success": false,
    "status": 401,
    "message": "Unauthenticated",
    "error": "UNAUTHORIZED",
    "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "timestamp": "2025-12-22T13:30:00+08:00"
}
```

#### 6.1.7 Frontend Consumption Example

```javascript
// resources/views/cart/index.blade.php (line 262-270)
// Updates cart summary display when cart items are modified

function updateCartSummary(summary) {
    const itemCountEl = document.querySelector('[data-summary="item-count"]');
    const subtotalEl = document.querySelector('[data-summary="subtotal"]');
    const totalEl = document.querySelector('[data-summary="total"]');
    
    if (itemCountEl) itemCountEl.textContent = summary.item_count;
    if (subtotalEl) subtotalEl.textContent = 'RM ' + summary.subtotal.toFixed(2);
    if (totalEl) totalEl.textContent = 'RM ' + summary.total.toFixed(2);
}

// Called after quantity update (line 204):
// updateCartSummary(responseData.summary || data.summary);
```

#### 6.1.8 Modules That Consume This API

| Module | Student | Usage Context |
|--------|---------|---------------|
| Menu & Catalog | Haerine Deepak Singh | Display cart badge in navigation |
| Order & Pickup | Low Nam Lee | Calculate order totals from cart |
| Checkout Page | Frontend | Display payment summary |

---

### 6.2 Web Service Exposed #2: Send Notification API

#### 6.2.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Sends notifications to users for various events (order updates, promotions, welcome messages). Used by all modules to communicate with users via in-app notifications. Implements Observer Pattern for event-driven notification dispatch. |
| Source Module | Cart, Checkout & Notifications (Lee Song Yan) |
| Target Module | User & Auth (Ng Wayne Xiang), Order & Pickup (Low Nam Lee), Vendor Management (Lee Kin Hang) |
| URL | http://127.0.0.1:8000/api/notifications/send |
| Function Name | send() |
| HTTP Method | POST |
| Authentication | Bearer Token Required |

#### 6.2.2 Request Parameters

| Field Name | Field Type | Mandatory/Optional | Description | Format | Example |
|------------|------------|-------------------|-------------|--------|---------|
| Authorization | String | Mandatory | Bearer token | Header | Bearer 1\|abc123xyz789 |
| user_id | Integer | Mandatory | Target user ID | Numeric | 1 |
| type | String | Mandatory | Notification type | welcome/order/promo/system | order |
| title | String | Mandatory | Notification title | Text (max 100) | Order Ready! |
| message | String | Mandatory | Notification body | Text (max 500) | Your order #FH-123 is ready |
| data | Object | Optional | Additional metadata | JSON object | {"order_id": 123} |

#### 6.2.3 Example Request

```http
POST /api/notifications/send HTTP/1.1
Host: 127.0.0.1:8000
Authorization: Bearer 1|abc123xyz789...
Content-Type: application/json
Accept: application/json

{
    "user_id": 1,
    "type": "order",
    "title": "Order Ready for Pickup!",
    "message": "Your order #FH-20251222-A1B2 is ready. Please collect at Counter 3.",
    "data": {"order_id": 123, "queue_number": 105}
}
```

#### 6.2.4 Response Parameters (Success)

| Field Name | Field Type | Mandatory/Optional | Description | Format | Example |
|------------|------------|-------------------|-------------|--------|---------|
| success | Boolean | Mandatory | Request success status | true/false | true |
| status | Integer | Mandatory | HTTP status code | 201 | 201 |
| message | String | Mandatory | Response message | Text | "Notification sent successfully" |
| request_id | String | Mandatory | Unique request identifier | UUID | a1b2c3d4-e5f6-7890 |
| timestamp | String | Mandatory | Response timestamp | ISO 8601 | 2025-12-22T13:30:00+08:00 |
| data.notification_id | Integer | Mandatory | Created notification ID | Numeric | 1 |
| data.type | String | Mandatory | Notification type | Text | order |
| data.title | String | Mandatory | Notification title | Text | Order Ready! |
| data.created_at | String | Mandatory | Creation timestamp | ISO 8601 | 2025-12-22T13:30:00+08:00 |

#### 6.2.5 Example Response (Success - 201 Created)

```json
{
    "success": true,
    "status": 201,
    "message": "Notification sent successfully",
    "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "timestamp": "2025-12-22T13:30:00+08:00",
    "data": {
        "notification_id": 1,
        "type": "order",
        "title": "Order Ready for Pickup!",
        "created_at": "2025-12-22T13:30:00+08:00"
    }
}
```

#### 6.2.6 Example Response (Error - User Not Found)

```json
{
    "success": false,
    "status": 404,
    "message": "User not found",
    "error": "USER_NOT_FOUND",
    "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "timestamp": "2025-12-22T13:30:00+08:00"
}
```

#### 6.2.7 Example Response (Error - Validation Failed)

```json
{
    "success": false,
    "status": 422,
    "message": "Validation failed",
    "error": "VALIDATION_ERROR",
    "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "timestamp": "2025-12-22T13:30:00+08:00",
    "errors": {
        "title": ["The title field is required."],
        "message": ["The message field is required."]
    }
}
```

#### 6.2.8 Backend Consumption Example

```php
// app/Http/Controllers/Api/AuthController.php (line 41-48)
// Ng Wayne Xiang consumes Send Notification API to send welcome notifications after registration

public function register(RegisterRequest $request): JsonResponse
{
    $user = $this->authService->register($request->validated());
    $token = $user->createToken('auth-token')->plainTextToken;

    // Web Service: Consume Notification Service (Lee Song Yan)
    $this->notificationService->send(
        $user->id,
        'welcome',
        'Welcome to FoodHunter!',
        'Thank you for joining FoodHunter. Start exploring delicious food now!',
        ['registration_date' => now()->toDateString()]
    );

    return $this->createdResponse([
        'user' => $this->formatUser($user),
        'token' => $token,
    ], 'Registration successful');
}
```

#### 6.2.9 Modules That Consume This API

| Module | Student | Usage Context |
|--------|---------|---------------|
| User & Authentication | Ng Wayne Xiang | Send welcome notification on registration |
| Order & Pickup | Low Nam Lee | Send order status update notifications |
| Vendor Management | Lee Kin Hang | Send new order notifications to vendors |

---

### 6.3 Web Service Consumed: Voucher Validation API (Lee Kin Hang)

#### 6.3.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Validates voucher codes during checkout to apply discounts. Consumed when user applies a voucher code before payment processing. |
| Source Module | Vendor Management (Lee Kin Hang) |
| Target Module | Cart, Checkout & Notifications (Lee Song Yan) |
| URL | http://127.0.0.1:8000/api/vouchers/validate |
| Function Name | validate() |
| HTTP Method | POST |

#### 6.3.2 Request Parameters

| Field Name | Field Type | Mandatory/Optional | Description | Format | Example |
|------------|------------|-------------------|-------------|--------|---------|
| Authorization | String | Mandatory | Bearer token | Header | Bearer 1\|abc123xyz789 |
| code | String | Mandatory | Voucher code | Uppercase string | SUMMER20 |
| subtotal | Decimal | Mandatory | Order subtotal | Numeric | 50.00 |

#### 6.3.3 Example Request Sent

```http
POST /api/vouchers/validate HTTP/1.1
Host: 127.0.0.1:8000
Authorization: Bearer 1|abc123xyz789...
Content-Type: application/json

{
    "code": "SUMMER20",
    "subtotal": 50.00
}
```

#### 6.3.5 Expected Response

```json
{
    "success": true,
    "status": 200,
    "message": "Voucher is valid",
    "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "timestamp": "2025-12-22T14:30:00+08:00",
    "data": {
        "voucher_id": 5,
        "code": "SUMMER20",
        "type": "percentage",
        "value": 20,
        "discount": 10.00,
        "description": "20% off (max RM15.00)",
        "min_order": 30.00,
        "expires_at": "2025-12-31T23:59:59+08:00"
    }
}
```

---

### 6.4 API Route Configuration

```php
// routes/api.php

Route::middleware('auth:sanctum')->group(function () {
    // Cart (Lee Song Yan - Cart, Checkout & Notifications Module)
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'add']);
    Route::put('/cart/{cartItem}', [CartController::class, 'update']);
    Route::delete('/cart/{cartItem}', [CartController::class, 'remove']);
    Route::delete('/cart', [CartController::class, 'clear']);
    Route::get('/cart/count', [CartController::class, 'count']);
    
    // Web Service: Cart Summary (Lee Song Yan exposes, Haerine Deepak Singh consumes)
    Route::get('/cart/summary', [CartController::class, 'summary']);
    
    // Notifications (Lee Song Yan)
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    
    // Web Service: Send Notification (Lee Song Yan exposes, all modules consume)
    Route::post('/notifications/send', [NotificationController::class, 'send']);
    
});
```

---

### 6.5 Complete API Endpoints Summary

The following API endpoints are implemented in the Cart, Checkout & Notifications module:

| Method | Endpoint | Function | Type | Description |
|--------|----------|----------|------|-------------|
| GET | /api/cart | index() | Protected | Get cart items with full details |
| POST | /api/cart | add() | Protected | Add item to cart |
| PUT | /api/cart/{cartItem} | update() | Protected | Update cart item quantity |
| DELETE | /api/cart/{cartItem} | remove() | Protected | Remove item from cart |
| DELETE | /api/cart | clear() | Protected | Clear entire cart |
| GET | /api/cart/count | count() | Protected | Get cart item count |
| GET | /api/cart/summary | summary() | **EXPOSED** | Get cart summary totals |
| GET | /api/cart/validate | validateCart() | **EXPOSED** | Validate cart before checkout |
| GET | /api/cart/recommendations | recommendations() | Protected | Get item recommendations |
| GET | /api/notifications | index() | Protected | List user's notifications |
| POST | /api/notifications/send | send() | **EXPOSED** | Send notification to user |
| POST | /api/notifications/{id}/read | markAsRead() | Protected | Mark notification as read |

---

### 6.6 Security Features

| Feature | Implementation | OWASP Reference |
|---------|---------------|-----------------|
| Price Manipulation | Server-side price validation (re-fetch from DB) | OWASP [1] |
| CSRF Attack | CSRF token protection on all forms | OWASP [73] |
| Authentication | Sanctum token-based auth required | OWASP [66-67] |
| Authorization | User can only access own cart/notifications | OWASP [4] |

---

### 6.7 Implementation Files

| File | Purpose |
|------|---------|
| `app/Http/Controllers/Api/CartController.php` | Cart API controller with exposed web services |
| `app/Http/Controllers/Api/NotificationController.php` | Notification API controller |
| `app/Http/Controllers/Web/CartController.php` | Web controller for checkout process |
| `app/Models/CartItem.php` | Cart item Eloquent model |
| `app/Models/Notification.php` | Notification Eloquent model |
| `app/Services/NotificationService.php` | Notification business logic (Observer Pattern) |
| `app/Patterns/Observer/NotificationObserver.php` | Observer Pattern implementation |
| `app/Traits/ApiResponse.php` | Standardized API response format |
