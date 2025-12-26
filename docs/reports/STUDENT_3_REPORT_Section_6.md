## 6. Web Services

### 6.1 Web Service Exposed #1: Cart Summary API

#### 6.1.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Returns the current user's cart summary including item count, subtotal, service fee, and total. Used by Menu module to show cart status in navigation bar and for calculating checkout totals. |
| Source Module | Cart & Checkout (Student 3) |
| Target Module | Menu & Catalog (Student 2), Order & Pickup (Student 4) |
| URL | http://127.0.0.1:8000/api/cart/summary |
| Function Name | summary() |
| HTTP Method | GET |
| Authentication | Bearer Token Required |

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

#### 6.1.7 Implementation Code

```php
// app/Http/Controllers/Api/CartController.php

/**
 * Web Service: Expose - Cart Summary API
 * Student 2 (Menu) consumes this for cart status display
 * 
 * @param Request $request
 * @return JsonResponse
 */
public function summary(Request $request): JsonResponse
{
    $cartItems = CartItem::where('user_id', $request->user()->id)
        ->with('menuItem')
        ->get();

    return $this->successResponse($this->calculateSummary($cartItems));
}

private function calculateSummary($cartItems): array
{
    $subtotal = $cartItems->sum(fn($item) => $item->getSubtotal());
    $serviceFee = 2.00;
    $total = $subtotal + $serviceFee;

    return [
        'item_count' => $cartItems->sum('quantity'),
        'subtotal' => (float) $subtotal,
        'service_fee' => $serviceFee,
        'discount' => 0.00,
        'total' => (float) $total,
    ];
}
```

#### 6.1.8 Frontend Consumption Example

```javascript
// resources/views/layouts/app.blade.php
// Load cart summary for navigation badge

async function loadCartSummary() {
    try {
        const response = await fetch('/api/cart/summary', {
            headers: {
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + getToken()
            }
        });
        const data = await response.json();
        
        if (data.success) {
            // Update cart badge in navigation
            document.querySelector('.cart-badge').textContent = data.data.item_count;
            document.querySelector('.cart-total').textContent = 'RM ' + data.data.total.toFixed(2);
        }
    } catch (err) {
        console.log('Cart summary not available');
    }
}
```

#### 6.1.9 Modules That Consume This API

| Module | Student | Usage Context |
|--------|---------|---------------|
| Menu & Catalog | Student 2 | Display cart badge in navigation |
| Order & Pickup | Student 4 | Calculate order totals |
| Checkout Page | Frontend | Display payment summary |

---

### 6.2 Web Service Exposed #2: Cart Validation API

#### 6.2.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Validates all items in the user's cart before checkout. Checks item availability, vendor status, and identifies any issues that would prevent order placement. Critical for ensuring a smooth checkout process. |
| Source Module | Cart & Checkout (Student 3) |
| Target Module | Order & Pickup (Student 4), Checkout Page |
| URL | http://127.0.0.1:8000/api/cart/validate |
| Function Name | validateCart() |
| HTTP Method | GET |
| Authentication | Bearer Token Required |

#### 6.2.2 Request Parameters

| Field Name | Field Type | Mandatory/Optional | Description | Format | Example |
|------------|------------|-------------------|-------------|--------|---------|
| Authorization | String | Mandatory | Bearer token | Header | Bearer 1\|abc123xyz789 |

#### 6.2.3 Example Request

```http
GET /api/cart/validate HTTP/1.1
Host: 127.0.0.1:8000
Authorization: Bearer 1|abc123xyz789...
Content-Type: application/json
Accept: application/json
```

#### 6.2.4 Response Parameters (Success)

