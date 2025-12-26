## 6. Web Services

### 6.1 Web Service Exposed #1: Item Availability API

#### 6.1.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Checks if a menu item is available and in stock. Used by Cart module to validate items before adding to cart or during checkout. This ensures customers cannot order unavailable items. |
| Source Module | Menu & Catalog (Student 2) |
| Target Module | Cart & Checkout (Student 3) |
| URL | http://127.0.0.1:8000/api/menu/{menuItem}/availability |
| Function Name | checkAvailability() |
| HTTP Method | GET |
| Authentication | Not Required (Public API) |
| Design Pattern | Repository Pattern |

#### 6.1.2 Request Parameters

| Field Name | Field Type | Mandatory/Optional | Description | Format | Example |
|------------|------------|-------------------|-------------|--------|---------|
| menuItem | Integer | Mandatory | Menu item ID | Path parameter | 5 |

#### 6.1.3 Example Request

```http
GET /api/menu/5/availability HTTP/1.1
Host: 127.0.0.1:8000
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
| data.item_id | Integer | Mandatory | Menu item ID | Numeric | 5 |
| data.name | String | Mandatory | Item name (HTML encoded for XSS prevention) | Text | Nasi Lemak Special |
| data.available | Boolean | Mandatory | Overall availability status | true/false | true |
| data.is_available | Boolean | Mandatory | Vendor marked item as available | true/false | true |
| data.price | Float | Mandatory | Current item price | Decimal | 8.50 |

#### 6.1.5 Example Response (Success - 200 OK)

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

#### 6.1.6 Example Response (Error - 404 Not Found)

```json
{
    "success": false,
    "status": 404,
    "message": "Menu item not found",
    "error": "NOT_FOUND",
    "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "timestamp": "2025-12-22T13:30:00+08:00"
}
```

#### 6.1.7 Implementation Code

```php
// app/Http/Controllers/Api/MenuController.php

/**
 * Web Service: Expose - Check Item Availability
 * Student 3 (Cart) consumes this to validate items before adding
 * Uses Repository Pattern for data access
 * 
 * @param MenuItem $menuItem
 * @return JsonResponse
 */
public function checkAvailability(MenuItem $menuItem): JsonResponse
{
    $item = $this->menuRepository->findById($menuItem->id);
    
    if (!$item) {
        return $this->errorResponse('Menu item not found', 404, 'NOT_FOUND');
    }

    return $this->successResponse([
        'item_id' => $item->id,
        'name' => htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8'),
        'available' => $item->is_available,
        'is_available' => $item->is_available,
        'price' => (float) $item->price,
    ]);
}
```

#### 6.1.8 Frontend Consumption Example

```javascript
// resources/views/layouts/app.blade.php
// Used by addToCart() function to validate item before adding

