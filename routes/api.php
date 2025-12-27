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

// Authentication (Ng Wayne Xiang)
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Menu - Public (Haerine Deepak Singh)
Route::get('/categories', [MenuController::class, 'categories']);
Route::get('/vendors', [MenuController::class, 'vendors']);
Route::get('/vendors/{vendor}', [MenuController::class, 'vendorMenu']);
// Web Service: Vendor Availability (Lee Kin Hang exposes, Cart/Order modules consume)
Route::get('/vendors/{vendor}/availability', [MenuController::class, 'vendorAvailability']);
Route::get('/menu/featured', [MenuController::class, 'featured']);
Route::get('/menu/search', [MenuController::class, 'search']);
// Web Service: Popular Items (Haerine Deepak Singh exposes, Order/Cart modules consume)
Route::get('/menu/popular', [MenuController::class, 'popularItems']);
Route::get('/menu/{menuItem}', [MenuController::class, 'show']);
Route::get('/menu/{menuItem}/related', [MenuController::class, 'related']);
// Web Service: Menu Item Availability (Haerine Deepak Singh exposes, Low Nam Lee consumes)
Route::get('/menu/{menuItem}/availability', [MenuController::class, 'checkAvailability']);

// ============================================================================
// PROTECTED ROUTES (Authentication Required)
// ============================================================================

Route::middleware('auth:sanctum')->group(function () {
    
    // Auth (Ng Wayne Xiang)
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    // Web Service: Token Validation (Ng Wayne Xiang exposes, others consume)
    Route::post('/auth/validate-token', [AuthController::class, 'validateToken']);
    // Web Service: User Statistics (Ng Wayne Xiang exposes, Order/Menu modules consume)
    Route::get('/auth/user-stats', [AuthController::class, 'userStats']);

    // Cart (Lee Song Yan - Cart, Checkout & Notifications Module)
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'add']);
    Route::put('/cart/{cartItem}', [CartController::class, 'update']);
    Route::delete('/cart/{cartItem}', [CartController::class, 'remove']);
    Route::delete('/cart', [CartController::class, 'clear']);
    Route::get('/cart/count', [CartController::class, 'count']);
    // Web Service: Cart Validation (Lee Song Yan exposes, Order module consumes)
    Route::get('/cart/validate', [CartController::class, 'validateCart']);
    // Web Service: Cart Recommendations (consumes Haerine Deepak Singh's Popular Items)
    Route::get('/cart/recommendations', [CartController::class, 'recommendations']);

    // Orders (Low Nam Lee - Order & Pickup Module)
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/active', [OrderController::class, 'active']);
    // Web Service: Order History (consumes Ng Wayne Xiang's User Stats)
    Route::get('/orders/history', [OrderController::class, 'history']);
    // Web Service: Pickup QR Validation (Low Nam Lee exposes, Vendor module consumes)
    Route::post('/orders/validate-pickup', [OrderController::class, 'validatePickupQr']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::post('/orders/{order}/reorder', [OrderController::class, 'reorder']);
    // Web Service: Order Status (Low Nam Lee exposes, Lee Song Yan consumes)
    Route::get('/orders/{order}/status', [OrderController::class, 'status']);

    // Notifications (Lee Song Yan - Cart, Checkout & Notifications Module)
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/dropdown', [NotificationController::class, 'dropdown']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    // Web Service: Send Notification (Lee Song Yan exposes, Students 1,3,5 consume)
    Route::post('/notifications/send', [NotificationController::class, 'send']);

    // Web Service: Cart Summary (Lee Song Yan exposes, Haerine Deepak Singh consumes)
    Route::get('/cart/summary', [CartController::class, 'summary']);

    // Web Service: Validate Voucher (Lee Kin Hang exposes, Lee Song Yan consumes)
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

        // Voucher Management (Lee Kin Hang)
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
                    'status' => 400,
                    'message' => 'URL is required',
                    'error' => 'BAD_REQUEST',
                    'request_id' => \Illuminate\Support\Str::uuid()->toString(),
                    'timestamp' => now()->toIso8601String(),
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
                'status' => 200,
                'message' => 'Request completed',
                'request_id' => \Illuminate\Support\Str::uuid()->toString(),
                'timestamp' => now()->toIso8601String(),
                'data' => [
                    'response_status' => $response->getStatusCode(),
                    'response_headers' => $response->getHeaders(),
                    'response_body' => json_decode($response->getBody()->getContents(), true),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status' => 500,
                'message' => $e->getMessage(),
                'error' => 'SERVER_ERROR',
                'request_id' => \Illuminate\Support\Str::uuid()->toString(),
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    });
});
