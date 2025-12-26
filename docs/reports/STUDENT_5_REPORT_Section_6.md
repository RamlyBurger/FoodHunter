## 6. Web Services

### 6.1 Web Service Exposed #1: Validate Voucher API

#### 6.1.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Validates a voucher code and calculates the discount amount based on the cart subtotal. Used by Cart and Order modules during checkout to apply voucher discounts. Implements the Factory Pattern for flexible discount calculation strategies. |
| Source Module | Voucher & Notification (Student 5) |
| Target Module | Cart & Checkout (Student 3), Order & Pickup (Student 4) |
| URL | http://127.0.0.1:8000/api/vouchers/validate |
| Function Name | validate() |
| HTTP Method | POST |
| Authentication | Bearer Token Required |
| Design Pattern | Factory Pattern |

#### 6.1.2 Request Parameters

| Field Name | Field Type | Mandatory/Optional | Description | Format | Example |
|------------|------------|-------------------|-------------|--------|---------|
| Authorization | String | Mandatory | Bearer token | Header | Bearer 1\|abc123xyz789 |
| code | String | Mandatory | Voucher code to validate | Uppercase string | SUMMER20 |
| subtotal | Decimal | Mandatory | Cart subtotal for discount calculation | Numeric | 50.00 |

#### 6.1.3 Example Request

```http
POST /api/vouchers/validate HTTP/1.1
Host: 127.0.0.1:8000
Authorization: Bearer 1|abc123xyz789...
Content-Type: application/json
Accept: application/json

{
    "code": "SUMMER20",
    "subtotal": 50.00
}
```

#### 6.1.4 Response Parameters (Success)

| Field Name | Field Type | Mandatory/Optional | Description | Format | Example |
|------------|------------|-------------------|-------------|--------|---------|
| success | Boolean | Mandatory | Request success status | true/false | true |
| status | Integer | Mandatory | HTTP status code | 200 | 200 |
| message | String | Mandatory | Response message | Text | "Voucher is valid" |
| request_id | String | Mandatory | Unique request identifier | UUID | a1b2c3d4-e5f6-7890 |
| timestamp | String | Mandatory | Response timestamp | ISO 8601 | 2025-12-22T13:30:00+08:00 |
| data.voucher_id | Integer | Mandatory | Voucher ID | Numeric | 5 |
| data.code | String | Mandatory | Voucher code | Text | SUMMER20 |
| data.type | String | Mandatory | Voucher type | fixed/percentage | percentage |
| data.value | Decimal | Mandatory | Voucher value (% or RM) | Numeric | 20 |
| data.discount | Decimal | Mandatory | Calculated discount amount | Numeric | 10.00 |
| data.description | String | Mandatory | Human-readable description | Text | 20% off (max RM15.00) |
| data.min_order | Decimal | Optional | Minimum order requirement | Numeric | 30.00 |
| data.max_discount | Decimal | Optional | Maximum discount cap | Numeric | 15.00 |
| data.expires_at | String | Optional | Voucher expiry date | ISO 8601 | 2025-12-31T23:59:59+08:00 |

#### 6.1.5 Example Response (Success - 200 OK)

```json
{
    "success": true,
    "status": 200,
    "message": "Voucher is valid",
    "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "timestamp": "2025-12-22T13:30:00+08:00",
    "data": {
        "voucher_id": 5,
        "code": "SUMMER20",
        "type": "percentage",
        "value": 20,
        "discount": 10.00,
        "description": "20% off (max RM15.00)",
        "min_order": 30.00,
        "max_discount": 15.00,
        "expires_at": "2025-12-31T23:59:59+08:00"
    }
}
```

#### 6.1.6 Example Response (Error - Minimum Order Not Met)

```json
{
    "success": false,
    "status": 400,
    "message": "Voucher not applicable",
    "error": "MIN_ORDER_NOT_MET",
    "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "timestamp": "2025-12-22T13:30:00+08:00",
    "data": {
        "min_order_required": 30.00,
        "current_subtotal": 20.00
    }
}
```

