## 6. Web Services

### 6.1 Web Service Exposed #1: Item Availability API

#### 6.1.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Checks if a menu item is available and in stock. Used by Cart module to validate items before adding to cart or during checkout. This ensures customers cannot order unavailable items. |
| Source Module | Menu & Catalog (Student 2) |
| Target Module | Order & Pickup (Student 3), Cart, Checkout & Notifications (Student 4) |
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
// resources/views/layouts/app.blade.php (line 1320-1340)
// addToCart() function validates item availability before adding to cart

function addToCart(itemId, quantity = 1, btn = null) {
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    }
    
    // First check item availability using Student 2's API
    fetch('/api/menu/' + itemId + '/availability', {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(availData => {
        const data = availData.data || availData;
        if (!data.available || !data.is_available) {
            showToast('Sorry, this item is currently unavailable', 'error');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-cart3"></i>';
            }
            return Promise.reject('unavailable');
        }
        
        // Item is available, proceed to add to cart
        return fetch('{{ route("cart.add") }}', { /* add to cart */ });
    })
}
```

#### 6.1.9 Modules That Consume This API

| Module | Student | Usage Context |
|--------|---------|---------------|
| Order & Pickup | Student 3 | Validates items during order creation |
| Cart, Checkout & Notifications | Student 4 | Validates items before adding to cart |
| Checkout Page | Student 4 | Re-validates items during checkout |

---

### 6.2 Web Service Exposed #2: Popular Items API

#### 6.2.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Returns a list of popular menu items based on total sales (total_sold). Used by Cart module's "You might also like" section to provide trending recommendations and by Home page to display trending items. |
| Source Module | Menu & Catalog (Student 2) |
| Target Module | Order & Pickup (Student 3), Cart, Checkout & Notifications (Student 4), Home Page |
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

#### 6.2.8 Frontend Consumption Example (Home Page)

```javascript
// resources/views/home.blade.php (line 597-656)
// Load and render popular items using Student 2's API