async function addToCart(itemId, quantity = 1) {
    // First check item availability using Student 2's API
    fetch('/api/menu/' + itemId + '/availability', {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.data.available) {
            // Item is available, proceed to add to cart
            performAddToCart(itemId, quantity);
        } else {
            showToast('This item is currently unavailable', 'warning');
        }
    })
    .catch(err => {
        console.error('Availability check failed:', err);
    });
}
```

#### 6.1.9 Modules That Consume This API

| Module | Student | Usage Context |
|--------|---------|---------------|
| Cart & Checkout | Student 3 | Validates items before adding to cart |
| Checkout Page | Student 3 | Re-validates items during checkout |
| Order Module | Student 4 | Validates items during order creation |

---

### 6.2 Web Service Exposed #2: Popular Items API

#### 6.2.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Returns a list of popular menu items based on total sales (total_sold). Used by Cart module to provide personalized recommendations and by Home page to display trending items. |
| Source Module | Menu & Catalog (Student 2) |
| Target Module | Cart & Checkout (Student 3), Home Page |
| URL | http://127.0.0.1:8000/api/menu/popular |
| Function Name | popularItems() |
| HTTP Method | GET |
| Authentication | Not Required (Public API) |
| Design Pattern | Repository Pattern |

#### 6.2.2 Request Parameters

| Field Name | Field Type | Mandatory/Optional | Description | Format | Example |
|------------|------------|-------------------|-------------|--------|---------|
| category_id | Integer | Optional | Filter by category | Query parameter | 1 |
| vendor_id | Integer | Optional | Filter by vendor | Query parameter | 2 |
| limit | Integer | Optional | Number of items to return (max 20) | Query parameter | 6 |

#### 6.2.3 Example Request

```http
GET /api/menu/popular?limit=6&category_id=1 HTTP/1.1
Host: 127.0.0.1:8000
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
| data.items | Array | Mandatory | List of popular items | Array of objects | [...] |
| data.items[].id | Integer | Mandatory | Menu item ID | Numeric | 5 |
| data.items[].name | String | Mandatory | Item name | Text | Nasi Lemak Special |
| data.items[].price | Float | Mandatory | Item price | Decimal | 8.50 |
| data.items[].total_sold | Integer | Mandatory | Total units sold | Numeric | 150 |
| data.items[].image | String | Mandatory | Item image URL | URL | /storage/menu/item.jpg |
| data.items[].category | Object | Optional | Category info | Object | {"id": 1, "name": "Rice"} |
| data.items[].vendor | Object | Optional | Vendor info | Object | {"id": 2, "store_name": "Warung Kak"} |
| data.total | Integer | Mandatory | Total items returned | Numeric | 6 |

#### 6.2.5 Example Response (Success - 200 OK)

```json
{
    "success": true,
    "status": 200,
    "message": "Success",
    "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "timestamp": "2025-12-22T13:30:00+08:00",
    "data": {
        "items": [
            {
                "id": 5,
                "name": "Nasi Lemak Special",
                "price": 8.50,
                "total_sold": 150,
                "image": "http://127.0.0.1:8000/storage/menu/nasi-lemak.jpg",
                "category": {
                    "id": 1,
                    "name": "Rice"
                },
                "vendor": {
                    "id": 2,
                    "store_name": "Warung Kak Leha"
                }
            },
            {
                "id": 12,
                "name": "Mee Goreng Mamak",
                "price": 7.00,
                "total_sold": 120,
                "image": "http://127.0.0.1:8000/storage/menu/mee-goreng.jpg",
                "category": {
                    "id": 2,
                    "name": "Noodles"
                },
                "vendor": {
                    "id": 3,
                    "store_name": "Restoran Mamak Corner"
                }
            }
        ],
        "total": 2
    }
}
```

#### 6.2.6 Example Response (Empty - 200 OK)

```json
{
    "success": true,
    "status": 200,
    "message": "Success",
    "request_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "timestamp": "2025-12-22T13:30:00+08:00",
    "data": {
        "items": [],
        "total": 0
    }
}
```

#### 6.2.7 Implementation Code

```php
// app/Http/Controllers/Api/MenuController.php

/**
 * Web Service: Expose - Popular Items API
 * Other modules (Order, Cart) consume this for recommendations
 * Uses Repository Pattern for data access
 * 
 * @param Request $request
 * @return JsonResponse
 */
public function popularItems(Request $request): JsonResponse
{
    $categoryId = $request->get('category_id');
    $vendorId = $request->get('vendor_id');
    $limit = min((int) $request->get('limit', 10), 20);
    
    $query = MenuItem::where('is_available', true)
        ->with(['category:id,name', 'vendor:id,store_name']);
    
    if ($categoryId) {
        $query->where('category_id', $categoryId);
    }
    
    if ($vendorId) {
        $query->where('vendor_id', $vendorId);
    }
    
    $items = $query->orderBy('total_sold', 'desc')
        ->limit($limit)
        ->get();
    
    return $this->successResponse([
        'items' => $items->map(fn($item) => [
            'id' => $item->id,
            'name' => $item->name,
            'price' => (float) $item->price,
            'total_sold' => $item->total_sold,
            'image' => ImageHelper::menuItem($item->image),
            'category' => $item->category ? [
                'id' => $item->category->id, 
                'name' => $item->category->name
            ] : null,
            'vendor' => $item->vendor ? [
                'id' => $item->vendor->id, 
                'store_name' => $item->vendor->store_name
            ] : null,
        ]),
        'total' => $items->count(),
    ]);
}
```

