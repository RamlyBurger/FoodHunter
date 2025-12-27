<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\UserVoucher;
use App\Patterns\Factory\VoucherFactory;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Vendor Voucher API Controller - Lee Kin Hang
 * 
 * Design Pattern: Factory Pattern (for voucher discount calculation)
 * 
 * Web Service: Exposes voucher validation API for Cart module (Lee Song Yan)
 */
class VoucherController extends Controller
{
    use ApiResponse;

    /**
     * List all vouchers for the vendor
     */
    public function index(Request $request): JsonResponse
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return $this->notFoundResponse('Vendor profile not found');
        }

        $vouchers = Voucher::where('vendor_id', $vendor->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($voucher) => $this->formatVoucher($voucher));

        return $this->successResponse($vouchers);
    }

    /**
     * Store a new voucher
     */
    public function store(Request $request): JsonResponse
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return $this->notFoundResponse('Vendor profile not found');
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:20|unique:vouchers,code',
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0.01',
            'min_order' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'per_user_limit' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $code = $request->code ?: strtoupper(Str::random(8));

        $voucher = Voucher::create([
            'vendor_id' => $vendor->id,
            'code' => strtoupper($code),
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'value' => $request->value,
            'min_order' => $request->min_order ?? 0,
            'max_discount' => $request->max_discount,
            'usage_limit' => $request->usage_limit,
            'per_user_limit' => $request->per_user_limit ?? 1,
            'expires_at' => $request->expires_at,
            'is_active' => true,
        ]);

        return $this->createdResponse(
            $this->formatVoucher($voucher),
            'Voucher created successfully'
        );
    }

    /**
     * Update a voucher
     */
    public function update(Request $request, Voucher $voucher): JsonResponse
    {
        $vendor = $request->user()->vendor;

        if (!$vendor || $voucher->vendor_id !== $vendor->id) {
            return $this->forbiddenResponse('Unauthorized');
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0.01',
            'min_order' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'per_user_limit' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);

        $voucher->update([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'value' => $request->value,
            'min_order' => $request->min_order ?? 0,
            'max_discount' => $request->max_discount,
            'usage_limit' => $request->usage_limit,
            'per_user_limit' => $request->per_user_limit ?? 1,
            'expires_at' => $request->expires_at,
            'is_active' => $request->is_active ?? $voucher->is_active,
        ]);

        return $this->successResponse(
            $this->formatVoucher($voucher->fresh()),
            'Voucher updated successfully'
        );
    }

    /**
     * Delete a voucher
     */
    public function destroy(Request $request, Voucher $voucher): JsonResponse
    {
        $vendor = $request->user()->vendor;

        if (!$vendor || $voucher->vendor_id !== $vendor->id) {
            return $this->forbiddenResponse('Unauthorized');
        }

        $voucher->delete();

        return $this->successResponse(null, 'Voucher deleted successfully');
    }

    /**
     * Web Service: Expose - Validate Voucher API
     * 
     * Lee Song Yan (Cart module) consumes this to validate vouchers during checkout.
     * Uses Factory Pattern to calculate discounts based on voucher type.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $code = strtoupper(trim($request->code));
        $subtotal = (float) $request->subtotal;

        // Find voucher by code
        $voucher = Voucher::where('code', $code)->first();

        if (!$voucher) {
            return $this->errorResponse('Voucher not found', 404, 'VOUCHER_NOT_FOUND');
        }

        // Check if voucher is active
        if (!$voucher->is_active) {
            return $this->errorResponse('Voucher is inactive', 400, 'VOUCHER_INACTIVE');
        }

        // Check if voucher has expired
        if ($voucher->expires_at && now()->gt($voucher->expires_at)) {
            return $this->errorResponse('Voucher has expired', 400, 'VOUCHER_EXPIRED');
        }

        // Check if voucher has started
        if ($voucher->starts_at && now()->lt($voucher->starts_at)) {
            return $this->errorResponse('Voucher is not yet active', 400, 'VOUCHER_NOT_STARTED');
        }

        // Check usage limit
        if ($voucher->usage_limit && $voucher->usage_count >= $voucher->usage_limit) {
            return $this->errorResponse('Voucher usage limit reached', 400, 'USAGE_LIMIT_REACHED');
        }

        // Check minimum order requirement using Factory Pattern
        if (!VoucherFactory::isApplicable($voucher, $subtotal)) {
            return $this->errorResponse(
                'Voucher not applicable',
                400,
                'MIN_ORDER_NOT_MET',
                [
                    'min_order_required' => (float) $voucher->min_order,
                    'current_subtotal' => $subtotal,
                ]
            );
        }

        // Check if user has redeemed this voucher (if authenticated)
        $user = $request->user();
        if ($user) {
            $userVoucher = UserVoucher::where('user_id', $user->id)
                ->where('voucher_id', $voucher->id)
                ->first();

            if (!$userVoucher) {
                return $this->errorResponse(
                    'You have not redeemed this voucher',
                    400,
                    'VOUCHER_NOT_REDEEMED'
                );
            }

            if ($userVoucher->usage_count >= $voucher->per_user_limit) {
                return $this->errorResponse(
                    'You have already used this voucher',
                    400,
                    'USER_LIMIT_REACHED'
                );
            }
        }

        // Calculate discount using Factory Pattern
        $discount = VoucherFactory::calculateDiscount($voucher, $subtotal);
        $description = VoucherFactory::getDescription($voucher);

        return $this->successResponse([
            'voucher_id' => $voucher->id,
            'code' => $voucher->code,
            'type' => $voucher->type,
            'value' => (float) $voucher->value,
            'discount' => round($discount, 2),
            'description' => $description,
            'min_order' => (float) ($voucher->min_order ?? 0),
            'expires_at' => $voucher->expires_at?->toIso8601String(),
        ], 'Voucher is valid');
    }

    /**
     * Format voucher for API response
     */
    private function formatVoucher(Voucher $voucher): array
    {
        return [
            'id' => $voucher->id,
            'code' => $voucher->code,
            'name' => $voucher->name,
            'description' => $voucher->description,
            'type' => $voucher->type,
            'value' => (float) $voucher->value,
            'min_order' => (float) ($voucher->min_order ?? 0),
            'max_discount' => $voucher->max_discount ? (float) $voucher->max_discount : null,
            'usage_limit' => $voucher->usage_limit,
            'usage_count' => $voucher->usage_count,
            'per_user_limit' => $voucher->per_user_limit,
            'starts_at' => $voucher->starts_at?->toIso8601String(),
            'expires_at' => $voucher->expires_at?->toIso8601String(),
            'is_active' => $voucher->is_active,
            'is_valid' => $voucher->isValid(),
            'created_at' => $voucher->created_at->toIso8601String(),
        ];
    }
}
