<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RewardController;
use App\Http\Controllers\VendorDashboardController;
use App\Http\Controllers\VendorMenuController;
use App\Http\Controllers\VendorOrderController;
use App\Http\Controllers\VendorReportController;
use App\Http\Controllers\VendorSettingsController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\NotificationController;

// =====================================================
// AUTHENTICATION ROUTES (GET only, POST in auth.php)
// =====================================================
// Guest routes (only accessible when not logged in)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
});

// =====================================================
// PUBLIC ROUTES
// =====================================================
// Home Page
Route::get('/', [HomeController::class, 'index'])->name('home');

// Menu
Route::get('/menu', [MenuController::class, 'index'])->name('menu');

Route::get('/menu/{id}', [MenuController::class, 'show'])->name('food.details');

Route::get('/store/{vendorId}', [MenuController::class, 'showVendorStore'])->name('vendor.store');

// Contact
Route::get('/contact', function () {
    return view('contact');
})->name('contact');

Route::post('/contact', function () {
    return redirect()->route('contact')->with('success', 'Message sent successfully!');
})->name('contact.send');

// =====================================================
// AUTHENTICATED USER ROUTES
// =====================================================
Route::middleware('auth')->group(function () {
    // Cart
    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::put('/cart/{id}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{id}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
    Route::get('/cart/count', [CartController::class, 'getCount'])->name('cart.count');
    Route::post('/cart/apply-voucher', [CartController::class, 'applyVoucher'])->name('cart.applyVoucher');
    Route::post('/cart/remove-voucher', [CartController::class, 'removeVoucher'])->name('cart.removeVoucher');

    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
    Route::post('/wishlist/add', [WishlistController::class, 'add'])->name('wishlist.add');
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::delete('/wishlist/{id}', [WishlistController::class, 'remove'])->name('wishlist.remove');
    Route::get('/wishlist/count', [WishlistController::class, 'getCount'])->name('wishlist.count');

    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('order.details');
    Route::post('/orders/{id}/reorder', [OrderController::class, 'reorder'])->name('order.reorder');

    // Checkout & Payment
    Route::get('/checkout', [PaymentController::class, 'showCheckout'])->name('checkout');
    Route::post('/checkout', [PaymentController::class, 'processPayment'])->name('checkout.process');
    Route::get('/order/confirmation/{order}', [PaymentController::class, 'showConfirmation'])->name('order.confirmation');

    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/notifications', [ProfileController::class, 'updateNotifications'])->name('profile.notifications');
    
    // Rewards
    Route::get('/rewards', [RewardController::class, 'index'])->name('rewards');
    Route::post('/rewards/{reward}/redeem', [RewardController::class, 'redeem'])->name('rewards.redeem');
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{id}', [NotificationController::class, 'delete'])->name('notifications.delete');
});

// =====================================================
// VENDOR DASHBOARD (only accessible for vendors)
// =====================================================
Route::middleware(['auth'])->group(function () {
    Route::get('/vendor/dashboard', [VendorDashboardController::class, 'index'])->name('vendor.dashboard');
    
    // Menu Management with CRUD
    Route::get('/vendor/menu', [VendorMenuController::class, 'index'])->name('vendor.menu');
    Route::post('/vendor/menu', [VendorMenuController::class, 'store'])->name('vendor.menu.store');
    Route::put('/vendor/menu/{id}', [VendorMenuController::class, 'update'])->name('vendor.menu.update');
    Route::delete('/vendor/menu/{id}', [VendorMenuController::class, 'destroy'])->name('vendor.menu.destroy');
    Route::post('/vendor/menu/{id}/toggle-availability', [VendorMenuController::class, 'toggleAvailability'])->name('vendor.menu.toggle');
    
    // Order Management
    Route::get('/vendor/orders', [VendorOrderController::class, 'index'])->name('vendor.orders');
    Route::get('/vendor/orders/{id}', [VendorOrderController::class, 'show'])->name('vendor.orders.show');
    Route::post('/vendor/orders/{id}/accept', [VendorOrderController::class, 'accept'])->name('vendor.orders.accept');
    Route::post('/vendor/orders/{id}/reject', [VendorOrderController::class, 'reject'])->name('vendor.orders.reject');
    Route::post('/vendor/orders/{id}/status', [VendorOrderController::class, 'updateStatus'])->name('vendor.orders.status');
    
    // Reports & Analytics
    Route::get('/vendor/reports', [VendorReportController::class, 'index'])->name('vendor.reports');
    
    // Settings
    Route::get('/vendor/settings', [VendorSettingsController::class, 'index'])->name('vendor.settings');
    Route::post('/vendor/settings/store-info', [VendorSettingsController::class, 'updateStoreInfo'])->name('vendor.settings.store-info');
    Route::post('/vendor/settings/operating-hours', [VendorSettingsController::class, 'updateOperatingHours'])->name('vendor.settings.operating-hours');
    Route::post('/vendor/settings/notifications', [VendorSettingsController::class, 'updateNotifications'])->name('vendor.settings.notifications');
    Route::post('/vendor/settings/payment-methods', [VendorSettingsController::class, 'updatePaymentMethods'])->name('vendor.settings.payment-methods');
    Route::post('/vendor/settings/toggle-status', [VendorSettingsController::class, 'toggleStoreStatus'])->name('vendor.settings.toggle-status');
    Route::post('/vendor/settings/password', [VendorSettingsController::class, 'updatePassword'])->name('vendor.settings.password');
    Route::post('/vendor/settings/profile', [VendorSettingsController::class, 'updateProfile'])->name('vendor.settings.profile');
    Route::post('/vendor/notifications/{id}/read', [VendorSettingsController::class, 'markNotificationRead'])->name('vendor.notifications.read');
    Route::post('/vendor/notifications/read-all', [VendorSettingsController::class, 'markAllNotificationsRead'])->name('vendor.notifications.read-all');
});

// Loyalty/Rewards - Redirect to rewards page
Route::get('/loyalty', [RewardController::class, 'index'])->name('loyalty')->middleware('auth');

Route::post('/login', function () {
    // Login logic will be added later
    return redirect()->route('home');
})->name('login.post');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::post('/register', function () {
    // Registration logic will be added later
    return redirect()->route('home');
})->name('register.post');

Route::post('/logout', function () {
    // Logout logic will be added later
    return redirect()->route('home');
})->name('logout');

// Additional Pages
Route::get('/about', function () {
    return view('home'); // You can create a separate about page later
})->name('about');

require __DIR__.'/auth.php';
require __DIR__.'/test-patterns.php';