| Field Name | Field Type | Mandatory/Optional | Description | Format | Example |
|------------|------------|-------------------|-------------|--------|---------|
| success | Boolean | Mandatory | Request success status | true/false | true |
| status | Integer | Mandatory | HTTP status code | 200 | 200 |
| message | String | Mandatory | Response message | Text | "Success" |
| request_id | String | Mandatory | Unique request identifier | UUID | a1b2c3d4-e5f6-7890 |
| timestamp | String | Mandatory | Response timestamp | ISO 8601 | 2025-12-22T13:30:00+08:00 |
| data.valid | Boolean | Mandatory | Overall cart validity | true/false | true |
| data.issues | Array | Mandatory | List of validation issues | Array of objects | [] |
| data.issues[].type | String | Optional | Issue type code | ITEM_UNAVAILABLE/VENDOR_CLOSED | ITEM_UNAVAILABLE |
| data.issues[].item_id | Integer | Optional | Affected menu item ID | Numeric | 5 |
| data.issues[].item_name | String | Optional | Affected item name | Text | Nasi Lemak |
| data.issues[].vendor_name | String | Optional | Affected vendor name | Text | Warung Kak |
| data.valid_items_count | Integer | Mandatory | Number of valid items | Numeric | 2 |
| data.total_items_count | Integer | Mandatory | Total items in cart | Numeric | 3 |
| data.summary | Object | Mandatory | Cart summary for valid items | Object | {...} |

#### 6.2.5 Example Response (Success - All Valid)

```json
{
    "success": true,
    "status": 200,
    "message": "Success",
    "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "timestamp": "2025-12-22T13:30:00+08:00",
    "data": {
        "valid": true,
        "issues": [],
        "valid_items_count": 3,
        "total_items_count": 3,
        "summary": {
            "item_count": 3,
            "subtotal": 25.50,
            "service_fee": 2.00,
            "discount": 0.00,
            "total": 27.50
        }
    }
}
```

#### 6.2.6 Example Response (Success - With Issues)

```json
{
    "success": true,
    "status": 200,
    "message": "Success",
    "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "timestamp": "2025-12-22T13:30:00+08:00",
    "data": {
        "valid": false,
        "issues": [
            {
                "type": "ITEM_UNAVAILABLE",
                "item_id": 5,
                "item_name": "Nasi Lemak Special"
            },
            {
                "type": "VENDOR_CLOSED",
                "item_id": 12,
                "item_name": "Mee Goreng",
                "vendor_name": "Restoran Mamak"
            }
        ],
        "valid_items_count": 1,
        "total_items_count": 3,
        "summary": {
            "item_count": 1,
            "subtotal": 8.00,
            "service_fee": 2.00,
            "discount": 0.00,
            "total": 10.00
        }
    }
}
```

#### 6.2.7 Example Response (Error - Empty Cart)

```json
{
    "success": false,
    "status": 400,
    "message": "Cart is empty",
    "error": "EMPTY_CART",
    "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "timestamp": "2025-12-22T13:30:00+08:00"
}
```

#### 6.2.8 Implementation Code

```php
// app/Http/Controllers/Api/CartController.php

/**
 * Web Service: Expose - Cart Validation API
 * Other modules (Checkout, Order) consume this to validate cart before processing
 * Checks item availability, vendor status, and minimum order requirements
 * 
 * @param Request $request
 * @return JsonResponse
 */
public function validateCart(Request $request): JsonResponse
{
    $cartItems = CartItem::where('user_id', $request->user()->id)
        ->with(['menuItem.vendor'])
        ->get();

    if ($cartItems->isEmpty()) {
        return $this->errorResponse('Cart is empty', 400, 'EMPTY_CART');
    }

    $issues = [];
    $validItems = [];
    
    foreach ($cartItems as $item) {
        if (!$item->menuItem) {
            $issues[] = ['type' => 'ITEM_NOT_FOUND', 'item_id' => $item->menu_item_id];
            continue;
        }
        
        if (!$item->menuItem->is_available) {
            $issues[] = [
                'type' => 'ITEM_UNAVAILABLE',
                'item_id' => $item->menu_item_id,
                'item_name' => $item->menuItem->name,
            ];
            continue;
        }
        
        if (!$item->menuItem->vendor || !$item->menuItem->vendor->is_open) {
            $issues[] = [
                'type' => 'VENDOR_CLOSED',
                'item_id' => $item->menu_item_id,
                'item_name' => $item->menuItem->name,
                'vendor_name' => $item->menuItem->vendor?->store_name,
            ];
            continue;
        }
        
        $validItems[] = $item;
    }

    $summary = $this->calculateSummary(collect($validItems));
    
    return $this->successResponse([
        'valid' => empty($issues),
        'issues' => $issues,
        'valid_items_count' => count($validItems),
        'total_items_count' => $cartItems->count(),
        'summary' => $summary,
    ]);
}
```