#### 6.1.7 Example Response (Error - Voucher Expired)

```json
{
    "success": false,
    "status": 400,
    "message": "Voucher has expired",
    "error": "VOUCHER_EXPIRED",
    "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "timestamp": "2025-12-22T13:30:00+08:00"
}
```

#### 6.1.8 Example Response (Error - Voucher Not Found)

```json
{
    "success": false,
    "status": 404,
    "message": "Voucher not found",
    "error": "NOT_FOUND",
    "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "timestamp": "2025-12-22T13:30:00+08:00"
}
```

#### 6.1.9 Implementation Code

```php
// app/Http/Controllers/Api/VoucherController.php

/**
 * Web Service: Expose - Validate Voucher API
 * Other modules (Cart, Order) consume this to validate voucher codes
 * Uses Factory Pattern for discount calculation
 * 
 * @param Request $request
 * @return JsonResponse
 */
public function validate(Request $request): JsonResponse
{
    $request->validate([
        'code' => 'required|string',
        'subtotal' => 'required|numeric|min:0',
    ]);

    $voucher = Voucher::where('code', strtoupper($request->code))
        ->where('is_active', true)
        ->first();

    if (!$voucher) {
        return $this->errorResponse('Voucher not found', 404, 'NOT_FOUND');
    }

    // Check expiry
    if ($voucher->expires_at && $voucher->expires_at < now()) {
        return $this->errorResponse('Voucher has expired', 400, 'VOUCHER_EXPIRED');
    }

    // Check minimum order
    if ($voucher->min_order && $request->subtotal < $voucher->min_order) {
        return $this->errorResponse('Voucher not applicable', 400, 'MIN_ORDER_NOT_MET', [
            'min_order_required' => (float) $voucher->min_order,
            'current_subtotal' => (float) $request->subtotal,
        ]);
    }

    // Calculate discount using Factory Pattern
    $discount = VoucherFactory::calculateDiscount($voucher, $request->subtotal);

    return $this->successResponse([
        'voucher_id' => $voucher->id,
        'code' => $voucher->code,
        'type' => $voucher->type,
        'value' => (float) $voucher->value,
        'discount' => round($discount, 2),
        'description' => $voucher->description,
        'min_order' => $voucher->min_order ? (float) $voucher->min_order : null,
        'max_discount' => $voucher->max_discount ? (float) $voucher->max_discount : null,
        'expires_at' => $voucher->expires_at?->toIso8601String(),
    ], 'Voucher is valid');
}
```

#### 6.1.10 Frontend Consumption Example

```javascript
// resources/views/cart/index.blade.php
// Validate voucher using Student 5's API before applying

function applyVoucher() {
    const code = document.getElementById('voucher-code-input').value.trim();
    const subtotal = parseFloat(document.querySelector('.subtotal-value')?.textContent?.replace('RM ', '') || 0);
    
    // First validate voucher using Student 5's API
    fetch('/api/vouchers/validate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ code: code, subtotal: subtotal })
    })
    .then(res => res.json())
    .then(validateData => {
        if (!validateData.success) {
            showError(validateData.message || 'Invalid voucher code.');
            return;
        }
        
        // Voucher validated, now apply it
        applyValidatedVoucher(code, validateData.data);
    })
    .catch(err => {
        showError('Error validating voucher');
    });
}
```

#### 6.1.11 Modules That Consume This API

| Module | Student | Usage Context |
|--------|---------|---------------|
| Cart & Checkout | Student 3 | Validate voucher before applying to cart |
| Order & Pickup | Student 4 | Validate voucher during order creation |
| Cart Page | Frontend | Real-time voucher validation |
| Checkout Page | Frontend | Final voucher validation before payment |

---

### 6.2 Web Service Exposed #2: Send Notification API

