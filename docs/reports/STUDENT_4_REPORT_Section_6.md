## 6. Web Services

### 6.1 Web Service Exposed: Send Notification API

#### 6.1.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Accepts notification requests from other modules. Creates in-app notifications for users. Used by Auth module for welcome notifications and Order module for status updates. |
| Source Module | Cart, Checkout & Notifications (Student 4) |
| Target Module | User & Authentication (Student 1), Order & Pickup (Student 3) |
| URL | http://127.0.0.1:8000/api/notifications/send |
| Function Name | send() |

#### 6.1.2 Request Parameters

| Field Name | Field Type | Mandatory/Optional | Description | Format |
|------------|------------|-------------------|-------------|--------|
| Authorization | String | Mandatory | Bearer token | Header |
| user_id | Integer | Mandatory | Target user ID | Numeric |
| type | String | Mandatory | Notification type | Max 50 chars |
| title | String | Mandatory | Notification title | Max 100 chars |
| message | String | Mandatory | Notification body | Max 500 chars |
| data | Object | Optional | Additional data | JSON object |

#### 6.1.3 Example Request

```http
POST /api/notifications/send HTTP/1.1
Host: 127.0.0.1:8000
Authorization: Bearer 1|abc123xyz789...
Content-Type: application/json

{
    "user_id": 5,
    "type": "welcome",
    "title": "Welcome to FoodHunter!",
    "message": "Your account has been created successfully.",
    "data": {"action": "profile"}
}
```

#### 6.1.4 Response Parameters (Success)

| Field Name | Field Type | Mandatory/Optional | Description | Format |
|------------|------------|-------------------|-------------|--------|
| success | Boolean | Mandatory | Request success status | true/false |
| status | Integer | Mandatory | HTTP status code | 201 |
| data.notification_id | Integer | Mandatory | Created notification ID | Numeric |
| data.sent_at | String | Mandatory | Timestamp when sent | ISO 8601 |

#### 6.1.5 Example Response (Success - 201 Created)

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

#### 6.1.6 Example Response (Error - 422 Unprocessable Entity)

```json
{
    "success": false,
    "status": 422,
    "message": "Validation failed",
    "errors": {
        "user_id": ["The user_id field is required."],
        "title": ["The title field is required."]
    }
}
```

---

### 6.2 Web Service Consumed: Order Status API (Student 3)

#### 6.2.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Gets order status information for notification content. Consumed when generating order status notifications. |
| Source Module | Order & Pickup (Student 3) |
| Target Module | Cart, Checkout & Notifications (Student 4) |
| URL | http://127.0.0.1:8000/api/orders/{order}/status |
| Function Name | status() |

#### 6.2.2 Request Parameters

| Field Name | Field Type | Mandatory/Optional | Description | Format |
|------------|------------|-------------------|-------------|--------|
| order | Integer | Mandatory | Order ID | Path parameter |
| Authorization | String | Mandatory | Bearer token | Header |

#### 6.2.3 Implementation

```php
// app/Services/NotificationService.php
// When order status changes, fetch order details from Student 3's API

public function notifyOrderStatusChanged(int $userId, int $orderId, string $newStatus): Notification
{
    // Get order details using Student 3's exposed Order Status API
    // The status endpoint returns order_number, status, pickup info
    
    $messages = [
        'confirmed' => 'Your order has been confirmed by the vendor.',
        'preparing' => 'Your order is now being prepared.',
        'ready' => 'Your order is ready for pickup!',
        'cancelled' => 'Your order has been cancelled.',
    ];

    return Notification::create([
        'user_id' => $userId,
        'type' => 'order_status',
        'title' => 'Order Update',
        'message' => $messages[$newStatus] ?? "Order status: {$newStatus}",
        'data' => ['order_id' => $orderId, 'status' => $newStatus],
    ]);
}
```

#### 6.2.4 Example Request Sent

```http
GET /api/orders/123/status HTTP/1.1
Host: 127.0.0.1:8000
Authorization: Bearer 1|abc123xyz789...
```

#### 6.2.5 Expected Response

```json
{
    "success": true,
    "status": 200,
    "data": {
        "order_id": 123,
        "order_number": "FH-20251222-A1B2",
        "status": "ready",
        "pickup": {
            "queue_number": 105,
            "status": "ready"
        }
    }
}
```

---

### 6.3 Other API Endpoints in This Module

The following API endpoints are implemented in the Cart, Checkout & Notifications module:

| Method | Endpoint | Function | Description |
|--------|----------|----------|-------------|
| GET | /api/cart | index() | Get cart items with summary |
| POST | /api/cart | add() | Add item to cart |
| PUT | /api/cart/{cartItem} | update() | Update cart item quantity |
| DELETE | /api/cart/{cartItem} | remove() | Remove item from cart |
| DELETE | /api/cart | clear() | Clear entire cart |
| GET | /api/cart/summary | summary() | Get cart summary only |
| GET | /api/notifications | index() | List user's notifications |
| GET | /api/notifications/unread-count | unreadCount() | Get unread count |
| POST | /api/notifications/{id}/read | markAsRead() | Mark notification as read |
| POST | /api/notifications/read-all | markAllAsRead() | Mark all as read |
| DELETE | /api/notifications/{id} | destroy() | Delete notification |
| POST | /api/notifications/send | send() | **EXPOSED** - Send notification |

**Implementation Files:**
- `app/Http/Controllers/Api/CartController.php`
- `app/Http/Controllers/Api/NotificationController.php`
- `app/Services/NotificationService.php`