function loadPopularItems() {
    const container = document.getElementById('popular-items-container');
    
    fetch('/api/menu/popular?limit=6', {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(response => {
        const data = response.data || response;
        if (data.items && data.items.length > 0) {
            // Render popular items from Student 2's API
            container.innerHTML = data.items.map(item => {
                const price = parseFloat(item.price).toFixed(2);
                const vendorName = item.vendor?.store_name || 'Vendor';
                const totalSold = item.total_sold || 0;
                
                return `
                <div class="col-md-4 col-lg-2">
                    <a href="/menu/${item.id}" class="text-decoration-none">
                        <div class="card h-100">
                            <img src="${item.image}" alt="${item.name}" class="card-img-top">
                            <span class="badge bg-danger">${totalSold} sold</span>
                            <div class="card-body">
                                <h6>${item.name}</h6>
                                <small>${vendorName}</small>
                                <span class="fw-bold">RM ${price}</span>
                            </div>
                        </div>
                    </a>
                </div>`;
            }).join('');
        }
    });
}

document.addEventListener('DOMContentLoaded', loadPopularItems);
```

#### 6.2.9 Modules that Consume This API

```javascript
// resources/views/cart/index.blade.php (line 193-261)
// Load popular items for "You might also like" section using Student 2's API

// Cart item IDs to exclude from popular items
const cartItemIds = @json($cartItemIds);
const wishlistIds = @json($wishlistIds);

/**
 * Load popular items from Student 2's Popular Items API
 * Consumes: GET /api/menu/popular
 * This replaces the random items with trending popular items
 */
function loadPopularItems() {
    const container = document.getElementById('popular-items-container');
    const section = document.getElementById('popular-items-section');
    
    if (!container || !section) return;

    fetch('/api/menu/popular?limit=8', {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(response => {
        const data = response.data || response;
        if (data.items && data.items.length > 0) {
            // Filter out items already in cart
            const filteredItems = data.items.filter(item => !cartItemIds.includes(item.id));
            
            if (filteredItems.length > 0) {
                // Take only first 4 items
                const displayItems = filteredItems.slice(0, 4);
                
                container.innerHTML = displayItems.map(item => {
                    const price = parseFloat(item.price).toFixed(2);
                    const vendorName = item.vendor?.store_name || 'Vendor';
                    const totalSold = item.total_sold || 0;
                    
                    return `
                    <div class="col-6 col-md-3">
                        <div class="card h-100 menu-item-card">
                            <div class="position-relative">
                                <a href="/menu/${item.id}">
                                    <img src="${item.image}" class="card-img-top" alt="${item.name}">
                                </a>
                                <span class="badge bg-danger position-absolute">
                                    <i class="bi bi-fire me-1"></i>${totalSold} sold
                                </span>
                            </div>
                            <div class="card-body p-3">
                                <a href="/menu/${item.id}" class="text-decoration-none text-dark">
                                    <h6 class="card-title mb-1">${item.name}</h6>
                                </a>
                                <small class="text-muted d-block mb-2">${vendorName}</small>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold">RM ${price}</span>
                                    <button type="button" class="btn btn-sm btn-primary" 
                                        onclick="addToCart(${item.id}, 1, this)">
                                        <i class="bi bi-cart-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>`;
                }).join('');
                
                section.style.display = 'block';
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', loadPopularItems);
```

#### 6.2.10 Modules That Consume This API

| Module | Student | Usage Context |
|--------|---------|---------------|
| Cart Page | Student 4 | Displays popular items as recommendations in cart page |
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

### 6.4 Web Service Consumed: Cart Summary API (Student 4)

#### 6.4.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Gets current cart summary to display cart status on menu pages. Consumed to show cart item count and total in the navigation bar. |
| Source Module | Cart, Checkout & Notifications (Student 4) |
| Target Module | Menu & Catalog (Student 2) |
| URL | http://127.0.0.1:8000/api/cart/summary |
| Function Name | summary() |
| HTTP Method | GET |

#### 6.4.2 Implementation

```javascript
// resources/views/layouts/app.blade.php (line 1483-1525)
// Navigation bar consumes Cart dropdown API which includes summary data

function loadCartDropdown() {
    fetch('/api/cart/dropdown', { headers: { 'Accept': 'application/json' } })
    .then(res => res.json())
    .then(response => {
        const data = response.data || response;
        const countEl = document.getElementById('cart-count');
        const totalEl = document.getElementById('cart-total-header');
        
        // Update cart badge in navigation (consumed by Menu module's navigation)
        countEl.textContent = data.count || 0;
        totalEl.textContent = 'RM ' + (data.total || 0).toFixed(2);
        
        // Display cart items in dropdown
        if (!data.items || data.items.length === 0) {
            container.innerHTML = '<div class="text-center py-4 text-muted">Your cart is empty</div>';
            return;
        }
        // ... render cart items
    });
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

### 6.7 Security Features

| Feature | Implementation | OWASP Reference |
|---------|---------------|-----------------|
| XSS Prevention | HTML encoding on item names | OWASP [7] |
| SQL Injection Prevention | Parameterized queries via Eloquent | OWASP [3] |
| Input Validation | Request validation for search queries | OWASP [5] |
| Rate Limiting | API rate limiting on search endpoint | OWASP [4] |

---

### 6.8 Implementation Files

| File | Purpose |
|------|---------|
| `app/Http/Controllers/Api/MenuController.php` | API controller with exposed web services |
| `app/Repositories/MenuItemRepository.php` | Repository Pattern implementation |
| `app/Models/MenuItem.php` | Menu item Eloquent model |
| `app/Models/Category.php` | Category Eloquent model |
| `app/Models/Vendor.php` | Vendor Eloquent model |
| `app/Helpers/ImageHelper.php` | Centralized image URL handling |
| `app/Traits/ApiResponse.php` | Standardized API response format |