#### 6.2.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Sends notifications to users for various events (order updates, promotions, welcome messages). Used by all modules to communicate with users. Implements the Observer Pattern for notification dispatch. |
| Source Module | Voucher & Notification (Student 5) |
| Target Module | User & Auth (Student 1), Cart & Checkout (Student 3), Order & Pickup (Student 4) |
| URL | http://127.0.0.1:8000/api/notifications/send |
| Function Name | send() |
| HTTP Method | POST |
| Authentication | Bearer Token Required (Admin/System) |
| Design Pattern | Observer Pattern |

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
    "data": {
        "order_id": 123,
        "queue_number": 105
    }
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

#### 6.2.7 Implementation Code

```php
// app/Services/NotificationService.php

/**
 * Web Service: Expose - Send Notification API
 * Other modules consume this via the service to send notifications
 * Uses Observer Pattern for notification dispatch
 */
class NotificationService
{
    /**
     * Send a notification to a user
     */
    public function send(
        int $userId,
        string $type,
        string $title,
        string $message,
        array $data = []
    ): Notification {
        $user = User::find($userId);
        
        if (!$user) {
            throw new \Exception('User not found');
        }

        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'is_read' => false,
        ]);
    }

    /**
     * Notify customer when order status changes
     */
    public function notifyOrderStatusChange(int $userId, int $orderId, string $status): void
    {
        $messages = [
            'confirmed' => 'Your order has been confirmed and is being prepared.',
            'preparing' => 'Your order is now being prepared.',
            'ready' => 'Your order is ready for pickup!',
            'completed' => 'Your order has been completed. Thank you!',
            'cancelled' => 'Your order has been cancelled.',
        ];

        $this->send(
            $userId,
            'order',
            "Order {$status}",
            $messages[$status] ?? "Order status updated to {$status}",
            ['order_id' => $orderId, 'status' => $status]
        );
    }

    /**
     * Notify vendor of new order
     */
    public function notifyVendorNewOrder(int $vendorUserId, int $orderId, string $customerName, float $total): void
    {
        $this->send(
            $vendorUserId,
            'order',
            'New Order Received!',
            "New order from {$customerName} worth RM" . number_format($total, 2),
            ['order_id' => $orderId, 'customer_name' => $customerName, 'total' => $total]
        );
    }
}
```

#### 6.2.8 Backend Consumption Example

```php
// app/Http/Controllers/Api/AuthController.php
// Student 1 consumes Student 5's Notification Service

public function register(RegisterRequest $request): JsonResponse
{
    $user = $this->authService->register($request->validated());
    $token = $user->createToken('auth-token')->plainTextToken;

    // Web Service: Consume Notification Service (Student 5)
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

```php
// app/Http/Controllers/Web/CartController.php
// Student 3 consumes Student 5's Notification Service

public function processCheckout(Request $request)
{
    // ... order creation logic ...

    // Send notifications to vendors for each order
    $notificationService = app(NotificationService::class);
    $customerName = Auth::user()->name;
    
    foreach ($orders as $order) {
        // Notify vendor of new order
        if ($order->vendor && $order->vendor->user) {
            $notificationService->notifyVendorNewOrder(
                $order->vendor->user->id,
                $order->id,
                $customerName,
                $order->total
            );
        }
        
        // Notify customer of order placed
        $notificationService->notifyOrderCreated(Auth::id(), $order->id);
    }

    // ... return response ...
}
```

#### 6.2.9 Modules That Consume This API

| Module | Student | Usage Context |
|--------|---------|---------------|
| User & Authentication | Student 1 | Welcome notification on registration |
| Cart & Checkout | Student 3 | Order confirmation notifications |
| Order & Pickup | Student 4 | Order status change notifications |
| Vendor Module | Vendor | New order notifications |

---

### 6.3 Web Service Consumed: Order Status API (Student 4)

#### 6.3.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Gets order status information to generate order-related notifications. Consumed when vendor updates order status to notify customers with accurate order details. |
| Source Module | Order & Pickup (Student 4) |
| Target Module | Voucher & Notification (Student 5) |
| URL | http://127.0.0.1:8000/api/orders/{order}/status |
| Function Name | status() |
| HTTP Method | GET |

#### 6.3.2 Request Parameters

| Field Name | Field Type | Mandatory/Optional | Description | Format | Example |
|------------|------------|-------------------|-------------|--------|---------|
| Authorization | String | Mandatory | Bearer token | Header | Bearer 1\|abc123xyz789 |
| order | Integer | Mandatory | Order ID | Path parameter | 123 |

#### 6.3.3 Implementation

```php
// app/Http/Controllers/Api/Vendor/OrderController.php
// When order status is updated, NotificationService gets order details

