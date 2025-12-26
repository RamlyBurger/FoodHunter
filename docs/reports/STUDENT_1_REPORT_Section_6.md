## 6. Web Services

### 6.1 Web Service Exposed #1: Token Validation API

#### 6.1.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Validates an API token and returns user information. Used by other modules to verify user authentication status before processing requests. This is a critical security API that ensures only authenticated users can access protected resources across all modules. |
| Source Module | User & Authentication (Student 1) |
| Target Module | All other modules (Menu, Cart, Order, Notification) |
| URL | http://127.0.0.1:8000/api/auth/validate-token |
| Function Name | validateToken() |
| HTTP Method | POST |
| Authentication | Bearer Token Required |

#### 6.1.2 Request Parameters

| Field Name | Field Type | Mandatory/Optional | Description | Format | Example |
|------------|------------|-------------------|-------------|--------|---------|
| Authorization | String | Mandatory | Bearer token in header | Bearer {token} | Bearer 1\|abc123xyz789 |

#### 6.1.3 Example Request

```http
POST /api/auth/validate-token HTTP/1.1
Host: 127.0.0.1:8000
Authorization: Bearer 1|abc123xyz789tokenstring...
Content-Type: application/json
Accept: application/json
```

#### 6.1.4 Response Parameters (Success)

| Field Name | Field Type | Mandatory/Optional | Description | Format | Example |
|------------|------------|-------------------|-------------|--------|---------|
| success | Boolean | Mandatory | Operation success status | true/false | true |
| status | Integer | Mandatory | HTTP status code | 200 | 200 |
| message | String | Mandatory | Response message | Text | "Token is valid" |
| request_id | String | Mandatory | Unique request identifier | UUID | a1b2c3d4-e5f6-7890 |
| timestamp | String | Mandatory | Response timestamp | ISO 8601 | 2025-12-22T13:30:00+08:00 |
| data.valid | Boolean | Mandatory | Token validity status | true/false | true |
| data.user_id | Integer | Mandatory | User ID | Numeric | 1 |
| data.email | String | Mandatory | User's email | Email format | john@example.com |
| data.role | String | Mandatory | User role | customer/vendor | customer |

#### 6.1.5 Example Response (Success - 200 OK)

```json
{
    "success": true,
    "status": 200,
    "message": "Token is valid",
    "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "timestamp": "2025-12-22T13:30:00+08:00",
    "data": {
        "valid": true,
        "user_id": 1,
        "email": "john@example.com",
        "role": "customer"
    }
}
```

#### 6.1.6 Example Response (Error - 401 Unauthorized)

```json
{
    "success": false,
    "status": 401,
    "message": "Invalid or expired token",
    "error": "UNAUTHORIZED",
    "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "timestamp": "2025-12-22T13:30:00+08:00"
}
```

#### 6.1.7 Implementation Code

```php
// app/Http/Controllers/Api/AuthController.php

/**
 * Web Service: Expose - Validate Token API
 * Other modules consume this to validate user tokens
 */
public function validateToken(Request $request): JsonResponse
{
    $token = $request->bearerToken();
    
    if (!$token) {
        return $this->unauthorizedResponse('No token provided');
    }

    $user = $this->authService->validateToken($token);

    if (!$user) {
        return $this->unauthorizedResponse('Invalid or expired token');
    }

    return $this->successResponse([
        'valid' => true,
        'user_id' => $user->id,
        'email' => $user->email,
        'role' => $user->role,
    ]);
}
```

#### 6.1.8 Frontend Consumption Example

```javascript
// Used by auth middleware across all modules
async function validateToken(token) {
    const response = await fetch('/api/auth/validate-token', {
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + token,
            'Accept': 'application/json'
        }
    });
    return await response.json();
}
```

#### 6.1.9 Modules That Consume This API

| Module | Student | Usage Context |
|--------|---------|---------------|
| Menu & Catalog | Student 2 | Wishlist operations authentication |
| Order & Pickup | Student 3 | Order management authentication |
| Cart, Checkout & Notifications | Student 4 | Cart operations and notification authentication |
| Vendor Management | Student 5 | Vendor operations authentication |

---

### 6.2 Web Service Exposed #2: User Statistics API

#### 6.2.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Returns comprehensive user statistics including total orders, completed orders, total spent, and active vouchers. Used by other modules to display user activity data and personalize recommendations. |
| Source Module | User & Authentication (Student 1) |
| Target Module | Order & Pickup (Student 3), Menu & Catalog (Student 2) |
| URL | http://127.0.0.1:8000/api/auth/user-stats |
| Function Name | userStats() |
| HTTP Method | GET |
| Authentication | Bearer Token Required |

#### 6.2.2 Request Parameters

