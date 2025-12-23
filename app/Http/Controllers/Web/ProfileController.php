<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EmailVerification;
use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * Profile Controller - Student 1
 * 
 * Handles user profile management including email change with Supabase OTP verification.
 */
class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Redirect vendors to vendor-specific profile page
        if ($user->isVendor() && $user->vendor) {
            $vendor = $user->vendor;
            $stats = [
                'total_orders' => $vendor->total_orders,
                'total_revenue' => \App\Models\Order::where('vendor_id', $vendor->id)->where('status', 'completed')->sum('total'),
                'menu_items' => $vendor->menuItems()->count(),
                'active_vouchers' => \App\Models\Voucher::where('vendor_id', $vendor->id)->where('is_active', true)->count(),
            ];
            return view('profile.vendor', compact('user', 'vendor', 'stats'));
        }
        
        return view('profile.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        
        // Handle vendor-specific updates
        if ($request->has('is_vendor') && $user->isVendor() && $user->vendor) {
            $request->validate([
                'store_name' => 'nullable|string|max:100',
                'phone' => 'nullable|string|max:20',
                'description' => 'nullable|string|max:1000',
                'min_order_amount' => 'nullable|numeric|min:0',
                'avg_prep_time' => 'nullable|integer|min:1|max:120',
            ]);

            $user->vendor->update([
                'store_name' => $request->store_name ?? $user->vendor->store_name,
                'phone' => $request->phone,
                'description' => $request->description,
                'min_order_amount' => $request->min_order_amount ?? 0,
                'avg_prep_time' => $request->avg_prep_time ?? 15,
            ]);

            return back()->with('success', 'Store information updated successfully.');
        }

        // Regular user update
        $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
        ]);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $user = Auth::user();

        // Delete old avatar if exists
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Store new avatar
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        return back()->with('success', 'Profile picture updated successfully.');
    }

    public function removeAvatar()
    {
        $user = Auth::user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->update(['avatar' => null]);

        return back()->with('success', 'Profile picture removed.');
    }

    /**
     * Update password with OWASP complexity requirements
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            // OWASP [38-39]: Password complexity - min 8 chars, mixed case, numbers, symbols
            'password' => [
                'required',
                'confirmed',
                \Illuminate\Validation\Rules\Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password changed successfully.');
    }

    public function requestEmailChange(Request $request)
    {
        $request->validate([
            'new_email' => 'required|email|unique:users,email',
            'password' => 'required',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password.']);
        }

        // Store pending email
        $user->update(['pending_email' => strtolower($request->new_email)]);

        // Supabase will send OTP via frontend JS on the verify-email page
        return redirect()->route('profile.verify-email')->with('success', 'Please verify your new email address.');
    }

    public function showVerifyEmail()
    {
        $user = Auth::user();
        if (!$user->pending_email) {
            return redirect()->route('profile.index')->with('error', 'No pending email change.');
        }

        return view('profile.verify-email', ['email' => $user->pending_email]);
    }

    public function verifyEmailChange(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        if (!$user->pending_email) {
            return redirect()->route('profile.index')->with('error', 'No pending email change.');
        }

        // Verify OTP with Supabase (type='magiclink' for email change)
        $supabaseService = app(SupabaseService::class);
        $result = $supabaseService->verifyOtp($user->pending_email, $request->code, 'magiclink');

        if (!$result['success']) {
            return back()->withErrors(['code' => $result['message']]);
        }

        // Update email
        $user->update([
            'email' => $user->pending_email,
            'pending_email' => null,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('profile.index')->with('success', 'Your email has been updated successfully!');
    }

    public function cancelEmailChange()
    {
        Auth::user()->update(['pending_email' => null]);
        EmailVerification::where('user_id', Auth::id())->where('type', 'email_change')->delete();

        return redirect()->route('profile.index')->with('success', 'Email change cancelled.');
    }
}
