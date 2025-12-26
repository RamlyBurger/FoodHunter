<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\UserVoucher;
use App\Patterns\Factory\VoucherFactory;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Voucher Controller - Student 5
 * 
 * Uses Factory Pattern to create voucher objects with different discount logic.
 */
class VoucherController extends Controller
{
    use ApiResponse;
    public function index(Request $request)
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

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return $this->successResponse([
                'vouchers' => $vouchers->map(fn($v) => [
                    'id' => $v->id,
                    'code' => $v->code,
                    'name' => $v->name,
                    'description' => $v->description,
                    'type' => $v->type,
                    'value' => (float) $v->value,
                    'min_order' => $v->min_order ? (float) $v->min_order : null,
                    'max_discount' => $v->max_discount ? (float) $v->max_discount : null,
                    'expires_at' => $v->expires_at?->format('Y-m-d'),
                    'vendor' => $v->vendor ? ['id' => $v->vendor->id, 'store_name' => $v->vendor->store_name] : null,
                    'is_redeemed' => isset($userVouchers[$v->id]),
                ]),
            ]);
        }

        return view('vouchers.index', compact('vouchers', 'userVouchers'));
    }

    public function redeem(Request $request, Voucher $voucher)
    {
        $user = Auth::user();

        // Check if voucher is valid
        if (!$voucher->isValid()) {
            if ($request->ajax() || $request->wantsJson()) {
                return $this->errorResponse('This voucher is no longer available.', 400);
            }
            return back()->with('error', 'This voucher is no longer available.');
        }

        // Check if user already has this voucher
        $existingVoucher = UserVoucher::where('user_id', $user->id)
            ->where('voucher_id', $voucher->id)
            ->first();

        if ($existingVoucher) {
            if ($request->ajax() || $request->wantsJson()) {
                return $this->errorResponse('You have already redeemed this voucher.', 400);
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
            return $this->successResponse([
                'voucher' => [
                    'id' => $voucher->id,
                    'code' => $voucher->code,
                    'name' => $voucher->name,
                ],
            ], 'Voucher "' . $voucher->name . '" has been added to your account!');
        }

        return back()->with('success', 'Voucher "' . $voucher->name . '" has been added to your account!');
    }

    public function myVouchers(Request $request)
    {
        $user = Auth::user();

        $userVouchers = UserVoucher::where('user_id', $user->id)
            ->with(['voucher.vendor'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return $this->successResponse([
                'vouchers' => $userVouchers->map(fn($uv) => [
                    'id' => $uv->id,
                    'voucher_id' => $uv->voucher_id,
                    'usage_count' => $uv->usage_count,
                    'redeemed_at' => $uv->redeemed_at?->format('Y-m-d H:i'),
                    'voucher' => $uv->voucher ? [
                        'code' => $uv->voucher->code,
                        'name' => $uv->voucher->name,
                        'type' => $uv->voucher->type,
                        'value' => (float) $uv->voucher->value,
                        'expires_at' => $uv->voucher->expires_at?->format('Y-m-d'),
                        'is_valid' => $uv->voucher->isValid(),
                        'vendor' => $uv->voucher->vendor ? [
                            'id' => $uv->voucher->vendor->id,
                            'store_name' => $uv->voucher->vendor->store_name,
                        ] : null,
                    ] : null,
                ]),
            ]);
        }

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
            return $this->notFoundResponse('Voucher code not found.');
        }

        if (!$voucher->isValid()) {
            return $this->errorResponse('This voucher is no longer valid.', 400);
        }

        // Check if user has this voucher
        $userVoucher = UserVoucher::where('user_id', $user->id)
            ->where('voucher_id', $voucher->id)
            ->first();

        if (!$userVoucher) {
            return $this->errorResponse('You have not redeemed this voucher. Please redeem it first from the Vouchers page.', 400);
        }

        if ($userVoucher->usage_count >= $voucher->per_user_limit) {
            return $this->errorResponse('You have already used this voucher.', 400);
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

        return $this->successResponse([
            'voucher' => [
                'name' => $voucher->name,
                'type' => $voucher->type,
                'value' => $voucher->value,
                'description' => $voucherDescription,
            ],
        ], 'Voucher applied successfully!');
    }

    public function remove()
    {
        session()->forget('applied_voucher');

        return $this->successResponse(null, 'Voucher removed.');
    }
}
