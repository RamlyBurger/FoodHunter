## 6. Web Services

### 6.1 Web Service Exposed #1: Order Status API

#### 6.1.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Returns real-time order status and pickup information. Used by Notification module to get order details for status update notifications and by frontend for live order tracking. Implements State Pattern for order status transitions. |
| Source Module | Order & Pickup (Student 4) |
| Target Module | Voucher & Notification (Student 5), Frontend |
| URL | http://127.0.0.1:8000/api/orders/{order}/status |
| Function Name | status() |
| HTTP Method | GET |
| Authentication | Bearer Token Required |
| Design Pattern | State Pattern |

#### 6.1.2 Request Parameters

| Field Name | Field Type | Mandatory/Optional | Description | Format | Example |
|------------|------------|-------------------|-------------|--------|---------|
| order | Integer | Mandatory | Order ID | Path parameter | 123 |
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
| data.order_number | String | Mandatory | Human-readable order number | FH-YYYYMMDD-XXXX | FH-20251222-A1B2 |
| data.status | String | Mandatory | Current order status | pending/confirmed/preparing/ready/completed | preparing |
| data.total | Float | Mandatory | Order total amount | Decimal | 25.50 |
| data.pickup.queue_number | Integer | Optional | Pickup queue number | Numeric | 105 |
| data.pickup.status | String | Optional | Pickup status | waiting/ready/collected | waiting |
| data.updated_at | String | Mandatory | Last update timestamp | ISO 8601 | 2025-12-22T13:30:00+08:00 |

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
        "total": 25.50,
        "pickup": {
            "queue_number": 105,
            "status": "waiting"
        },
        "updated_at": "2025-12-22T13:30:00+08:00"
    }
}
```

#### 6.1.6 Example Response (Error - 403 Forbidden)

```json
{
    "success": false,
    "status": 403,
    "message": "Unauthorized",
    "error": "FORBIDDEN",
    "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "timestamp": "2025-12-22T13:30:00+08:00"
}
```

#### 6.1.7 Implementation Code

```php
// app/Http/Controllers/Api/OrderController.php

/**
 * Web Service: Expose - Get Order Status
 * Student 5 (Notifications) consumes this to get order details
 * Uses State Pattern for status management
 */
public function status(Request $request, Order $order): JsonResponse
{
    // Security: IDOR Protection - verify ownership
    if ($order->user_id !== $request->user()->id) {
        return $this->forbiddenResponse('Unauthorized');
    }

    return $this->successResponse([
        'order_id' => $order->id,
        'order_number' => $order->order_number,
        'status' => $order->status,
        'total' => (float) $order->total,
        'pickup' => $order->pickup ? [
            'queue_number' => $order->pickup->queue_number,
            'status' => $order->pickup->status,
        ] : null,
        'updated_at' => $order->updated_at,
    ]);
}
```

#### 6.1.8 Frontend Consumption Example

```javascript
// resources/views/orders/show.blade.php
// Poll order status for live tracking

let statusPollInterval;

