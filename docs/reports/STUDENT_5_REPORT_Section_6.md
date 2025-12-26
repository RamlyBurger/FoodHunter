## 6. Web Services

### 6.1 Web Service Exposed #1: Validate Voucher API

#### 6.1.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Validates a voucher code and calculates the discount amount based on the cart subtotal. Used by Cart and Order modules during checkout to apply voucher discounts. Implements the Factory Pattern for flexible discount calculation strategies. |
| Source Module | Vendor Management (Student 5) |
| Target Module | Cart, Checkout & Notifications (Student 4), Order & Pickup (Student 3) |
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
// resources/views/cart/index.blade.php (line 341-389)
// applyVoucher() function validates voucher using Student 5's API

function applyVoucher() {
    const input = document.getElementById('voucher-code-input');
    const btn = document.getElementById('apply-voucher-btn');
    const errorDiv = document.getElementById('voucher-error');
    const code = input.value.trim().toUpperCase();
    
    // First validate voucher using Student 5's API
    const subtotal = parseFloat(document.querySelector('.subtotal-value')?.textContent?.replace('RM ', '') || 0);
    
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
            errorDiv.textContent = validateData.message || 'Invalid voucher code.';
            errorDiv.style.display = 'block';
            btn.disabled = false;
            btn.innerHTML = 'Apply';
            return Promise.reject('validation_failed');
        }
        // Voucher validated via Student 5 API, now apply it
        return fetch('/vouchers/apply', { /* apply voucher */ });
    })
}
```

#### 6.1.11 Modules That Consume This API

| Module | Student | Usage Context |
|--------|---------|---------------|
| Cart, Checkout & Notifications | Student 4 | Validate voucher before applying to cart |
| Cart Page | Frontend | Real-time voucher validation on cart/index.blade.php |
| Checkout Page | Frontend | Final voucher validation before payment |

---

### 6.2 Web Service Exposed #2: Vendor Availability API

#### 6.2.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Returns vendor availability status including whether the vendor is currently open, their operating hours for the day, and estimated preparation time. Used by Cart and Order modules to validate vendor availability before placing orders. |
| Source Module | Vendor Management (Student 5) |
| Target Module | Cart, Checkout & Notifications (Student 4), Order & Pickup (Student 3), Menu & Catalog (Student 2) |
| URL | http://127.0.0.1:8000/api/vendors/{vendor}/availability |
| Function Name | vendorAvailability() |
| HTTP Method | GET |
| Authentication | Not Required (Public API) |

#### 6.2.2 Request Parameters

| Field Name | Field Type | Mandatory/Optional | Description | Format | Example |
|------------|------------|-------------------|-------------|--------|---------|
| vendor | Integer | Mandatory | Vendor ID | Path parameter | 1 |

#### 6.2.3 Example Request

```http
GET /api/vendors/1/availability HTTP/1.1
Host: 127.0.0.1:8000
Accept: application/json
```

#### 6.2.4 Response Parameters (Success)

| Field Name | Field Type | Mandatory/Optional | Description | Format | Example |
|------------|------------|-------------------|-------------|--------|---------|
| success | Boolean | Mandatory | Request success status | true/false | true |
| status | Integer | Mandatory | HTTP status code | 200 | 200 |
| message | String | Mandatory | Response message | Text | "Vendor availability retrieved" |
| request_id | String | Mandatory | Unique request identifier | UUID | a1b2c3d4-e5f6-7890 |
| timestamp | String | Mandatory | Response timestamp | ISO 8601 | 2025-12-22T13:30:00+08:00 |
| data.vendor_id | Integer | Mandatory | Vendor ID | Numeric | 1 |
| data.store_name | String | Mandatory | Vendor store name | Text | Mak Cik Corner |
| data.is_open | Boolean | Mandatory | Manual open/close status | true/false | true |
| data.is_currently_open | Boolean | Mandatory | Real-time availability based on hours | true/false | true |
| data.today_hours | Object | Optional | Today's operating hours | Object | {"open": "08:00", "close": "18:00"} |
| data.avg_prep_time | Integer | Mandatory | Average preparation time in minutes | Numeric | 15 |
| data.min_order_amount | Decimal | Optional | Minimum order requirement | Numeric | 10.00 |

#### 6.2.5 Example Response (Success - Vendor Open)

```json
{
    "success": true,
    "status": 200,
    "message": "Vendor availability retrieved",
    "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "timestamp": "2025-12-22T13:30:00+08:00",
    "data": {
        "vendor_id": 1,
        "store_name": "Mak Cik Corner",
        "is_open": true,
        "is_currently_open": true,
        "today_hours": {
            "day": "Monday",
            "open_time": "08:00",
            "close_time": "18:00",
            "is_closed": false
        },
        "avg_prep_time": 15,
        "min_order_amount": 10.00
    }
}
```

#### 6.2.6 Example Response (Success - Vendor Closed)

```json
{
    "success": true,
    "status": 200,
    "message": "Vendor availability retrieved",
    "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "timestamp": "2025-12-22T13:30:00+08:00",
    "data": {
        "vendor_id": 1,
        "store_name": "Mak Cik Corner",
        "is_open": false,
        "is_currently_open": false,
        "today_hours": {
            "day": "Sunday",
            "is_closed": true
        },
        "avg_prep_time": 15,
        "min_order_amount": 10.00,
        "closed_reason": "Outside operating hours"
    }
}
```

#### 6.2.7 Example Response (Error - Vendor Not Found)

```json
{
    "success": false,
    "status": 404,
    "message": "Vendor not found",
    "error": "NOT_FOUND",
    "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "timestamp": "2025-12-22T13:30:00+08:00"
}
```

#### 6.2.8 Frontend Consumption Example

```javascript
// resources/views/cart/checkout.blade.php (line 528-545)
// Checkout page consumes Vendor Availability API before processing payment

