<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\UserVoucher;
use App\Patterns\Factory\VoucherFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Voucher Controller - Student 5
 * 
 * Uses Factory Pattern to create voucher objects with different discount logic.
 */
class VoucherController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get all active vouchers
        $vouchers = Voucher::active()
            ->with('vendor')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get user's redeemed vouchers
        $userVouchers = UserVoucher::where('user_id', $user->id)
            ->with(['voucher.vendor'])
            ->get()
            ->keyBy('voucher_id');

        return view('vouchers.index', compact('vouchers', 'userVouchers'));
    }

    public function redeem(Request $request, Voucher $voucher)
    {
        $user = Auth::user();

        // Check if voucher is valid
        if (!$voucher->isValid()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This voucher is no longer available.',
                ], 400);
            }
            return back()->with('error', 'This voucher is no longer available.');
        }

        // Check if user already has this voucher
        $existingVoucher = UserVoucher::where('user_id', $user->id)
            ->where('voucher_id', $voucher->id)
            ->first();

        if ($existingVoucher) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already redeemed this voucher.',
                ], 400);
            }
            return back()->with('error', 'You have already redeemed this voucher.');
        }

        // Create user voucher
        UserVoucher::create([
            'user_id' => $user->id,
            'voucher_id' => $voucher->id,
            'redeemed_at' => now(),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Voucher "' . $voucher->name . '" has been added to your account!',
                'voucher' => [
                    'id' => $voucher->id,
                    'code' => $voucher->code,
                    'name' => $voucher->name,
                ],
            ]);
        }

        return back()->with('success', 'Voucher "' . $voucher->name . '" has been added to your account!');
    }

    public function myVouchers()
    {
        $user = Auth::user();

        $userVouchers = UserVoucher::where('user_id', $user->id)
            ->with(['voucher.vendor'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('vouchers.my-vouchers', compact('userVouchers'));
    }

    public function apply(Request $request)
    {
        $request->validate([
            'voucher_code' => 'required|string',
        ]);

        $user = Auth::user();
        $code = strtoupper(trim($request->voucher_code));

        // Find voucher by code
        $voucher = Voucher::where('code', $code)->first();

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher code not found.',
            ], 404);
        }

        if (!$voucher->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'This voucher is no longer valid.',
            ], 400);
        }

        // Check if user has this voucher
        $userVoucher = UserVoucher::where('user_id', $user->id)
            ->where('voucher_id', $voucher->id)
            ->first();

        if (!$userVoucher) {
            return response()->json([
                'success' => false,
                'message' => 'You have not redeemed this voucher. Please redeem it first from the Vouchers page.',
            ], 400);
        }

        if ($userVoucher->usage_count >= $voucher->per_user_limit) {
            return response()->json([
                'success' => false,
                'message' => 'You have already used this voucher.',
            ], 400);
        }

        // Use Factory Pattern to get voucher description
        $voucherDescription = VoucherFactory::getDescription($voucher);

        // Store voucher in session for checkout
        session(['applied_voucher' => [
            'id' => $voucher->id,
            'code' => $voucher->code,
            'name' => $voucher->name,
            'type' => $voucher->type,
            'value' => $voucher->value,
            'min_order' => $voucher->min_order,
            'max_discount' => $voucher->max_discount,
            'vendor_id' => $voucher->vendor_id,
            'description' => $voucherDescription,
        ]]);

        return response()->json([
            'success' => true,
            'message' => 'Voucher applied successfully!',
            'voucher' => [
                'name' => $voucher->name,
                'type' => $voucher->type,
                'value' => $voucher->value,
                'description' => $voucherDescription,
            ],
        ]);
    }

    public function remove()
    {
        session()->forget('applied_voucher');

        return response()->json([
            'success' => true,
            'message' => 'Voucher removed.',
        ]);
    }
}
