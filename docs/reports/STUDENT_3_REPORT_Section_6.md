## 6. Web Services

### 6.1 Web Service Exposed #1: Order Status API

#### 6.1.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Returns real-time order status and pickup information. Used by Notification module to get order details for status update notifications and by frontend for live order tracking. Implements State Pattern for order status transitions. |
| Source Module | Order & Pickup (Student 3) |
| Target Module | Cart, Checkout & Notifications (Student 4), Frontend |
| URL | http://127.0.0.1:8000/api/orders/{order}/status |
| Function Name | status() |
| HTTP Method | GET |
| Authentication | Bearer Token Required |
| Design Pattern | State Pattern |

#### 6.1.2 Request Parameters

| Field Name | Field Type | Mandatory/Optional | Description | Format | Example |
|------------|------------|-------------------|-------------|--------|---------|
| Authorization | String | Mandatory | Bearer token | Header | Bearer 1\|abc123xyz789 |

#### 6.1.3 Example Request

```http
GET /api/orders/123/status HTTP/1.1
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
| data.order_id | Integer | Mandatory | Order ID | Numeric | 123 |
| data.order_number | String | Mandatory | Order reference number | Text | FH-20251222-A1B2 |
| data.status | String | Mandatory | Current order status | Text | preparing |
| data.total | Float | Mandatory | Order total | Decimal | 45.00 |
| data.pickup | Object | Optional | Pickup information | Object | {...} |
| data.updated_at | String | Mandatory | Last status update time | ISO 8601 | 2025-12-22T13:30:00+08:00 |

#### 6.1.5 Example Response (Success - 200 OK)

```json
{
    "success": true,
    "status": 200,
    "message": "Success",
    "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "timestamp": "2025-12-22T13:30:00+08:00",
    "data": {
        "order_id": 123,
        "order_number": "FH-20251222-A1B2",
        "status": "preparing",
        "total": 45.00,
        "pickup": {
            "queue_number": 105,
            "status": "pending"
        },
        "updated_at": "2025-12-22T13:30:00+08:00"
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
// resources/views/orders/show.blade.php (line 521-570)
// Real-time order status polling for live tracking

function checkOrderStatus() {
    // Only poll for active orders (not completed or cancelled)
    if (['completed', 'cancelled'].includes(currentStatus)) {
        return;
    }
    
    fetch('/api/orders/' + orderId + '/status', {
        headers: {
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + (document.querySelector('meta[name="api-token"]')?.content || '')
        }
    })
    .then(res => res.json())
    .then(response => {
        const data = response.data || response;
        if (data.status && data.status !== currentStatus) {
            // Status changed - update UI dynamically
            const statusBadge = document.querySelector('.order-status-badge');
            if (statusBadge) {
                statusBadge.innerHTML = `<i class="bi bi-${config.icon} me-1"></i>${data.status}`;
            }
            currentStatus = data.status;
            showToast('Order status updated to: ' + data.status, 'info');
        }
    });
}

// Start polling every 15 seconds for active orders (line 573-576)
statusCheckInterval = setInterval(checkOrderStatus, 15000);
```

#### 6.1.8 Modules That Consume This API

| Module | Student | Usage Context |
|--------|---------|---------------|
| Cart, Checkout & Notifications | Student 4 | Get order details for notification content |
| Vendor Management | Student 5 | Display order status to vendor |
| Frontend | - | Live order tracking for customers |

---

### 6.2 Web Service Exposed #2: Pickup QR Validation API

#### 6.2.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Validates a pickup QR code to verify order authenticity and mark order as collected. Used by vendors to confirm customer pickup and complete the order fulfillment process. Implements digital signature verification for QR code security. |
| Source Module | Order & Pickup (Student 3) |
| Target Module | Vendor Management (Student 5), Frontend |
| URL | http://127.0.0.1:8000/api/orders/validate-pickup |
| Function Name | validatePickupQr() |
| HTTP Method | POST |
| Authentication | Bearer Token Required |

#### 6.2.2 Request Parameters

| Field Name | Field Type | Mandatory/Optional | Description | Format | Example |
|------------|------------|-------------------|-------------|--------|---------|
| Authorization | String | Mandatory | Bearer token | Header | Bearer 1\|abc123xyz789 |
| qr_code | String | Mandatory | QR code data from customer's order | Text | FH-20251222-A1B2-HMAC123 |

#### 6.2.3 Example Request

```http
POST /api/orders/validate-pickup HTTP/1.1
Host: 127.0.0.1:8000
Authorization: Bearer 1|abc123xyz789...
Content-Type: application/json
Accept: application/json

{
    "qr_code": "FH-20251222-A1B2-HMAC123ABC456"
}
```

#### 6.2.4 Response Parameters (Success)

| Field Name | Field Type | Mandatory/Optional | Description | Format | Example |
|------------|------------|-------------------|-------------|--------|---------|
| success | Boolean | Mandatory | Request success status | true/false | true |
| status | Integer | Mandatory | HTTP status code | 200 | 200 |
| message | String | Mandatory | Response message | Text | "Pickup validated successfully" |
| request_id | String | Mandatory | Unique request identifier | UUID | a1b2c3d4-e5f6-7890 |
| timestamp | String | Mandatory | Response timestamp | ISO 8601 | 2025-12-22T13:30:00+08:00 |
| data.valid | Boolean | Mandatory | QR code validity | true/false | true |
| data.order_id | Integer | Mandatory | Order ID | Numeric | 123 |
| data.order_number | String | Mandatory | Order reference number | Text | FH-20251222-A1B2 |
| data.customer_name | String | Mandatory | Customer name | Text | John Doe |
| data.queue_number | Integer | Mandatory | Pickup queue number | Numeric | 105 |
| data.status | String | Mandatory | Updated order status | Text | completed |

#### 6.2.5 Example Response (Success - Valid QR)

```json
{
    "success": true,
    "status": 200,
    "message": "Pickup validated successfully",
    "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "timestamp": "2025-12-22T13:30:00+08:00",
    "data": {
        "valid": true,
        "order_id": 123,
        "order_number": "FH-20251222-A1B2",
        "customer_name": "John Doe",
        "queue_number": 105,
        "status": "completed",
        "collected_at": "2025-12-22T13:30:00+08:00"
    }
}
```

#### 6.2.6 Example Response (Error - Invalid QR)

```json
{
    "success": false,
    "status": 400,
    "message": "Invalid QR code",
    "error": "INVALID_QR_CODE",
    "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "timestamp": "2025-12-22T13:30:00+08:00"
}
```

#### 6.2.7 Example Response (Error - Order Not Ready)

```json
{
    "success": false,
    "status": 400,
    "message": "Order is not ready for pickup",
    "error": "ORDER_NOT_READY",
    "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "timestamp": "2025-12-22T13:30:00+08:00",
    "data": {
        "current_status": "preparing"
    }
}
```

#### 6.2.8 Frontend Consumption Example

```javascript
// resources/views/vendor/scan.blade.php (line 108-159)
// Vendor QR scanner page - validates pickup QR code using Student 3's API

// AJAX form submission - Uses Student 3's validatePickupQr API
form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validate QR using Student 3's API
    fetch('/api/orders/validate-pickup', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + (document.querySelector('meta[name="api-token"]')?.content || '')
        },
        body: JSON.stringify({ qr_code: input.value })
    })
    .then(res => res.json())
    .then(response => {
        const data = response.data || response;
        if (response.success && data.valid) {
            Swal.fire({
                icon: 'success',
                title: 'Order Found!',
                html: `<p><strong>Order:</strong> #${data.order_number}</p>
                       <p><strong>Customer:</strong> ${data.customer_name}</p>
                       <p><strong>Queue:</strong> #${data.queue_number}</p>`,
                confirmButtonText: 'Complete Pickup'
            }).then((result) => {
                if (result.isConfirmed) {
                    completePickupAjax(data.order_id);
                }
            });
        } else {
            showToast(data.message, 'error');
        }
    });
});
```

#### 6.2.9 Modules That Consume This API

| Module | Student | Usage Context |
|--------|---------|---------------|
| Vendor Management | Student 5 | Verify pickup and complete order |
| Frontend | - | Vendor dashboard QR scanner |

---

### 6.3 Web Service Consumed: Token Validation API (Student 1)

#### 6.3.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Validates user authentication token before processing order creation. Ensures the user session is still valid and prevents orders from expired sessions. |
| Source Module | User & Authentication (Student 1) |
| Target Module | Order & Pickup (Student 3) |
| URL | http://127.0.0.1:8000/api/auth/validate-token |
| Function Name | validateToken() |
| HTTP Method | POST |

#### 6.3.2 Implementation in Order Module

```php
// app/Http/Controllers/Api/OrderController.php (line 56-64)
// Consumes Student 1's Validate Token API before creating orders