// Inside processPayment() function:
// Validate vendor availability using Student 5's API before processing
@if(isset($vendor) && $vendor)
const vendorAvailability = await fetch('/api/vendors/{{ $vendor->id }}/availability', {
    headers: { 'Accept': 'application/json' }
}).then(r => r.json());

if (!vendorAvailability.success || !vendorAvailability.data?.is_currently_open) {
    const closedReason = vendorAvailability.data?.closed_reason || 'Vendor is currently closed';
    Swal.fire({ 
        title: 'Vendor Closed', 
        text: `${vendorAvailability.data?.store_name || 'The vendor'} is currently unavailable. ${closedReason}`, 
        icon: 'warning', 
        confirmButtonColor: '#FF6B35' 
    });
    submitBtn.disabled = false;
    submitBtn.innerHTML = '<i class="bi bi-lock-fill me-2"></i>Pay RM {{ number_format($summary["total"], 2) }}';
    return;
}
@endif
```

#### 6.2.10 Modules That Consume This API

| Module | Student | Usage Context |
|--------|---------|---------------|
| Cart, Checkout & Notifications | Student 4 | Validate vendor is open before checkout |
| Order & Pickup | Student 3 | Verify vendor availability before order creation |
| Menu & Catalog | Student 2 | Display vendor open/closed status on menu pages |

---

### 6.3 Web Service Consumed: Send Notification API (Student 4)

#### 6.3.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Sends notifications to vendors when new orders are received. Consumed by Vendor Management module to notify vendors of incoming orders and important updates. |
| Source Module | Cart, Checkout & Notifications (Student 4) |
| Target Module | Vendor Management (Student 5) |
| URL | http://127.0.0.1:8000/api/notifications/send |
| Function Name | send() |
| HTTP Method | POST |

#### 6.3.2 Request Parameters

| Field Name | Field Type | Mandatory/Optional | Description | Format | Example |
|------------|------------|-------------------|-------------|--------|---------|
| Authorization | String | Mandatory | Bearer token | Header | Bearer 1\|abc123xyz789 |
| user_id | Integer | Mandatory | Target vendor user ID | Numeric | 5 |
| type | String | Mandatory | Notification type | order | order |
| title | String | Mandatory | Notification title | Text | New Order Received! |
| message | String | Mandatory | Notification body | Text | New order #FH-123 from customer |

#### 6.3.3 Example Request Sent

```http
POST /api/notifications/send HTTP/1.1
Host: 127.0.0.1:8000
Authorization: Bearer 1|abc123xyz789...
Content-Type: application/json
Accept: application/json