| Field Name | Field Type | Mandatory/Optional | Description | Format | Example |
|------------|------------|-------------------|-------------|--------|---------|
| Authorization | String | Mandatory | Bearer token in header | Bearer {token} | Bearer 1\|abc123xyz789 |

#### 6.2.3 Example Request

```http
GET /api/auth/user-stats HTTP/1.1
Host: 127.0.0.1:8000
Authorization: Bearer 1|abc123xyz789tokenstring...
Content-Type: application/json
Accept: application/json
```

#### 6.2.4 Response Parameters (Success)

| Field Name | Field Type | Mandatory/Optional | Description | Format | Example |
|------------|------------|-------------------|-------------|--------|---------|
| success | Boolean | Mandatory | Operation success status | true/false | true |
| status | Integer | Mandatory | HTTP status code | 200 | 200 |
| message | String | Mandatory | Response message | Text | "Success" |
| request_id | String | Mandatory | Unique request identifier | UUID | a1b2c3d4-e5f6-7890 |
| timestamp | String | Mandatory | Response timestamp | ISO 8601 | 2025-12-22T13:30:00+08:00 |
| data.user_id | Integer | Mandatory | User ID | Numeric | 1 |
| data.total_orders | Integer | Mandatory | Total number of orders placed | Numeric | 15 |
| data.completed_orders | Integer | Mandatory | Number of completed orders | Numeric | 12 |
| data.total_spent | Float | Mandatory | Total amount spent on completed orders | Decimal | 245.50 |
| data.active_vouchers | Integer | Mandatory | Number of active redeemed vouchers | Numeric | 3 |
| data.member_since | String | Mandatory | Account creation date | ISO 8601 | 2025-01-15T10:30:00+08:00 |

#### 6.2.5 Example Response (Success - 200 OK)

```json
{
    "success": true,
    "status": 200,
    "message": "Success",
    "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "timestamp": "2025-12-22T13:30:00+08:00",
    "data": {
        "user_id": 1,
        "total_orders": 15,
        "completed_orders": 12,
        "total_spent": 245.50,
        "active_vouchers": 3,
        "member_since": "2025-01-15T10:30:00+08:00"
    }
}
```

#### 6.2.6 Example Response (Error - 401 Unauthorized)

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

#### 6.2.7 Implementation Code

```php
// app/Http/Controllers/Api/AuthController.php

/**
 * Web Service: Expose - User Statistics API
 * Other modules (Order, Menu) consume this to get user activity stats
 * 
 * @param Request $request
 * @return JsonResponse
 */
public function userStats(Request $request): JsonResponse
{
    $user = $request->user();
    
    // Get user statistics
    $totalOrders = \App\Models\Order::where('user_id', $user->id)->count();
    $completedOrders = \App\Models\Order::where('user_id', $user->id)
        ->where('status', 'completed')->count();
    $totalSpent = \App\Models\Order::where('user_id', $user->id)
        ->where('status', 'completed')->sum('total');
    $activeVouchers = \App\Models\UserVoucher::where('user_id', $user->id)
        ->whereHas('voucher', fn($q) => $q->where('is_active', true))
        ->count();
    
    return $this->successResponse([
        'user_id' => $user->id,
        'total_orders' => $totalOrders,
        'completed_orders' => $completedOrders,
        'total_spent' => round((float) $totalSpent, 2),
        'active_vouchers' => $activeVouchers,
        'member_since' => $user->created_at->toIso8601String(),
    ]);
}
```

#### 6.2.8 Frontend Consumption Example

```javascript
// resources/views/profile/index.blade.php
// Load user stats using Student 1's API
function loadUserStats() {
    fetch('/api/auth/user-stats', {
        headers: {
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + (document.querySelector('meta[name="api-token"]')?.content || '')
        }
    })
    .then(res => res.json())
    .then(response => {
        const data = response.data || response;
        if (data.total_orders !== undefined) {
            // Update stats display
            document.querySelectorAll('.stat-card-mini').forEach((card, index) => {
                const valueEl = card.querySelector('.value');
                if (index === 0 && valueEl) valueEl.textContent = data.total_orders;
                if (index === 1 && valueEl) valueEl.textContent = 'RM ' + data.total_spent.toFixed(0);
            });
        }
    })
    .catch(err => console.log('Stats API not available'));
}

// Load stats on page load
document.addEventListener('DOMContentLoaded', loadUserStats);
```

#### 6.2.9 Modules That Consume This API

| Module | Student | Usage Context |
|--------|---------|---------------|
| Order & Pickup | Student 3 | Order history() method displays user statistics |
| Menu & Catalog | Student 2 | Personalized recommendations based on order history |
| Profile Page | Frontend | Displays user statistics on profile dashboard |

---