function startStatusPolling(orderId) {
    statusPollInterval = setInterval(() => {
        fetch('/api/orders/' + orderId + '/status', {
            headers: {
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + getToken()
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                updateOrderStatusUI(data.data.status);
                updateQueueNumber(data.data.pickup?.queue_number);
                
                // Stop polling if order is completed
                if (data.data.status === 'completed' || data.data.status === 'cancelled') {
                    clearInterval(statusPollInterval);
                }
            }
        })
        .catch(err => console.error('Status poll failed:', err));
    }, 10000); // Poll every 10 seconds
}
```

#### 6.1.9 Modules That Consume This API

| Module | Student | Usage Context |
|--------|---------|---------------|
| Voucher & Notification | Student 5 | Get order details for notifications |
| Order Details Page | Frontend | Live order status tracking |
| Active Orders Page | Frontend | Display current order status |

---

### 6.2 Web Service Exposed #2: Pickup QR Validation API

#### 6.2.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Validates pickup QR codes scanned by vendors before completing order pickup. Verifies the QR code belongs to a valid, ready order and returns order details for confirmation. Critical for preventing unauthorized pickups. |
| Source Module | Order & Pickup (Student 4) |
| Target Module | Vendor Module, Vendor Scan Page |
| URL | http://127.0.0.1:8000/api/orders/validate-pickup |
| Function Name | validatePickupQr() |
| HTTP Method | POST |
| Authentication | Bearer Token Required |

#### 6.2.2 Request Parameters

| Field Name | Field Type | Mandatory/Optional | Description | Format | Example |
|------------|------------|-------------------|-------------|--------|---------|
| Authorization | String | Mandatory | Bearer token | Header | Bearer 1\|abc123xyz789 |
| qr_code | String | Mandatory | QR code value to validate | Body JSON | "PU-20251222-ABC123" |

#### 6.2.3 Example Request

```http
POST /api/orders/validate-pickup HTTP/1.1
Host: 127.0.0.1:8000
Authorization: Bearer 1|abc123xyz789...
Content-Type: application/json
Accept: application/json

{
    "qr_code": "PU-20251222-ABC123"
}
```

#### 6.2.4 Response Parameters (Success)

| Field Name | Field Type | Mandatory/Optional | Description | Format | Example |
|------------|------------|-------------------|-------------|--------|---------|
| success | Boolean | Mandatory | Request success status | true/false | true |
| status | Integer | Mandatory | HTTP status code | 200 | 200 |
| message | String | Mandatory | Response message | Text | "Success" |
| request_id | String | Mandatory | Unique request identifier | UUID | a1b2c3d4-e5f6-7890 |
| timestamp | String | Mandatory | Response timestamp | ISO 8601 | 2025-12-22T13:30:00+08:00 |
| data.valid | Boolean | Mandatory | QR code validity | true/false | true |
| data.order_id | Integer | Mandatory | Order ID | Numeric | 123 |
| data.order_number | String | Mandatory | Human-readable order number | Text | FH-20251222-A1B2 |
| data.vendor_id | Integer | Mandatory | Vendor ID for verification | Numeric | 5 |
| data.customer_name | String | Mandatory | Customer name for confirmation | Text | John Doe |
| data.queue_number | Integer | Mandatory | Pickup queue number | Numeric | 105 |
| data.total | Float | Mandatory | Order total amount | Decimal | 25.50 |
| data.items_count | Integer | Mandatory | Number of items in order | Numeric | 3 |
| data.status | String | Mandatory | Current order status | Text | ready |

#### 6.2.5 Example Response (Success - Valid QR)

```json
{
    "success": true,
    "status": 200,
    "message": "Success",
    "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "timestamp": "2025-12-22T13:30:00+08:00",
    "data": {
        "valid": true,
        "order_id": 123,
        "order_number": "FH-20251222-A1B2",
        "vendor_id": 5,
        "customer_name": "John Doe",
        "queue_number": 105,
        "total": 25.50,
        "items_count": 3,
        "status": "ready"
    }
}
```

#### 6.2.6 Example Response (Error - Invalid QR)

```json
{
    "success": false,
    "status": 404,
    "message": "Invalid QR code",
    "error": "QR_NOT_FOUND",
    "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "timestamp": "2025-12-22T13:30:00+08:00"
}
```

#### 6.2.7 Example Response (Error - Order Not Ready)

```json
{
    "success": false,
    "status": 400,
    "message": "Order is not ready for pickup. Current status: preparing",
    "error": "ORDER_NOT_READY",
    "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "timestamp": "2025-12-22T13:30:00+08:00"
}
```

#### 6.2.8 Example Response (Error - Already Picked Up)

```json
{
    "success": false,
    "status": 400,
    "message": "Order already picked up",
    "error": "ALREADY_PICKED_UP",
    "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "timestamp": "2025-12-22T13:30:00+08:00"
}
```

#### 6.2.9 Implementation Code

```php
// app/Http/Controllers/Api/OrderController.php