#### 6.2.9 Frontend Consumption Example

```javascript
// resources/views/cart/checkout.blade.php
// Validate cart before processing payment

async function processPayment() {
    const submitBtn = document.getElementById('submit-btn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Validating...';
    
    try {
        // Validate cart using Student 3's API before processing
        const cartValidation = await fetch('/api/cart/validate', {
            headers: {
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + getToken()
            }
        }).then(r => r.json());
        
        if (!cartValidation.success || !cartValidation.data?.valid) {
            const issues = cartValidation.data?.issues || [];
            let issueMsg = 'Some items in your cart are no longer available.';
            if (issues.length > 0) {
                issueMsg = issues.map(i => i.item_name + ': ' + i.type.replace('_', ' ')).join(', ');
            }
            Swal.fire({ title: 'Cart Issue', text: issueMsg, icon: 'warning' });
            submitBtn.disabled = false;
            return;
        }
        
        // Cart is valid, proceed with payment...
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
        // ... payment logic
    } catch (err) {
        console.error('Cart validation failed:', err);
    }
}
```

#### 6.2.10 Modules That Consume This API

| Module | Student | Usage Context |
|--------|---------|---------------|
| Order & Pickup | Student 4 | Validate before order creation |
| Checkout Page | Frontend | Pre-payment validation |
| Cart Page | Frontend | Real-time cart status check |

---

### 6.3 Web Service Consumed: Item Availability API (Student 2)

#### 6.3.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Validates menu items are available before adding to cart. Consumed when user clicks "Add to Cart" to ensure items are in stock and vendor is open. |
| Source Module | Menu & Catalog (Student 2) |
| Target Module | Cart & Checkout (Student 3) |
| URL | http://127.0.0.1:8000/api/menu/{menuItem}/availability |
| Function Name | checkAvailability() |
| HTTP Method | GET |

#### 6.3.2 Request Parameters

| Field Name | Field Type | Mandatory/Optional | Description | Format | Example |
|------------|------------|-------------------|-------------|--------|---------|
| menuItem | Integer | Mandatory | Menu item ID | Path parameter | 5 |

#### 6.3.3 Implementation

```php
// app/Http/Controllers/Api/CartController.php
// Before adding to cart, validate item is available

public function add(AddToCartRequest $request): JsonResponse
{
    $menuItem = MenuItem::findOrFail($request->menu_item_id);

    // Validate item availability using Student 2's Menu API logic
    if (!$menuItem->is_available) {
        return $this->errorResponse('Item is not available', 400, 'ITEM_UNAVAILABLE');
    }

    $cartItem = CartItem::updateOrCreate(
        [
            'user_id' => $request->user()->id,
            'menu_item_id' => $request->menu_item_id,
        ],
        [
            'quantity' => $request->quantity,
            'special_instructions' => $request->special_instructions,
        ]
    );

    return $this->successResponse(
        $this->formatCartItem($cartItem->load('menuItem')),
        'Item added to cart'
    );
}
```

#### 6.3.4 Example Request Sent