### 6.3 Web Service Consumed: Notification Service (Student 4)

#### 6.3.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Sends a welcome notification to newly registered users. Consumed after successful user registration to provide a personalized onboarding experience. |
| Source Module | Cart, Checkout & Notifications (Student 4) |
| Target Module | User & Authentication (Student 1) |
| URL | http://127.0.0.1:8000/api/notifications/send |
| Function Name | send() |
| HTTP Method | POST |

#### 6.3.2 Request Parameters

| Field Name | Field Type | Mandatory/Optional | Description | Format | Example |
|------------|------------|-------------------|-------------|--------|---------|
| user_id | Integer | Mandatory | Target user ID | Numeric | 1 |
| type | String | Mandatory | Notification type | welcome/order/promo | welcome |
| title | String | Mandatory | Notification title | Text (max 100) | Welcome to FoodHunter! |
| message | String | Mandatory | Notification body | Text (max 500) | Thank you for joining... |
| data | Object | Optional | Additional metadata | JSON object | {"registration_date": "2025-12-22"} |

#### 6.3.3 Implementation

```php
// app/Http/Controllers/Api/AuthController.php
public function register(RegisterRequest $request): JsonResponse
{
    $user = $this->authService->register($request->validated());

    $token = $user->createToken('auth-token')->plainTextToken;

    // Web Service: Consume Notification Service (Student 4)
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

#### 6.3.5 Expected Response

```json
{
    "success": true,
    "status": 201,
    "message": "Notification sent successfully",
    "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "timestamp": "2025-12-22T13:30:00+08:00",
    "data": {
        "notification_id": 1,
        "type": "welcome",
        "title": "Welcome to FoodHunter!",
        "created_at": "2025-12-22T13:30:00+08:00"
    }
}
```

---

### 6.4 API Route Configuration

```php
// routes/api.php

// Authentication (Student 1) - Public Routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Authentication (Student 1) - Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    
    // Web Service: Token Validation (Student 1 exposes, others consume)
    Route::post('/auth/validate-token', [AuthController::class, 'validateToken']);
    
    // Web Service: User Statistics (Student 1 exposes, Order/Menu modules consume)
    Route::get('/auth/user-stats', [AuthController::class, 'userStats']);
});
```

---

### 6.5 Complete API Endpoints Summary

The following API endpoints are implemented in the User & Authentication module:

| Method | Endpoint | Function | Type | Description |
|--------|----------|----------|------|-------------|
| POST | /api/auth/register | register() | Public | Register new user account |
| POST | /api/auth/login | login() | Public | User login with email/password |
| POST | /api/auth/logout | logout() | Protected | User logout (invalidate token) |
| GET | /api/auth/user | user() | Protected | Get current authenticated user |
| POST | /api/auth/validate-token | validateToken() | **EXPOSED** | Validate authentication token |
| GET | /api/auth/user-stats | userStats() | **EXPOSED** | Get user activity statistics |

---

### 6.6 Design Pattern: Strategy Pattern

The Authentication module implements the **Strategy Pattern** for flexible authentication methods:

```php
// app/Services/AuthService.php
class AuthService
{
    private AuthContext $authContext;

    public function __construct()
    {
        // Default to password authentication strategy
        $this->authContext = new AuthContext(new PasswordAuthStrategy());
    }

    public function attemptLogin(string $email, string $password, ?string $ip = null): array
    {
        // Uses Strategy Pattern via AuthContext
        return $this->authContext->authenticate([
            'email' => $email,
            'password' => $password,
            'ip' => $ip,
        ]);
    }
}
```

---

### 6.7 Security Features

| Feature | Implementation | OWASP Reference |
|---------|---------------|-----------------|
| Rate Limiting | Login attempts limited per IP | OWASP [38-39] |
| Session Regeneration | Token refresh on login | OWASP [66-67] |
| Single-Device Login | Invalidate other sessions on new login | OWASP [66-67] |
| Password Complexity | Min 8 chars, mixed case, numbers, symbols | OWASP [38-39] |
| Token Validation | Sanctum token-based authentication | OWASP [66-67] |

---

### 6.8 Implementation Files

| File | Purpose |
|------|---------|
| `app/Http/Controllers/Api/AuthController.php` | API controller with exposed web services |
| `app/Services/AuthService.php` | Authentication business logic with Strategy Pattern |
| `app/Patterns/Strategy/AuthContext.php` | Strategy Pattern context |
| `app/Patterns/Strategy/PasswordAuthStrategy.php` | Password authentication strategy |
| `app/Http/Requests/Auth/LoginRequest.php` | Login validation request |
| `app/Http/Requests/Auth/RegisterRequest.php` | Registration validation request |
| `app/Traits/ApiResponse.php` | Standardized API response format |