public function updateStatus(Request $request, Order $order): JsonResponse
{
    $result = $this->orderService->updateStatusWithLocking(
        $order->id,
        $request->status,
        $request->reason ?? 'Cancelled by vendor'
    );

    if ($result['success']) {
        // NotificationService consumes Student 4's Order Status API internally
        // to get order_number, queue_number for notification messages
        $this->notificationService->notifyOrderStatusChange(
            $order->user_id,
            $order->id,
            $result['new_status']
        );
    }

    return $this->successResponse(['status' => $result['new_status']], 'Order status updated');
}
```

#### 6.3.4 Example Request Sent

```http
GET /api/orders/123/status HTTP/1.1
Host: 127.0.0.1:8000
Authorization: Bearer 1|abc123xyz789...
Content-Type: application/json
Accept: application/json
```

#### 6.3.5 Expected Response

```json
{
    "success": true,
    "status": 200,
    "message": "Success",
    "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "timestamp": "2025-12-22T14:30:00+08:00",
    "data": {
        "order_id": 123,
        "order_number": "FH-20251222-A1B2",
        "status": "ready",
        "total": 45.00,
        "pickup": {
            "queue_number": 105,
            "status": "ready"
        },
        "updated_at": "2025-12-22T14:30:00+08:00"
    }
}
```

---

### 6.4 API Route Configuration

```php
// routes/api.php

// Notifications (Student 5)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/dropdown', [NotificationController::class, 'dropdown']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    
    // Web Service: Send Notification (Student 5 exposes, all modules consume)
    Route::post('/notifications/send', [NotificationController::class, 'send']);
});

// Vouchers (Student 5)
Route::middleware('auth:sanctum')->group(function () {
    // Web Service: Voucher Validation (Student 5 exposes, Cart/Order modules consume)
    Route::post('/vouchers/validate', [VoucherController::class, 'validate']);
});

// Vendor Voucher Management
Route::middleware(['auth:sanctum', 'vendor'])->prefix('vendor')->group(function () {
    Route::get('/vouchers', [VendorVoucherController::class, 'index']);
    Route::post('/vouchers', [VendorVoucherController::class, 'store']);
    Route::put('/vouchers/{voucher}', [VendorVoucherController::class, 'update']);
    Route::delete('/vouchers/{voucher}', [VendorVoucherController::class, 'destroy']);
});
```

---

### 6.5 Complete API Endpoints Summary

The following API endpoints are implemented in the Voucher & Notification module:

| Method | Endpoint | Function | Type | Description |
|--------|----------|----------|------|-------------|
| GET | /api/notifications | index() | Protected | List user's notifications |
| GET | /api/notifications/dropdown | dropdown() | Protected | Get notifications for dropdown |
| GET | /api/notifications/unread-count | unreadCount() | Protected | Get unread notification count |
| POST | /api/notifications/{id}/read | markAsRead() | Protected | Mark notification as read |
| POST | /api/notifications/read-all | markAllAsRead() | Protected | Mark all notifications as read |
| DELETE | /api/notifications/{id} | destroy() | Protected | Delete a notification |
| POST | /api/notifications/send | send() | **EXPOSED** | Send notification to user |
| POST | /api/vouchers/validate | validate() | **EXPOSED** | Validate voucher code |
| GET | /api/vendor/vouchers | index() | Vendor | List vendor's vouchers |
| POST | /api/vendor/vouchers | store() | Vendor | Create new voucher |
| PUT | /api/vendor/vouchers/{voucher} | update() | Vendor | Update voucher |
| DELETE | /api/vendor/vouchers/{voucher} | destroy() | Vendor | Delete voucher |

---

### 6.6 Design Pattern: Factory Pattern

The Voucher module implements the **Factory Pattern** for flexible discount calculation:

```php
// app/Patterns/Factory/VoucherFactory.php
class VoucherFactory
{
    /**
     * Calculate discount based on voucher type
     * Factory Pattern: Creates appropriate discount strategy
     */
    public static function calculateDiscount(Voucher $voucher, float $subtotal): float
    {
        $discount = match($voucher->type) {
            'percentage' => $subtotal * ($voucher->value / 100),
            'fixed' => $voucher->value,
            default => 0,
        };

        // Apply maximum discount cap if set
        if ($voucher->max_discount && $discount > $voucher->max_discount) {
            $discount = $voucher->max_discount;
        }

        // Ensure discount doesn't exceed subtotal
        return min($discount, $subtotal);
    }