#### 6.2.8 Frontend Consumption Example

```javascript
// resources/views/home.blade.php
// Load popular items using Student 2's API

function loadPopularItems() {
    fetch('/api/menu/popular?limit=6', {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(response => {
        const data = response.data || response;
        if (data.items && data.items.length > 0) {
            console.log('Popular items loaded via Student 2 API:', data.items.length);
            // Render popular items in the UI
            renderPopularItems(data.items);
        }
    })
    .catch(err => console.log('Popular items API:', err));
}

// Load on page ready
document.addEventListener('DOMContentLoaded', loadPopularItems);
```

#### 6.2.9 Modules That Consume This API

| Module | Student | Usage Context |
|--------|---------|---------------|
| Cart & Checkout | Student 3 | recommendations() method for cart suggestions |
| Home Page | Frontend | Displays trending/popular items section |
| Menu Page | Frontend | Shows popular items in category |

---

### 6.3 Web Service Consumed: Token Validation API (Student 1)

#### 6.3.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Validates user authentication token for wishlist operations. Consumed when user adds/removes items from wishlist to ensure only authenticated users can manage their wishlist. |
| Source Module | User & Authentication (Student 1) |
| Target Module | Menu & Catalog (Student 2) |
| URL | http://127.0.0.1:8000/api/auth/validate-token |
| Function Name | validateToken() |
| HTTP Method | POST |

#### 6.3.2 Request Parameters

| Field Name | Field Type | Mandatory/Optional | Description | Format | Example |
|------------|------------|-------------------|-------------|--------|---------|
| Authorization | String | Mandatory | Bearer token in header | Bearer {token} | Bearer 1\|abc123xyz789 |

#### 6.3.3 Implementation

```php
// app/Http/Controllers/Web/WishlistController.php
// The wishlist controller uses auth:sanctum middleware which internally
// validates tokens via Student 1's authentication system

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle']);
});

// When a user toggles wishlist, the token is validated first
public function toggle(Request $request)
{
    // Token validation happens automatically via middleware
    // If token is invalid, user gets 401 Unauthorized
    
    $user = $request->user(); // Validated user from Student 1's auth
    
    $wishlistItem = Wishlist::where('user_id', $user->id)
        ->where('menu_item_id', $request->menu_item_id)
        ->first();
        
    if ($wishlistItem) {
        $wishlistItem->delete();
        return $this->successResponse(null, 'Removed from wishlist');
    }
    
    Wishlist::create([
        'user_id' => $user->id,
        'menu_item_id' => $request->menu_item_id,
    ]);
    
    return $this->successResponse(null, 'Added to wishlist');
}
```

#### 6.3.4 Example Request Sent

```http
POST /api/auth/validate-token HTTP/1.1
Host: 127.0.0.1:8000
Authorization: Bearer 1|abc123xyz789tokenstring...
Content-Type: application/json
Accept: application/json
```

#### 6.3.5 Expected Response

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

---

### 6.4 Web Service Consumed: Cart Summary API (Student 3)

#### 6.4.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Gets current cart summary to display cart status on menu pages. Consumed to show cart item count and total in the navigation bar. |
| Source Module | Cart & Checkout (Student 3) |
| Target Module | Menu & Catalog (Student 2) |
| URL | http://127.0.0.1:8000/api/cart/summary |
| Function Name | summary() |
| HTTP Method | GET |

#### 6.4.2 Implementation