public function store(CreateOrderRequest $request): JsonResponse
{
    $user = $request->user();
    
    // Web Service: Consume Student 1's Validate Token API to verify user authentication
    // This ensures the user session is still valid before processing the order
    $authController = app(\App\Http\Controllers\Api\AuthController::class);
    $tokenValidation = $authController->validateToken($request);
    $validationData = json_decode($tokenValidation->getContent(), true);
    
    if (!($validationData['data']['valid'] ?? false)) {
        return $this->unauthorizedResponse('Session expired. Please login again.');
    }
    
    // ... proceed with order creation
}
```

---

### 6.4 Web Service Consumed: Item Availability API (Student 2)

#### 6.4.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Validates menu items are available before creating an order. Consumed during order creation to ensure all items are in stock and vendors are open. |
| Source Module | Menu & Catalog (Student 2) |
| Target Module | Order & Pickup (Student 3) |
| URL | http://127.0.0.1:8000/api/menu/{menuItem}/availability |
| Function Name | checkAvailability() |
| HTTP Method | GET |

#### 6.4.2 Request Parameters

| Field Name | Field Type | Mandatory/Optional | Description | Format | Example |
|------------|------------|-------------------|-------------|--------|---------|
| menuItem | Integer | Mandatory | Menu item ID | Path parameter | 5 |

#### 6.4.3 Example Request Sent

```http
GET /api/menu/5/availability HTTP/1.1
Host: 127.0.0.1:8000
Content-Type: application/json
Accept: application/json
```

#### 6.4.4 Expected Response

```json
{
    "success": true,
    "status": 200,
    "message": "Success",
    "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "timestamp": "2025-12-22T13:30:00+08:00",
    "data": {
        "item_id": 5,
        "name": "Nasi Lemak Special",
        "available": true,
        "is_available": true,
        "price": 8.50
    }
}
```

---

### 6.4 API Route Configuration

```php
// routes/api.php

