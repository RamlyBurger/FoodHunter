<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\Vendor\DashboardController as VendorDashboardController;
use App\Http\Controllers\Api\Vendor\MenuController as VendorMenuController;
use App\Http\Controllers\Api\Vendor\OrderController as VendorOrderController;
use App\Http\Controllers\Api\Vendor\VoucherController as VendorVoucherController;

/*
|--------------------------------------------------------------------------
| API Routes - FoodHunter
|--------------------------------------------------------------------------
|
| Web Service Integration:
| - Each module exposes REST endpoints
| - Each module consumes at least one other module's service
|
*/

// ============================================================================
// PUBLIC ROUTES
// ============================================================================

// Authentication (Student 1)
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Menu - Public (Student 2)
Route::get('/categories', [MenuController::class, 'categories']);
Route::get('/vendors', [MenuController::class, 'vendors']);
Route::get('/vendors/{vendor}', [MenuController::class, 'vendorMenu']);
Route::get('/menu/featured', [MenuController::class, 'featured']);
Route::get('/menu/search', [MenuController::class, 'search']);
Route::get('/menu/{menuItem}', [MenuController::class, 'show']);
Route::get('/menu/{menuItem}/related', [MenuController::class, 'related']);

// Web Service: Menu Item Availability (Student 2 exposes, Student 3 consumes)
Route::get('/menu/{menuItem}/availability', [MenuController::class, 'checkAvailability']);

// ============================================================================
// PROTECTED ROUTES (Authentication Required)
// ============================================================================

Route::middleware('auth:sanctum')->group(function () {
    
    // Auth (Student 1)
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    // Web Service: Token Validation (Student 1 exposes, others consume)
    Route::post('/auth/validate-token', [AuthController::class, 'validateToken']);

    // Cart (Student 3)
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'add']);
    Route::put('/cart/{cartItem}', [CartController::class, 'update']);
    Route::delete('/cart/{cartItem}', [CartController::class, 'remove']);
    Route::delete('/cart', [CartController::class, 'clear']);
    Route::get('/cart/count', [CartController::class, 'count']);

    // Orders (Student 4)
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/active', [OrderController::class, 'active']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::post('/orders/{order}/reorder', [OrderController::class, 'reorder']);
    // Web Service: Order Status (Student 4 exposes, Student 5 consumes)
    Route::get('/orders/{order}/status', [OrderController::class, 'status']);

    // Notifications (Student 5)
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/dropdown', [NotificationController::class, 'dropdown']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    // Web Service: Send Notification (Student 5 exposes, Students 1,4 consume)
    Route::post('/notifications/send', [NotificationController::class, 'send']);

    // Web Service: Cart Summary (Student 3 exposes, Student 2 consumes)
    Route::get('/cart/summary', [CartController::class, 'summary']);

    // Web Service: Validate Voucher (Student 5 exposes, Student 4 consumes)
    // Available to all authenticated users for checkout
    Route::post('/vouchers/validate', [VendorVoucherController::class, 'validate']);

    // ============================================================================
    // VENDOR ROUTES
    // ============================================================================

    Route::prefix('vendor')->middleware('vendor')->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [VendorDashboardController::class, 'index']);
        Route::post('/toggle-open', [VendorDashboardController::class, 'toggleOpen']);
        Route::get('/dashboard/top-items', [VendorDashboardController::class, 'topItems']);
        Route::get('/dashboard/recent-orders', [VendorDashboardController::class, 'recentOrders']);
        
        // Menu Management
        Route::get('/menu', [VendorMenuController::class, 'index']);
        Route::post('/menu', [VendorMenuController::class, 'store']);
        Route::get('/menu/{menuItem}', [VendorMenuController::class, 'show']);
        Route::put('/menu/{menuItem}', [VendorMenuController::class, 'update']);
        Route::delete('/menu/{menuItem}', [VendorMenuController::class, 'destroy']);
        Route::post('/menu/{menuItem}/toggle', [VendorMenuController::class, 'toggleAvailability']);
        Route::get('/categories', [VendorMenuController::class, 'categories']);
        
        // Order Management
        Route::get('/orders', [VendorOrderController::class, 'index']);
        Route::get('/orders/pending', [VendorOrderController::class, 'pending']);
        Route::get('/orders/{order}', [VendorOrderController::class, 'show']);
        Route::put('/orders/{order}/status', [VendorOrderController::class, 'updateStatus']);

        // Voucher Management (Student 5)
        Route::get('/vouchers', [VendorVoucherController::class, 'index']);
        Route::post('/vouchers', [VendorVoucherController::class, 'store']);
        Route::put('/vouchers/{voucher}', [VendorVoucherController::class, 'update']);
        Route::delete('/vouchers/{voucher}', [VendorVoucherController::class, 'destroy']);

        // Reports
        Route::get('/reports/revenue', [VendorDashboardController::class, 'revenueReport']);
    });

    // ============================================================================
    // CUSTOM API ROUTES (Dynamic endpoint testing)
    // ============================================================================
    Route::post('/custom-request', function (Request $request) {
        try {
            $method = strtoupper($request->input('method', 'GET'));
            $url = $request->input('url');
            $headers = $request->input('headers', []);
            $body = $request->input('body');

            if (!$url) {
                return response()->json([
                    'success' => false,
                    'message' => 'URL is required',
                ], 400);
            }

            $client = new \GuzzleHttp\Client();
            $options = [
                'headers' => array_merge([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ], $headers),
                'http_errors' => false,
            ];

            if ($body && in_array($method, ['POST', 'PUT', 'PATCH'])) {
                $options['json'] = $body;
            }

            $response = $client->request($method, $url, $options);

            return response()->json([
                'success' => true,
                'status' => $response->getStatusCode(),
                'headers' => $response->getHeaders(),
                'body' => json_decode($response->getBody()->getContents(), true),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    });
});
