<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RewardController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\Vendor\DashboardController as VendorDashboardController;
use App\Http\Controllers\Api\Vendor\MenuController as VendorMenuController;
use App\Http\Controllers\Api\Vendor\OrderController as VendorOrderController;
use App\Http\Controllers\Api\Vendor\SettingsController as VendorSettingsController;
use App\Http\Controllers\Api\Vendor\ReportController as VendorReportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// ============================================================================
// PUBLIC ROUTES (No Authentication Required)
// ============================================================================

// Home - Public Access
Route::prefix('home')->group(function () {
    Route::get('/', [HomeController::class, 'index']);
    Route::get('/statistics', [HomeController::class, 'statistics']);
});

// Authentication
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Menu - Public Access
Route::prefix('menu')->group(function () {
    Route::get('/', [MenuController::class, 'index']);
    Route::get('/search', [MenuController::class, 'search']);
    Route::get('/categories', [MenuController::class, 'categories']);
    Route::get('/vendors', [MenuController::class, 'vendors']);
    Route::get('/featured', [MenuController::class, 'featured']);
    Route::get('/popular', [MenuController::class, 'popular']);
    Route::get('/vendors/{vendorId}', [MenuController::class, 'vendorStore']);
    Route::get('/{id}', [MenuController::class, 'show']);
    Route::get('/{id}/related', [MenuController::class, 'related']);
});

// ============================================================================
// PROTECTED ROUTES (Authentication Required)
// ============================================================================

Route::middleware('auth:sanctum')->group(function () {
    
    // Auth - Protected
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/user', [AuthController::class, 'user']);
    });

    // Profile
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'index']);
        Route::put('/', [ProfileController::class, 'update']);
        Route::put('/password', [ProfileController::class, 'updatePassword']);
        Route::get('/orders', [ProfileController::class, 'recentOrders']);
        Route::get('/favorites', [ProfileController::class, 'favorites']);
        Route::get('/points', [ProfileController::class, 'loyaltyPoints']);
        Route::delete('/', [ProfileController::class, 'deleteAccount']);
    });

    // Cart
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/', [CartController::class, 'store']);
        Route::put('/{cartId}', [CartController::class, 'update']);
        Route::delete('/{cartId}', [CartController::class, 'destroy']);
        Route::delete('/', [CartController::class, 'clear']);
        Route::get('/count', [CartController::class, 'count']);
        Route::post('/voucher', [CartController::class, 'applyVoucher']);
        Route::delete('/voucher', [CartController::class, 'removeVoucher']);
        Route::get('/recommended', [CartController::class, 'recommended']);
    });

    // Checkout & Payment
    Route::prefix('checkout')->group(function () {
        Route::get('/', [PaymentController::class, 'checkout']);
        Route::post('/process', [PaymentController::class, 'processPayment']);
        Route::get('/confirmation/{orderId}', [PaymentController::class, 'confirmation']);
    });

    // Orders
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::post('/', [OrderController::class, 'store']);
        Route::get('/active', [OrderController::class, 'active']);
        Route::get('/{orderId}', [OrderController::class, 'show']);
        Route::post('/{orderId}/reorder', [OrderController::class, 'reorder']);
        Route::post('/{orderId}/cancel', [OrderController::class, 'cancel']);
    });

    // Wishlist
    Route::prefix('wishlist')->group(function () {
        Route::get('/', [WishlistController::class, 'index']);
        Route::post('/', [WishlistController::class, 'store']);
        Route::delete('/{id}', [WishlistController::class, 'destroy']);
        Route::post('/toggle', [WishlistController::class, 'toggle']);
        Route::get('/count', [WishlistController::class, 'count']);
        Route::get('/check/{itemId}', [WishlistController::class, 'check']);
        Route::delete('/', [WishlistController::class, 'clear']);
    });

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/recent', [NotificationController::class, 'recent']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
        Route::delete('/', [NotificationController::class, 'clear']);
    });

    // Rewards
    Route::prefix('rewards')->group(function () {
        Route::get('/', [RewardController::class, 'index']);
        Route::get('/points', [RewardController::class, 'points']);
        Route::get('/redeemed', [RewardController::class, 'redeemed']);
        Route::get('/{id}', [RewardController::class, 'show']);
        Route::post('/{id}/redeem', [RewardController::class, 'redeem']);
    });

    // ============================================================================
    // VENDOR ROUTES (Vendor Authentication Required)
    // ============================================================================

    Route::prefix('vendor')->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [VendorDashboardController::class, 'index']);
        Route::get('/dashboard/recent-orders', [VendorDashboardController::class, 'recentOrders']);
        Route::get('/dashboard/top-items', [VendorDashboardController::class, 'topSellingItems']);
        
        // Menu Management
        Route::prefix('menu')->group(function () {
            Route::get('/', [VendorMenuController::class, 'index']);
            Route::post('/', [VendorMenuController::class, 'store']);
            Route::get('/categories', [VendorMenuController::class, 'categories']);
            Route::get('/{id}', [VendorMenuController::class, 'show']);
            Route::put('/{id}', [VendorMenuController::class, 'update']);
            Route::delete('/{id}', [VendorMenuController::class, 'destroy']);
            Route::post('/{id}/toggle-availability', [VendorMenuController::class, 'toggleAvailability']);
        });
        
        // Order Management
        Route::prefix('orders')->group(function () {
            Route::get('/', [VendorOrderController::class, 'index']);
            Route::get('/{id}', [VendorOrderController::class, 'show']);
            Route::post('/{id}/status', [VendorOrderController::class, 'updateStatus']);
            Route::post('/{id}/accept', [VendorOrderController::class, 'accept']);
            Route::post('/{id}/reject', [VendorOrderController::class, 'reject']);
        });
        
        // Reports & Analytics
        Route::prefix('reports')->group(function () {
            Route::get('/', [VendorReportController::class, 'index']);
            Route::get('/revenue', [VendorReportController::class, 'revenue']);
            Route::get('/orders', [VendorReportController::class, 'orders']);
            Route::get('/top-items', [VendorReportController::class, 'topItems']);
            Route::get('/sales-chart', [VendorReportController::class, 'salesChart']);
        });
        
        // Settings
        Route::prefix('settings')->group(function () {
            Route::get('/', [VendorSettingsController::class, 'index']);
            Route::put('/store-info', [VendorSettingsController::class, 'updateStoreInfo']);
            Route::put('/operating-hours', [VendorSettingsController::class, 'updateOperatingHours']);
            Route::put('/notifications', [VendorSettingsController::class, 'updateNotifications']);
            Route::put('/payment-methods', [VendorSettingsController::class, 'updatePaymentMethods']);
            Route::post('/toggle-status', [VendorSettingsController::class, 'toggleStoreStatus']);
            Route::put('/password', [VendorSettingsController::class, 'updatePassword']);
            Route::put('/profile', [VendorSettingsController::class, 'updateProfile']);
        });
    });
});
