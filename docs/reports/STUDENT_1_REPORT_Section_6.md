## 6. Web Services

### 6.1 Web Service Exposed: User Login API

#### 6.1.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Authenticates user credentials and returns an access token for API access. Implements rate limiting and session regeneration for security. |
| Source Module | User & Authentication (Student 1) |
| Target Module | Frontend Application / Mobile App |
| URL | http://127.0.0.1:8000/api/auth/login |
| Function Name | login() |

#### 6.1.2 Request Parameters

| Field Name | Field Type | Mandatory/Optional | Description | Format |
|------------|------------|-------------------|-------------|--------|
| email | String | Mandatory | User's registered email address | Valid email format |
| password | String | Mandatory | User's password | Min 8 characters |

#### 6.1.3 Example Request

```http
POST /api/auth/login HTTP/1.1
Host: 127.0.0.1:8000
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "SecurePass123!"
}
```

#### 6.1.4 Response Parameters (Success)

| Field Name | Field Type | Mandatory/Optional | Description | Format |
|------------|------------|-------------------|-------------|--------|
| success | Boolean | Mandatory | Operation success status | true/false |
| status | Integer | Mandatory | HTTP status code | 200 |
| message | String | Mandatory | Response message | Text |
| data.token | String | Mandatory | Authentication token | Bearer token |
| data.user.id | Integer | Mandatory | User ID | Numeric |
| data.user.name | String | Mandatory | User's full name | Text |
| data.user.email | String | Mandatory | User's email | Email format |
| data.user.role | String | Mandatory | User role | customer/vendor |

#### 6.1.5 Example Response (Success - 200 OK)

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
            "role": "customer",
            "phone": "+60123456789",
            "points_balance": 150
        }
    }
}
```

#### 6.1.6 Example Response (Error - 401 Unauthorized)

```json
{
    "success": false,
    "status": 401,
    "message": "Invalid username and/or password.",
    "error": "UNAUTHORIZED"
}
```

---

### 6.2 Web Service Exposed: Token Validation API

#### 6.2.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Validates an API token and returns user information. Used by other modules to verify user authentication status. |
| Source Module | User & Authentication (Student 1) |
| Target Module | All other modules (Menu, Cart, Order, Notification) |
| URL | http://127.0.0.1:8000/api/auth/validate-token |
| Function Name | validateToken() |

#### 6.2.2 Request Parameters

| Field Name | Field Type | Mandatory/Optional | Description | Format |
|------------|------------|-------------------|-------------|--------|
| Authorization | String | Mandatory | Bearer token in header | Bearer {token} |

#### 6.2.3 Example Request

```http
POST /api/auth/validate-token HTTP/1.1
Host: 127.0.0.1:8000
Authorization: Bearer 1|abc123xyz789tokenstring...
```

#### 6.2.4 Response Parameters (Success)

| Field Name | Field Type | Mandatory/Optional | Description | Format |
|------------|------------|-------------------|-------------|--------|
| success | Boolean | Mandatory | Operation success status | true/false |
| status | Integer | Mandatory | HTTP status code | 200 |
| data.valid | Boolean | Mandatory | Token validity status | true/false |
| data.user_id | Integer | Mandatory | User ID | Numeric |
| data.email | String | Mandatory | User's email | Email format |
| data.role | String | Mandatory | User role | customer/vendor |

#### 6.2.5 Example Response (Success - 200 OK)

```json
{
    "success": true,
    "status": 200,
    "message": "OK",
    "data": {
        "valid": true,
        "user_id": 1,
        "email": "john@example.com",
        "role": "customer"
    }
}
```

#### 6.2.6 Example Response (Error - 401 Unauthorized)

```json
{
    "success": false,
    "status": 401,
    "message": "Invalid or expired token",
    "error": "UNAUTHORIZED"
}
```

---

### 6.3 Web Service Consumed: Notification Service (Student 5)

#### 6.3.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Sends a welcome notification to newly registered users. Consumed after successful user registration. |
| Source Module | Voucher & Notification (Student 5) |
| Target Module | User & Authentication (Student 1) |
| URL | http://127.0.0.1:8000/api/notifications/send |
| Function Name | send() |

#### 6.3.2 Request Parameters

| Field Name | Field Type | Mandatory/Optional | Description | Format |
|------------|------------|-------------------|-------------|--------|
| user_id | Integer | Mandatory | Target user ID | Numeric |
| type | String | Mandatory | Notification type | welcome/order/promo |
| title | String | Mandatory | Notification title | Text (max 100) |
| message | String | Mandatory | Notification body | Text (max 500) |
| data | Object | Optional | Additional metadata | JSON object |

#### 6.3.3 Implementation

```php
// app/Http/Controllers/Api/AuthController.php
$this->notificationService->send(
    $user->id,
    'welcome',
    'Welcome to FoodHunter!',
    'Thank you for joining FoodHunter. Start exploring delicious food now!',
    ['registration_date' => now()->toDateString()]
);
```

#### 6.3.4 Example Request Sent

```json
{
    "user_id": 1,
    "type": "welcome",
    "title": "Welcome to FoodHunter!",
    "message": "Thank you for joining FoodHunter. Start exploring delicious food now!",
    "data": {
        "registration_date": "2025-12-22"
    }
}
```
