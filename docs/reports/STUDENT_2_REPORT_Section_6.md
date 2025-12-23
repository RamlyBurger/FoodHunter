## 6. Web Services

### 6.1 Web Service Exposed: Item Availability API

#### 6.1.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Checks if a menu item is available and in stock. Used by Cart module to validate items before checkout. |
| Source Module | Menu & Catalog (Student 2) |
| Target Module | Cart & Checkout (Student 3) |
| URL | http://127.0.0.1:8000/api/menu/{menuItem}/availability |
| Function Name | checkAvailability() |

#### 6.1.2 Request Parameters

| Field Name | Field Type | Mandatory/Optional | Description | Format |
|------------|------------|-------------------|-------------|--------|
| menuItem | Integer | Mandatory | Menu item ID | Path parameter |

#### 6.1.3 Example Request

```http
GET /api/menu/5/availability HTTP/1.1
Host: 127.0.0.1:8000
Content-Type: application/json
```

#### 6.1.4 Response Parameters (Success)

| Field Name | Field Type | Mandatory/Optional | Description | Format |
|------------|------------|-------------------|-------------|--------|
| success | Boolean | Mandatory | Request success status | true/false |
| status | Integer | Mandatory | HTTP status code | 200 |
| data.item_id | Integer | Mandatory | Menu item ID | Numeric |
| data.name | String | Mandatory | Item name (HTML encoded) | Text |
| data.available | Boolean | Mandatory | Availability status | true/false |
| data.is_available | Boolean | Mandatory | Vendor marked as available | true/false |
| data.price | Float | Mandatory | Current item price | Decimal |

#### 6.1.5 Example Response (Success - 200 OK)

```json
{
    "success": true,
    "status": 200,
    "message": "OK",
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
    "error": "NOT_FOUND"
}
```

---

### 6.2 Web Service Exposed: Menu Search API

#### 6.2.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Searches menu items by name or description using Repository Pattern. Input is sanitized to prevent SQL injection. |
| Source Module | Menu & Catalog (Student 2) |
| Target Module | Frontend Application / Mobile App |
| URL | http://127.0.0.1:8000/api/menu/search?q={query} |
| Function Name | search() |

#### 6.2.2 Request Parameters

| Field Name | Field Type | Mandatory/Optional | Description | Format |
|------------|------------|-------------------|-------------|--------|
| q | String | Mandatory | Search query | Min 2 characters |

#### 6.2.3 Example Request

```http
GET /api/menu/search?q=nasi HTTP/1.1
Host: 127.0.0.1:8000
Content-Type: application/json
```

#### 6.2.4 Response Parameters (Success)

| Field Name | Field Type | Mandatory/Optional | Description | Format |
|------------|------------|-------------------|-------------|--------|
| success | Boolean | Mandatory | Request success status | true/false |
| status | Integer | Mandatory | HTTP status code | 200 |
| data | Array | Mandatory | List of matching items | JSON array |
| data[].id | Integer | Mandatory | Menu item ID | Numeric |
| data[].name | String | Mandatory | Item name | Text |
| data[].price | Float | Mandatory | Item price | Decimal |
| data[].vendor.id | Integer | Mandatory | Vendor ID | Numeric |
| data[].vendor.store_name | String | Mandatory | Vendor name | Text |

#### 6.2.5 Example Response (Success - 200 OK)

```json
{
    "success": true,
    "status": 200,
    "message": "OK",
    "data": [
        {
            "id": 5,
            "name": "Nasi Lemak Special",
            "price": 8.50,
            "image": "/images/menu/nasi-lemak.jpg",
            "is_available": true,
            "vendor": {
                "id": 1,
                "store_name": "Warung Makcik"
            }
        }
    ]
}
```

#### 6.2.6 Example Response (Error - 400 Bad Request)

```json
{
    "success": false,
    "status": 400,
    "message": "Search query must be at least 2 characters",
    "error": "INVALID_QUERY"
}
```

---

### 6.3 Web Service Consumed: Token Validation API (Student 1)

#### 6.3.1 Webservice Mechanism

| Description | Value |
|-------------|-------|
| Protocol | RESTful |
| Function Description | Validates user authentication token for wishlist operations. Consumed when user adds/removes items from wishlist. |
| Source Module | User & Authentication (Student 1) |
| Target Module | Menu & Catalog (Student 2) |
| URL | http://127.0.0.1:8000/api/auth/validate-token |
| Function Name | validateToken() |

#### 6.3.2 Request Parameters

| Field Name | Field Type | Mandatory/Optional | Description | Format |
|------------|------------|-------------------|-------------|--------|
| Authorization | String | Mandatory | Bearer token in header | Bearer {token} |

#### 6.3.3 Implementation

```php
// app/Http/Controllers/Web/WishlistController.php
// The wishlist controller uses auth:sanctum middleware which internally
// validates tokens via Student 1's authentication system

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle']);
});
```

#### 6.3.4 Example Request Sent

```http
POST /api/auth/validate-token HTTP/1.1
Host: 127.0.0.1:8000
Authorization: Bearer 1|abc123xyz789tokenstring...
```

#### 6.3.5 Expected Response

```json
{
    "success": true,
    "status": 200,
    "data": {
        "valid": true,
        "user_id": 1,
        "email": "john@example.com",
        "role": "customer"
    }
}
```