    /**
     * Create voucher with validation
     */
    public static function create(array $data): Voucher
    {
        return Voucher::create([
            'vendor_id' => $data['vendor_id'],
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'value' => $data['value'],
            'min_order' => $data['min_order'] ?? null,
            'max_discount' => $data['max_discount'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'expires_at' => $data['expires_at'] ?? null,
        ]);
    }
}
```

---

### 6.7 Design Pattern: Observer Pattern

The Notification module implements the **Observer Pattern** for event-driven notifications:

```php
// app/Services/NotificationService.php
// Observer Pattern: Listens to system events and dispatches notifications

class NotificationService
{
    // Notification type constants
    const TYPE_WELCOME = 'welcome';
    const TYPE_ORDER = 'order';
    const TYPE_PROMO = 'promo';
    const TYPE_SYSTEM = 'system';

    /**
     * Observer: Handle order created event
     */
    public function notifyOrderCreated(int $userId, int $orderId): void
    {
        $this->send(
            $userId,
            self::TYPE_ORDER,
            'Order Placed Successfully!',
            'Your order has been placed and is being processed.',
            ['order_id' => $orderId]
        );
    }

    /**
     * Observer: Handle user registration event
     */
    public function notifyWelcome(int $userId): void
    {
        $this->send(
            $userId,
            self::TYPE_WELCOME,
            'Welcome to FoodHunter!',
            'Start exploring delicious food from various vendors.',
            ['registration_date' => now()->toDateString()]
        );
    }
}
```

---

### 6.8 Security Features

| Feature | Implementation | OWASP Reference |
|---------|---------------|-----------------|
| Authentication | Sanctum token-based auth required | OWASP [66-67] |
| Authorization | User can only view own notifications | OWASP [4] |
| Input Validation | Voucher code and subtotal validated | OWASP [5] |
| Rate Limiting | Notification send endpoint rate limited | OWASP [4] |
| XSS Prevention | Notification content sanitized | OWASP [7] |

---

### 6.9 Implementation Files

| File | Purpose |
|------|---------|
| `app/Http/Controllers/Api/NotificationController.php` | Notification API controller |
| `app/Http/Controllers/Api/VoucherController.php` | Voucher validation API controller |
| `app/Http/Controllers/Api/Vendor/VoucherController.php` | Vendor voucher management |
| `app/Services/NotificationService.php` | Notification business logic (Observer Pattern) |
| `app/Patterns/Factory/VoucherFactory.php` | Voucher discount calculation (Factory Pattern) |
| `app/Models/Notification.php` | Notification Eloquent model |
| `app/Models/Voucher.php` | Voucher Eloquent model |
| `app/Traits/ApiResponse.php` | Standardized API response format |