Route::middleware('auth:sanctum')->group(function () {
    // Orders (Student 3 - Order & Pickup Module)
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/active', [OrderController::class, 'active']);
    Route::get('/orders/history', [OrderController::class, 'history']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::post('/orders/{order}/reorder', [OrderController::class, 'reorder']);
    
    // Web Service: Order Status (Student 3 exposes, Student 4 consumes)
    Route::get('/orders/{order}/status', [OrderController::class, 'status']);
    
    // Web Service: Pickup QR Validation (Student 3 exposes, Vendor module consumes)
    Route::post('/orders/validate-pickup', [OrderController::class, 'validatePickupQr']);
    
    // Web Service: Cart Recommendations (Student 3 consumes Student 2's Popular Items)
    Route::get('/cart/recommendations', [CartController::class, 'recommendations']);
});
```

---

### 6.5 API Route Configuration

The following API endpoints are implemented in the Order & Pickup module:

| Method | Endpoint | Function | Type | Description |
|--------|----------|----------|------|-------------|
| GET | /api/orders | index() | Protected | List user's orders with pagination |
| POST | /api/orders | store() | Protected | Create new order from cart |
| GET | /api/orders/active | active() | Protected | Get active orders |
| GET | /api/orders/history | history() | Protected | Get order history with stats |
| POST | /api/orders/validate-pickup | validatePickupQr() | **EXPOSED** | Validate pickup QR code |
| GET | /api/orders/{order} | show() | Protected | Get single order details |
| POST | /api/orders/{order}/cancel | cancel() | Protected | Cancel an order |
| POST | /api/orders/{order}/reorder | reorder() | Protected | Add order items back to cart |
| GET | /api/orders/{order}/status | status() | **EXPOSED** | Get order status |

---

### 6.7 Security Features

| Feature | Implementation | OWASP Reference |
|---------|---------------|-----------------|
| IDOR Prevention | Order ownership verified before access | OWASP [4] |
| Authentication | Sanctum token-based auth required | OWASP [66-67] |
| Input Validation | QR code format validation | OWASP [5] |
| State Validation | Order status transitions controlled | OWASP [8] |

---

### 6.8 Implementation Files

| File | Purpose |
|------|---------|
| `app/Http/Controllers/Api/OrderController.php` | API controller with exposed web services |
| `app/Http/Controllers/Web/OrderController.php` | Web controller for order views |
| `app/Models/Order.php` | Order Eloquent model |
| `app/Models/Pickup.php` | Pickup Eloquent model |
| `app/Patterns/State/OrderContext.php` | State Pattern context |
| `app/Patterns/State/OrderState.php` | State Pattern interface |
| `app/Services/OrderService.php` | Order business logic service |
| `app/Traits/ApiResponse.php` | Standardized API response format |