/**
 * Web Service: Expose - Validate Pickup QR Code API
 * Vendor module consumes this to verify pickup codes before completing orders
 * 
 * @param Request $request
 * @return JsonResponse
 */
public function validatePickupQr(Request $request): JsonResponse
{
    $request->validate([
        'qr_code' => 'required|string',
    ]);

    $pickup = Pickup::where('qr_code', $request->qr_code)->first();

    if (!$pickup) {
        return $this->errorResponse('Invalid QR code', 404, 'QR_NOT_FOUND');
    }

    $order = $pickup->order;

    if (!$order) {
        return $this->errorResponse('Order not found', 404, 'ORDER_NOT_FOUND');
    }

    // Check if order is ready for pickup
    if ($order->status !== 'ready') {
        return $this->errorResponse(
            "Order is not ready for pickup. Current status: {$order->status}",
            400,
            'ORDER_NOT_READY'
        );
    }

    // Check if already picked up
    if ($pickup->status === 'picked_up') {
        return $this->errorResponse('Order already picked up', 400, 'ALREADY_PICKED_UP');
    }

    return $this->successResponse([
        'valid' => true,
        'order_id' => $order->id,
        'order_number' => $order->order_number,
        'vendor_id' => $order->vendor_id,
        'customer_name' => $order->user->name ?? 'Customer',
        'queue_number' => $pickup->queue_number,
        'total' => (float) $order->total,
        'items_count' => $order->items->count(),
        'status' => $order->status,
    ]);
}
```

#### 6.2.10 Frontend Consumption Example

```javascript
// resources/views/vendor/scan.blade.php
// Vendor QR scanner uses Student 4's API