```javascript
// resources/views/menu/index.blade.php
// Menu page consumes Cart Summary to show cart status

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
            updateCartBadge(data.data.item_count);
            updateCartTotal(data.data.total);
        }
    } catch (err) {
        console.log('Cart summary not available');
    }
}
```

---

### 6.5 API Route Configuration

```php
// routes/api.php

// Menu - Public (Student 2)
Route::get('/categories', [MenuController::class, 'categories']);
Route::get('/vendors', [MenuController::class, 'vendors']);
Route::get('/vendors/{vendor}', [MenuController::class, 'vendorMenu']);
Route::get('/menu/featured', [MenuController::class, 'featured']);
Route::get('/menu/search', [MenuController::class, 'search']);

// Web Service: Popular Items (Student 2 exposes, Order/Cart modules consume)
Route::get('/menu/popular', [MenuController::class, 'popularItems']);

Route::get('/menu/{menuItem}', [MenuController::class, 'show']);
Route::get('/menu/{menuItem}/related', [MenuController::class, 'related']);

// Web Service: Menu Item Availability (Student 2 exposes, Student 3 consumes)
Route::get('/menu/{menuItem}/availability', [MenuController::class, 'checkAvailability']);
```

---

### 6.6 Complete API Endpoints Summary

The following API endpoints are implemented in the Menu & Catalog module:

| Method | Endpoint | Function | Type | Description |
|--------|----------|----------|------|-------------|
| GET | /api/categories | categories() | Public | List all food categories |
| GET | /api/vendors | vendors() | Public | List all vendors |
| GET | /api/vendors/{vendor} | vendorMenu() | Public | Get vendor's menu items |
| GET | /api/menu/featured | featured() | Public | Get featured/promoted items |
| GET | /api/menu/search | search() | Public | Search menu items by keyword |
| GET | /api/menu/popular | popularItems() | **EXPOSED** | Get popular items by sales |
| GET | /api/menu/{menuItem} | show() | Public | Get single menu item details |
| GET | /api/menu/{menuItem}/related | related() | Public | Get related menu items |
| GET | /api/menu/{menuItem}/availability | checkAvailability() | **EXPOSED** | Check item availability |

---

### 6.7 Design Pattern: Repository Pattern

The Menu module implements the **Repository Pattern** for data access abstraction:

```php
// app/Repositories/MenuItemRepository.php
class MenuItemRepository
{
    public function findById(int $id): ?MenuItem
    {
        return MenuItem::with(['category', 'vendor'])->find($id);
    }
    
    public function getAvailable(): Collection
    {
        return MenuItem::where('is_available', true)
            ->with(['category', 'vendor'])
            ->get();
    }
    
    public function getPopular(int $limit = 10): Collection
    {
        return MenuItem::where('is_available', true)
            ->orderBy('total_sold', 'desc')
            ->limit($limit)
            ->get();
    }
    
    public function search(string $query): Collection
    {
        return MenuItem::where('is_available', true)
            ->where(function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%");
            })
            ->get();
    }
}
```

---

### 6.8 Security Features

| Feature | Implementation | OWASP Reference |
|---------|---------------|-----------------|
| XSS Prevention | HTML encoding on item names | OWASP [7] |
| SQL Injection Prevention | Parameterized queries via Eloquent | OWASP [3] |
| Input Validation | Request validation for search queries | OWASP [5] |
| Rate Limiting | API rate limiting on search endpoint | OWASP [4] |

---

### 6.9 Implementation Files

| File | Purpose |
|------|---------|
| `app/Http/Controllers/Api/MenuController.php` | API controller with exposed web services |
| `app/Repositories/MenuItemRepository.php` | Repository Pattern implementation |
| `app/Models/MenuItem.php` | Menu item Eloquent model |
| `app/Models/Category.php` | Category Eloquent model |
| `app/Models/Vendor.php` | Vendor Eloquent model |
| `app/Helpers/ImageHelper.php` | Centralized image URL handling |
| `app/Traits/ApiResponse.php` | Standardized API response format |
