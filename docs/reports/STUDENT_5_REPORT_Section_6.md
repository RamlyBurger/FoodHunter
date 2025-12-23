## 6. Web Services

### 6.1 Web Service Exposed: Validate Voucher API

#### 6.1.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Validates a voucher code and returns discount information. Used by Cart module during checkout to apply voucher discounts using the Factory Pattern. |
| Source Module | Vendor Management (Student 5) |
| Target Module | Cart, Checkout & Notifications (Student 4) |
| URL | http://127.0.0.1:8000/api/vendor/vouchers/validate |
| Function Name | validate() |

#### 6.1.2 Request Parameters

| Field Name | Field Type | Mandatory/Optional | Description | Format |
|------------|------------|-------------------|-------------|--------|
| Authorization | String | Mandatory | Bearer token | Header |
| code | String | Mandatory | Voucher code to validate | Uppercase string |
| subtotal | Decimal | Mandatory | Cart subtotal for discount calculation | Numeric |

#### 6.1.3 Example Request

```http
POST /api/vendor/vouchers/validate HTTP/1.1
Host: 127.0.0.1:8000
Authorization: Bearer 1|abc123xyz789...
Content-Type: application/json

{
    "code": "SUMMER20",
    "subtotal": 50.00
}
```

#### 6.1.4 Response Parameters (Success)

| Field Name | Field Type | Mandatory/Optional | Description | Format |
|------------|------------|-------------------|-------------|--------|
| success | Boolean | Mandatory | Request success status | true/false |
| status | Integer | Mandatory | HTTP status code | 200 |
| data.voucher_id | Integer | Mandatory | Voucher ID | Numeric |
| data.code | String | Mandatory | Voucher code | Text |
| data.type | String | Mandatory | Voucher type | fixed/percentage |
| data.discount | Decimal | Mandatory | Calculated discount amount | Numeric |
| data.description | String | Mandatory | Human-readable description | Text |

#### 6.1.5 Example Response (Success - 200 OK)

```json
{
    "success": true,
    "status": 200,
    "message": "Voucher is valid",
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

#### 6.1.6 Example Response (Error - 400 Bad Request)

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

### 6.2 Web Service Consumed: Send Notification API (Student 4)

#### 6.2.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Sends notification to vendor when a new order is received. Consumed when customer completes checkout. |
| Source Module | Cart, Checkout & Notifications (Student 4) |
| Target Module | Vendor Management (Student 5) |
| URL | http://127.0.0.1:8000/api/notifications/send |
| Function Name | send() |

#### 6.2.2 Request Parameters

| Field Name | Field Type | Mandatory/Optional | Description | Format |
|------------|------------|-------------------|-------------|--------|
| Authorization | String | Mandatory | Bearer token | Header |
| user_id | Integer | Mandatory | Vendor's user ID | Numeric |
| type | String | Mandatory | Notification type | Text |
| title | String | Mandatory | Notification title | Max 100 chars |
| message | String | Mandatory | Notification body | Max 500 chars |

#### 6.2.3 Implementation

```php
// app/Http/Controllers/Web/VendorController.php
// When order status is updated, notify customer using Student 4's API

public function updateOrderStatus(Request $request, Order $order)
{
    // ... status update logic ...

    if ($result) {
        // Consume Student 4's Notification API
        $notificationService = app(NotificationService::class);
        $notificationService->notifyCustomerOrderUpdate(
            $order->user_id,
            $order->id,
            $newStatus,
            $vendor->store_name
        );
    }
}
```

#### 6.2.4 Example Request Sent

```http
POST /api/notifications/send HTTP/1.1
Host: 127.0.0.1:8000
Authorization: Bearer 1|abc123xyz789...
Content-Type: application/json

{
    "user_id": 42,
    "type": "vendor_order",
    "title": "New Order Received!",
    "message": "You have a new order #FH-20251222-0001 worth RM45.00",
    "data": {"order_id": 123, "total": 45.00}
}
```

#### 6.2.5 Expected Response

```json
{
    "success": true,
    "status": 201,
    "message": "Notification sent successfully",
    "data": {
        "notification_id": 456,
        "sent_at": "2025-12-22T14:30:00+08:00"
    }
}
```

---

### 6.3 Other API Endpoints in This Module

The following API endpoints are implemented in the Vendor Management module:

| Method | Endpoint | Function | Description |
|--------|----------|----------|-------------|
| GET | /api/vendor/dashboard | dashboard() | Get dashboard statistics |
| GET | /api/vendor/orders | orders() | List vendor's orders |
| GET | /api/vendor/orders/pending | pendingOrders() | Get pending orders |
| GET | /api/vendor/orders/{id} | orderShow() | Get order details |
| PUT | /api/vendor/orders/{id}/status | updateStatus() | Update order status |
| GET | /api/vendor/menu | menu() | List menu items |
| POST | /api/vendor/menu | menuStore() | Create menu item |
| PUT | /api/vendor/menu/{id} | menuUpdate() | Update menu item |
| DELETE | /api/vendor/menu/{id} | menuDestroy() | Delete menu item |
| GET | /api/vendor/vouchers | vouchers() | List vouchers |
| POST | /api/vendor/vouchers | voucherStore() | Create voucher |
| PUT | /api/vendor/vouchers/{id} | voucherUpdate() | Update voucher |
| DELETE | /api/vendor/vouchers/{id} | voucherDestroy() | Delete voucher |
| POST | /api/vendor/vouchers/validate | validate() | **EXPOSED** - Validate voucher |

**Implementation Files:**
- `app/Http/Controllers/Web/VendorController.php`
- `app/Http/Controllers/Api/Vendor/OrderController.php`
- `app/Patterns/Factory/VoucherFactory.php`
- `app/Models/Voucher.php`
- `app/Models/Vendor.php`
