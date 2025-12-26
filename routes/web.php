<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\MenuController;
use App\Http\Controllers\Web\CartController;
use App\Http\Controllers\Web\OrderController;
use App\Http\Controllers\Web\VendorController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\NotificationController;
use App\Http\Controllers\Web\WishlistController;
use App\Http\Controllers\Web\VendorReportController;
use App\Http\Controllers\Web\SecurityTestController;
use App\Http\Controllers\Web\VoucherController;
use App\Http\Controllers\Web\ForgotPasswordController;

/*
|--------------------------------------------------------------------------
| Web Routes - FoodHunter
|--------------------------------------------------------------------------
*/

// Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Menu Browsing (Public)
Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');
Route::get('/menu/{item}', [MenuController::class, 'show'])->name('menu.show');
Route::get('/vendors/{vendor}', [MenuController::class, 'vendor'])->name('menu.vendor');

// Guest routes (login, register)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // Email Verification
    Route::get('/verify', [AuthController::class, 'showVerify'])->name('verify.show');
    Route::post('/verify', [AuthController::class, 'verify'])->name('verify.submit');
    Route::post('/verify/resend', [AuthController::class, 'resendCode'])->name('verify.resend');
    Route::post('/verify/resend-ajax', [AuthController::class, 'resendCodeAjax'])->name('verify.resend-ajax');
    Route::post('/verify/mark-sent', [AuthController::class, 'markOtpSent'])->name('verify.mark-sent');

    // Google OAuth
    Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

    // Password Reset (Supabase OTP)
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/password-verify', [ForgotPasswordController::class, 'showVerifyForm'])->name('password.verify');
    Route::post('/password-verify', [ForgotPasswordController::class, 'verifyOtp'])->name('password.verify.submit');
    Route::get('/reset-password', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset.form');
    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Routes (Require Authentication - All Users)
Route::middleware('auth')->group(function () {
    // Profile (accessible by both customers and vendors)
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::get('/profile/avatar/remove', [ProfileController::class, 'removeAvatar'])->name('profile.avatar.remove');
    Route::post('/profile/change-email', [ProfileController::class, 'requestEmailChange'])->name('profile.change-email');
    Route::get('/profile/verify-email', [ProfileController::class, 'showVerifyEmail'])->name('profile.verify-email');
    Route::post('/profile/verify-email', [ProfileController::class, 'verifyEmailChange'])->name('profile.verify-email.submit');
    Route::post('/profile/cancel-email-change', [ProfileController::class, 'cancelEmailChange'])->name('profile.cancel-email-change');

    // Change Password with OTP
    Route::get('/auth/change-password', [AuthController::class, 'showChangePassword'])->name('auth.change-password');
    Route::post('/auth/change-password/request', [AuthController::class, 'requestPasswordChange']);
    Route::get('/auth/change-password/verify', [AuthController::class, 'showChangePasswordVerify'])->name('auth.change-password.verify');
    Route::post('/auth/change-password/verify', [AuthController::class, 'verifyPasswordChange']);
    Route::post('/auth/change-password/resend-otp', [AuthController::class, 'resendPasswordChangeOtp']);

    // Change Email with OTP
    Route::get('/auth/change-email', [AuthController::class, 'showChangeEmail'])->name('auth.change-email');
    Route::post('/auth/change-email/request', [AuthController::class, 'requestEmailChange']);
    Route::get('/auth/change-email/verify', [AuthController::class, 'showChangeEmailVerify'])->name('auth.change-email.verify');
    Route::post('/auth/change-email/verify', [AuthController::class, 'verifyEmailChange']);
    Route::post('/auth/change-email/resend-otp', [AuthController::class, 'resendEmailChangeOtp']);

    // Notifications (accessible by both customers and vendors)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});

// Customer-Only Routes (Require Authentication + Customer Role)
// OWASP [77-100]: Access Control - Customers only (vendors cannot place orders)
Route::middleware(['auth', 'customer'])->group(function () {
    // Cart
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::put('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

    // Checkout
    Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');
    Route::post('/checkout', [CartController::class, 'processCheckout'])->name('checkout.process');

    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/orders/{order}/reorder', [OrderController::class, 'reorder'])->name('orders.reorder');

    // Vouchers
    Route::get('/vouchers', [VoucherController::class, 'index'])->name('vouchers.index');
    Route::post('/vouchers/{voucher}/redeem', [VoucherController::class, 'redeem'])->name('vouchers.redeem');
    Route::get('/vouchers/my', [VoucherController::class, 'myVouchers'])->name('vouchers.my');
    Route::post('/vouchers/apply', [VoucherController::class, 'apply'])->name('vouchers.apply');
    Route::post('/vouchers/remove', [VoucherController::class, 'remove'])->name('vouchers.remove');

    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::delete('/wishlist/{item}', [WishlistController::class, 'remove'])->name('wishlist.remove');
    Route::get('/wishlist/count', [WishlistController::class, 'count'])->name('wishlist.count');

});

// Vendor Routes (Require Auth + Vendor Role)
// OWASP [77-100]: Access Control - Vendors only
Route::middleware(['auth', 'vendor'])->prefix('vendor')->group(function () {
    Route::get('/dashboard', [VendorController::class, 'dashboard'])->name('vendor.dashboard');
    Route::get('/orders', [VendorController::class, 'orders'])->name('vendor.orders');
    Route::get('/orders/{order}', [VendorController::class, 'orderShow'])->name('vendor.orders.show');
    Route::put('/orders/{order}/status', [VendorController::class, 'updateOrderStatus'])->name('vendor.orders.status');
    Route::get('/menu', [VendorController::class, 'menu'])->name('vendor.menu');
    Route::post('/menu', [VendorController::class, 'menuStore'])->name('vendor.menu.store');
    Route::get('/menu/{menuItem}', [VendorController::class, 'menuShow'])->name('vendor.menu.show');
    Route::put('/menu/{menuItem}', [VendorController::class, 'menuUpdate'])->name('vendor.menu.update');
    Route::delete('/menu/{menuItem}', [VendorController::class, 'menuDestroy'])->name('vendor.menu.destroy');
    Route::post('/toggle-open', [VendorController::class, 'toggleOpen'])->name('vendor.toggle');
    Route::get('/reports', [VendorReportController::class, 'index'])->name('vendor.reports');
    Route::get('/scan', [VendorController::class, 'scanQrCode'])->name('vendor.scan');
    Route::post('/scan/verify', [VendorController::class, 'verifyQrCode'])->name('vendor.scan.verify');
    Route::post('/pickup/{order}/complete', [VendorController::class, 'completePickup'])->name('vendor.pickup.complete');
    Route::post('/orders/{order}/complete-pickup', [VendorController::class, 'completePickupWithQR'])->name('vendor.orders.complete-pickup');
    
    // Voucher Management
    Route::get('/vouchers', [VendorController::class, 'vouchers'])->name('vendor.vouchers');
    Route::post('/vouchers', [VendorController::class, 'voucherStore'])->name('vendor.vouchers.store');
    Route::put('/vouchers/{voucher}', [VendorController::class, 'voucherUpdate'])->name('vendor.vouchers.update');
    Route::delete('/vouchers/{voucher}', [VendorController::class, 'voucherDestroy'])->name('vendor.vouchers.destroy');
    Route::post('/vouchers/{voucher}/toggle', [VendorController::class, 'voucherToggle'])->name('vendor.vouchers.toggle');
    
    // Profile Settings
    Route::post('/toggle-status', [VendorController::class, 'toggleStatus'])->name('vendor.toggle-status');
    Route::post('/update-hours', [VendorController::class, 'updateHours'])->name('vendor.update-hours');
});

// Contact Page
Route::get('/contact', function () {
    return view('contact');
})->name('contact');

Route::post('/contact', function (\Illuminate\Http\Request $request) {
    // In a real app, this would send an email or save to database
    if ($request->ajax() || $request->wantsJson()) {
        return response()->json([
            'success' => true,
            'message' => 'Your message has been sent successfully!'
        ]);
    }
    return redirect()->route('contact')->with('success', 'Your message has been sent successfully!');
})->name('contact.send');


// API Helpers for cart, wishlist, and notifications (using session auth)
Route::middleware('auth')->group(function () {
    Route::get('/api/cart/count', [CartController::class, 'count']);
    Route::get('/api/cart/dropdown', [CartController::class, 'dropdown']);
    Route::get('/api/wishlist/dropdown', [WishlistController::class, 'dropdown']);
    Route::get('/api/notifications/dropdown', [NotificationController::class, 'dropdown']);
});

// Session validity check for single-device login enforcement (no auth middleware - checks manually)
Route::get('/api/auth/session-check', function () {
    if (!auth()->check()) {
        return response()->json(['valid' => false, 'reason' => 'not_authenticated'], 401);
    }
    
    // Verify session exists in database (for single-device login enforcement)
    $sessionId = session()->getId();
    $sessionExists = \DB::table('sessions')
        ->where('id', $sessionId)
        ->where('user_id', auth()->id())
        ->exists();
    
    if (!$sessionExists) {
        // Session was deleted (user logged in from another device)
        \Auth::guard('web')->logout();
        session()->invalidate();
        session()->regenerateToken();
        return response()->json(['valid' => false, 'reason' => 'session_revoked'], 401);
    }
    
    return response()->json(['valid' => true, 'user_id' => auth()->id()]);
});

// Health check
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});

// API Tester page
Route::get('/api-tester', function () {
    return view('api-tester');
})->name('api-tester');

// Security Testing Routes (Development Only)
Route::prefix('security-test')->group(function () {
    Route::get('/', [SecurityTestController::class, 'index'])->name('security.test');
    Route::get('/rate-limiting', [SecurityTestController::class, 'testRateLimiting']);
    Route::get('/generic-errors', [SecurityTestController::class, 'testGenericErrors']);
    Route::get('/session-security', [SecurityTestController::class, 'testSessionSecurity']);
    Route::get('/sql-injection', [SecurityTestController::class, 'testSqlInjection']);
    Route::get('/xss-prevention', [SecurityTestController::class, 'testXssPrevention']);
    Route::get('/csrf-protection', [SecurityTestController::class, 'testCsrfProtection']);
    Route::get('/price-validation', [SecurityTestController::class, 'testPriceValidation']);
    Route::get('/idor-prevention', [SecurityTestController::class, 'testIdorPrevention']);
    Route::get('/qr-signature', [SecurityTestController::class, 'testQrCodeSignature']);
    Route::get('/voucher-generation', [SecurityTestController::class, 'testVoucherGeneration']);
    Route::get('/audit-logging', [SecurityTestController::class, 'testAuditLogging']);
    Route::get('/cors-config', [SecurityTestController::class, 'testCorsConfig']);
    Route::post('/clear-rate-limit', [SecurityTestController::class, 'clearRateLimit']);
    Route::get('/single-device-login', [SecurityTestController::class, 'testSingleDeviceLogin']);
});
