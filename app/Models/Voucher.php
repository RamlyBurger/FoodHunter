<?php
/**
 * =============================================================================
 * Voucher Model - Lee Kin Hang (Vendor Management Module)
 * =============================================================================
 * 
 * @author     Lee Kin Hang
 * @module     Vendor Management Module
 * @pattern    Factory Pattern (VoucherFactory)
 * 
 * Represents discount vouchers created by vendors.
 * Discount calculation handled by Factory Pattern (FixedVoucher/PercentageVoucher).
 * =============================================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'code',
        'name',
        'description',
        'type',
        'value',
        'min_order',
        'max_discount',
        'usage_limit',
        'usage_count',
        'per_user_limit',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_order' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_vouchers')
            ->withPivot(['usage_count', 'redeemed_at', 'used_at'])
            ->withTimestamps();
    }

    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->starts_at && now()->lt($this->starts_at)) {
            return false;
        }

        if ($this->expires_at && now()->gt($this->expires_at)) {
            return false;
        }

        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    public function canBeUsedBy(User $user): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        $userVoucher = $this->users()->where('user_id', $user->id)->first();
        
        if (!$userVoucher) {
            return false;
        }

        if ($userVoucher->pivot->usage_count >= $this->per_user_limit) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $orderTotal): float
    {
        if ($this->min_order && $orderTotal < $this->min_order) {
            return 0;
        }

        $discount = 0;

        if ($this->type === 'fixed') {
            $discount = (float)$this->value;
        } else {
            $discount = $orderTotal * ((float)$this->value / 100);
        }

        if ($this->max_discount && $discount > $this->max_discount) {
            $discount = (float)$this->max_discount;
        }

        return min($discount, $orderTotal);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->where(function ($q) {
                $q->whereNull('usage_limit')
                    ->orWhereColumn('usage_count', '<', 'usage_limit');
            });
    }
}