form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validate QR using Student 4's API
    fetch('/api/orders/validate-pickup', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + getToken()
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
                html: `
                    <div class="text-start">
                        <p><strong>Order:</strong> #${data.order_number}</p>
                        <p><strong>Customer:</strong> ${data.customer_name}</p>
                        <p><strong>Queue:</strong> #${data.queue_number}</p>
                        <p><strong>Total:</strong> RM ${data.total.toFixed(2)}</p>
                        <p><strong>Items:</strong> ${data.items_count} item(s)</p>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Complete Pickup',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    completePickup(data.order_id);
                }
            });
        } else {
            showToast(response.message || 'Invalid QR code', 'error');
        }
    });
});
```

#### 6.2.11 Modules That Consume This API

| Module | Student | Usage Context |
|--------|---------|---------------|
| Vendor Module | Vendor | Verify QR code before marking pickup complete |
| Vendor Scan Page | Frontend | Display order details for confirmation |

---

### 6.3 Web Service Consumed: Voucher Validation API (Student 5)

#### 6.3.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Validates voucher codes during checkout to apply discounts. Consumed when user applies a voucher code while placing an order. Uses Factory Pattern for discount calculation. |
| Source Module | Voucher & Notification (Student 5) |
| Target Module | Order & Pickup (Student 4) |
| URL | http://127.0.0.1:8000/api/vouchers/validate |
| Function Name | validate() |
| HTTP Method | POST |

#### 6.3.2 Request Parameters

| Field Name | Field Type | Mandatory/Optional | Description | Format | Example |
|------------|------------|-------------------|-------------|--------|---------|
| Authorization | String | Mandatory | Bearer token | Header | Bearer 1\|abc123xyz789 |
| code | String | Mandatory | Voucher code | Uppercase string | SUMMER20 |
| subtotal | Decimal | Mandatory | Order subtotal | Numeric | 50.00 |

#### 6.3.3 Implementation

```php
// app/Http/Controllers/Api/OrderController.php
// During order creation, validate and apply voucher using Student 5's API

public function store(CreateOrderRequest $request): JsonResponse
{
    // ... cart validation ...

    $subtotal = $cartItems->sum(fn($item) => $item->menuItem->price * $item->quantity);
    $discount = 0.00;

    // Apply voucher if provided - consumes Student 5's Voucher Validation API
    if ($request->voucher_code) {
        $voucher = Voucher::where('code', strtoupper($request->voucher_code))
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->first();

        if ($voucher && $subtotal >= ($voucher->min_order ?? 0)) {
            // Calculate discount using Factory Pattern (Student 5)
            $discount = VoucherFactory::calculateDiscount($voucher, $subtotal);
        }
    }

    // ... order creation ...
}
```

#### 6.3.4 Example Request Sent

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

### 6.4 Web Service Consumed: User Statistics API (Student 1)

#### 6.4.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Gets user statistics for displaying order history with context. Consumed by order history endpoint to show user's overall activity summary. |
| Source Module | User & Authentication (Student 1) |
| Target Module | Order & Pickup (Student 4) |
| URL | http://127.0.0.1:8000/api/auth/user-stats |
| Function Name | userStats() |
| HTTP Method | GET |

#### 6.4.2 Implementation

```php
// app/Http/Controllers/Api/OrderController.php

/**
 * Get order history with user statistics
 * Web Service: Consumes Student 1's User Stats API for order insights
 * 
 * @param Request $request
 * @return JsonResponse
 */
public function history(Request $request): JsonResponse
{
    $user = $request->user();
    
    // Consume Student 1's User Stats internally
    $totalOrders = Order::where('user_id', $user->id)->count();
    $completedOrders = Order::where('user_id', $user->id)
        ->where('status', 'completed')->count();
    $totalSpent = Order::where('user_id', $user->id)
        ->where('status', 'completed')->sum('total');
    
    // Get recent orders
    $recentOrders = Order::where('user_id', $user->id)
        ->with(['vendor:id,store_name', 'pickup'])
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();

    return $this->successResponse([
        'user_stats' => [
            'total_orders' => $totalOrders,
            'completed_orders' => $completedOrders,
            'total_spent' => round((float) $totalSpent, 2),
            'average_order' => $completedOrders > 0 
                ? round($totalSpent / $completedOrders, 2) : 0,
        ],
        'recent_orders' => $recentOrders->map(fn($order) => $this->formatOrder($order)),
    ]);
}
```

---

### 6.5 API Route Configuration

```php
// routes/api.php

Route::middleware('auth:sanctum')->group(function () {
    // Orders (Student 4)
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/active', [OrderController::class, 'active']);
    
    // Web Service: Order History (Student 4 consumes Student 1's User Stats)
    Route::get('/orders/history', [OrderController::class, 'history']);
    
    // Web Service: Pickup QR Validation (Student 4 exposes, Vendor module consumes)
    Route::post('/orders/validate-pickup', [OrderController::class, 'validatePickupQr']);
    
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::post('/orders/{order}/reorder', [OrderController::class, 'reorder']);
    
    // Web Service: Order Status (Student 4 exposes, Student 5 consumes)
    Route::get('/orders/{order}/status', [OrderController::class, 'status']);
});
```

---

### 6.6 Complete API Endpoints Summary

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

### 6.7 Design Pattern: State Pattern

The Order module implements the **State Pattern** for order status management:

```php
// app/Patterns/State/OrderContext.php
class OrderContext
{
    private OrderState $state;
    private Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->state = $this->resolveState($order->status);
    }

    public function canTransitionTo(string $newStatus): bool
    {
        return $this->state->canTransitionTo($newStatus);
    }

    public function transitionTo(string $newStatus): bool
    {
        if (!$this->canTransitionTo($newStatus)) {
            return false;
        }
        
        $this->order->status = $newStatus;
        $this->order->save();
        $this->state = $this->resolveState($newStatus);
        
        return true;
    }
}
```

---

### 6.8 Security Features

| Feature | Implementation | OWASP Reference |
|---------|---------------|-----------------|
| IDOR Prevention | Order ownership verified before access | OWASP [4] |
| Authentication | Sanctum token-based auth required | OWASP [66-67] |
| Input Validation | QR code format validation | OWASP [5] |
| State Validation | Order status transitions controlled | OWASP [8] |

---

### 6.9 Implementation Files

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