{
    "user_id": 5,
    "type": "order",
    "title": "New Order Received!",
    "message": "New order #FH-20251222-A1B2 from John Doe worth RM45.00",
    "data": {"order_id": 123, "customer_name": "John Doe", "total": 45.00}
}
```

#### 6.3.4 Expected Response

```json
{
    "success": true,
    "status": 201,
    "message": "Notification sent successfully",
    "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "timestamp": "2025-12-22T14:30:00+08:00",
    "data": {
        "notification_id": 1,
        "type": "order",
        "title": "New Order Received!",
        "created_at": "2025-12-22T14:30:00+08:00"
    }
}
```

---

### 6.4 API Route Configuration

```php
// routes/api.php

// Public Vendor Routes (Student 5)
Route::get('/vendors/{vendor}/availability', [MenuController::class, 'vendorAvailability']);

// Voucher Validation (Student 5 exposes, Cart/Order modules consume)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/vouchers/validate', [VendorVoucherController::class, 'validate']);
});

// Vendor Management Routes (Student 5)
Route::middleware(['auth:sanctum', 'vendor'])->prefix('vendor')->group(function () {
    Route::get('/dashboard', [VendorDashboardController::class, 'index']);
    Route::get('/vouchers', [VendorVoucherController::class, 'index']);
    Route::post('/vouchers', [VendorVoucherController::class, 'store']);
    Route::put('/vouchers/{voucher}', [VendorVoucherController::class, 'update']);
    Route::delete('/vouchers/{voucher}', [VendorVoucherController::class, 'destroy']);
    Route::get('/orders', [VendorOrderController::class, 'index']);
    Route::put('/orders/{order}/status', [VendorOrderController::class, 'updateStatus']);
});
```

---

### 6.5 Complete API Endpoints Summary

The following API endpoints are implemented in the Vendor Management module:

| Method | Endpoint | Function | Type | Description |
|--------|----------|----------|------|-------------|
| POST | /api/vouchers/validate | validate() | **EXPOSED** | Validate voucher code |
| GET | /api/vendors/{vendor}/availability | vendorAvailability() | **EXPOSED** | Check vendor availability status |
| GET | /api/vendor/dashboard | dashboard() | Vendor | Vendor dashboard stats |
| GET | /api/vendor/vouchers | index() | Vendor | List vendor's vouchers |
| POST | /api/vendor/vouchers | store() | Vendor | Create new voucher |
| PUT | /api/vendor/vouchers/{voucher} | update() | Vendor | Update voucher |
| DELETE | /api/vendor/vouchers/{voucher} | destroy() | Vendor | Delete voucher |
| GET | /api/vendor/orders | orders() | Vendor | List vendor's orders |
| PUT | /api/vendor/orders/{order}/status | updateStatus() | Vendor | Update order status |


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
| `app/Http/Controllers/Api/VoucherController.php` | Voucher validation API controller |
| `app/Http/Controllers/Api/Vendor/VoucherController.php` | Vendor voucher management |
| `app/Http/Controllers/Api/Vendor/MenuController.php` | Vendor menu management |
| `app/Http/Controllers/Api/Vendor/DashboardController.php` | Vendor dashboard |
| `app/Patterns/Factory/VoucherFactory.php` | Voucher discount calculation (Factory Pattern) |
| `app/Models/Vendor.php` | Vendor Eloquent model |
| `app/Models/VendorHour.php` | Vendor hours Eloquent model |
| `app/Models/Voucher.php` | Voucher Eloquent model |
| `app/Traits/ApiResponse.php` | Standardized API response format |
