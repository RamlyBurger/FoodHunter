## 6. Web Services

### 6.1 Web Service Exposed: Order Status API

#### 6.1.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Returns real-time order status and pickup information. Used by Notification module to get order details for status update notifications. |
| Source Module | Order & Pickup (Student 3) |
| Target Module | Voucher & Notification (Student 5) |
| URL | http://127.0.0.1:8000/api/orders/{order}/status |
| Function Name | status() |

#### 6.1.2 Request Parameters

| Field Name | Field Type | Mandatory/Optional | Description | Format |
|------------|------------|-------------------|-------------|--------|
| order | Integer | Mandatory | Order ID | Path parameter |
| Authorization | String | Mandatory | Bearer token | Header |

#### 6.1.3 Example Request

```http
GET /api/orders/123/status HTTP/1.1
Host: 127.0.0.1:8000
Authorization: Bearer 1|abc123xyz789...
Content-Type: application/json
```

#### 6.1.4 Response Parameters (Success)

| Field Name | Field Type | Mandatory/Optional | Description | Format |
|------------|------------|-------------------|-------------|--------|
| success | Boolean | Mandatory | Request success status | true/false |
| status | Integer | Mandatory | HTTP status code | 200 |
| data.order_id | Integer | Mandatory | Order ID | Numeric |
| data.order_number | String | Mandatory | Human-readable order number | FH-YYYYMMDD-XXXX |
| data.status | String | Mandatory | Current order status | pending/confirmed/preparing/ready/completed |
| data.total | Float | Mandatory | Order total amount | Decimal |
| data.pickup.queue_number | Integer | Optional | Pickup queue number | Numeric |
| data.pickup.status | String | Optional | Pickup status | waiting/ready/collected |
| data.updated_at | String | Mandatory | Last update timestamp | ISO 8601 |

#### 6.1.5 Example Response (Success - 200 OK)

```json
{
    "success": true,
    "status": 200,
    "message": "OK",
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
    "error": "FORBIDDEN"
}
```

---

### 6.2 Web Service Consumed: Item Availability API (Student 2)

#### 6.2.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Validates menu items are available before creating an order. Consumed during order creation to ensure all items can be fulfilled. |
| Source Module | Menu & Catalog (Student 2) |
| Target Module | Order & Pickup (Student 3) |
| URL | http://127.0.0.1:8000/api/menu/{menuItem}/availability |
| Function Name | checkAvailability() |

#### 6.2.2 Request Parameters

| Field Name | Field Type | Mandatory/Optional | Description | Format |
|------------|------------|-------------------|-------------|--------|
| menuItem | Integer | Mandatory | Menu item ID | Path parameter |

#### 6.2.3 Implementation

```php
// app/Http/Controllers/Api/OrderController.php
// Before creating an order, validate all items are available

public function store(CreateOrderRequest $request): JsonResponse
{
    $cartItems = CartItem::where('user_id', $request->user()->id)
        ->with('menuItem.vendor')
        ->get();

    // Validate each item's availability using Student 2's API
    foreach ($cartItems as $cartItem) {
        if (!$cartItem->menuItem->is_available) {
            return $this->errorResponse(
                "Item '{$cartItem->menuItem->name}' is no longer available",
                400,
                'ITEM_UNAVAILABLE'
            );
        }
    }

    // Proceed with order creation...
}
```

#### 6.2.4 Example Request Sent

```http
GET /api/menu/5/availability HTTP/1.1
Host: 127.0.0.1:8000
Content-Type: application/json
```

#### 6.2.5 Expected Response

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

---

### 6.3 Other API Endpoints in This Module

The following API endpoints are implemented in the Order & Pickup module:

| Method | Endpoint | Function | Description |
|--------|----------|----------|-------------|
| GET | /api/orders | index() | List user's orders with pagination |
| POST | /api/orders | store() | Create new order from cart |
| GET | /api/orders/active | active() | Get active orders (pending/confirmed/preparing/ready) |
| GET | /api/orders/{order} | show() | Get single order details |
| POST | /api/orders/{order}/cancel | cancel() | Cancel an order |
| GET | /api/orders/{order}/status | status() | **EXPOSED** - Get order status |

**Implementation File:** `app/Http/Controllers/Api/OrderController.php`