```http
GET /api/menu/5/availability HTTP/1.1
Host: 127.0.0.1:8000
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

### 6.4 Web Service Consumed: Popular Items API (Student 2)

#### 6.4.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Gets popular menu items for cart recommendations. Consumed to suggest additional items based on what's trending or related to cart contents. |
| Source Module | Menu & Catalog (Student 2) |
| Target Module | Cart & Checkout (Student 3) |
| URL | http://127.0.0.1:8000/api/menu/popular |
| Function Name | popularItems() |
| HTTP Method | GET |

#### 6.4.2 Implementation

```php
// app/Http/Controllers/Api/CartController.php

/**
 * Get cart recommendations
 * Web Service: Consumes Student 2's Popular Items API for recommendations
 * 
 * @param Request $request
 * @return JsonResponse
 */
public function recommendations(Request $request): JsonResponse
{
    $cartItems = CartItem::where('user_id', $request->user()->id)
        ->with('menuItem')
        ->get();

    // Get category IDs from cart items for targeted recommendations
    $categoryIds = $cartItems->pluck('menuItem.category_id')->unique()->filter()->values();
    
    // Consume Student 2's Popular Items API internally
    $popularItems = MenuItem::where('is_available', true)
        ->when($categoryIds->isNotEmpty(), fn($q) => $q->whereIn('category_id', $categoryIds))
        ->whereNotIn('id', $cartItems->pluck('menu_item_id'))
        ->with(['category:id,name', 'vendor:id,store_name'])
        ->orderBy('total_sold', 'desc')
        ->limit(6)
        ->get();

    return $this->successResponse([
        'recommendations' => $popularItems->map(fn($item) => [
            'id' => $item->id,
            'name' => $item->name,
            'price' => (float) $item->price,
            'image' => ImageHelper::menuItem($item->image),
            'total_sold' => $item->total_sold,
            'category' => $item->category?->name,
            'vendor' => $item->vendor?->store_name,
        ]),
    ]);
}
```

---

### 6.5 API Route Configuration

```php
// routes/api.php

Route::middleware('auth:sanctum')->group(function () {
    // Cart (Student 3)
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'add']);
    Route::put('/cart/{cartItem}', [CartController::class, 'update']);
    Route::delete('/cart/{cartItem}', [CartController::class, 'remove']);
    Route::delete('/cart', [CartController::class, 'clear']);
    Route::get('/cart/count', [CartController::class, 'count']);
    
    // Web Service: Cart Summary (Student 3 exposes, Student 2 consumes)
    Route::get('/cart/summary', [CartController::class, 'summary']);
    
    // Web Service: Cart Validation (Student 3 exposes, Checkout/Order modules consume)
    Route::get('/cart/validate', [CartController::class, 'validateCart']);
    
    // Web Service: Cart Recommendations (Student 3 consumes Student 2's Popular Items)
    Route::get('/cart/recommendations', [CartController::class, 'recommendations']);
});
```

---

### 6.6 Complete API Endpoints Summary

The following API endpoints are implemented in the Cart & Checkout module:

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

---

### 6.7 Security Features

| Feature | Implementation | OWASP Reference |
|---------|---------------|-----------------|
| Authentication | Sanctum token-based auth required | OWASP [66-67] |
| Authorization | User can only access own cart items | OWASP [4] |
| Input Validation | Request validation for quantities | OWASP [5] |
| IDOR Prevention | Cart items verified against user_id | OWASP [4] |

---

### 6.8 Implementation Files

| File | Purpose |
|------|---------|
| `app/Http/Controllers/Api/CartController.php` | API controller with exposed web services |
| `app/Http/Controllers/Web/CartController.php` | Web controller for checkout process |
| `app/Models/CartItem.php` | Cart item Eloquent model |
| `app/Http/Requests/Cart/AddToCartRequest.php` | Add to cart validation request |
| `app/Helpers/ImageHelper.php` | Centralized image URL handling |
| `app/Traits/ApiResponse.php` | Standardized API response format |
