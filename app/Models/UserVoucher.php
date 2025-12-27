<?php
/**
 * =============================================================================
 * UserVoucher Model - Lee Kin Hang (Vendor Management Module)
 * =============================================================================
 * 
 * @author     Lee Kin Hang
 * @module     Vendor Management Module
 * @pattern    Factory Pattern (VoucherFactory)
 * 
 * Pivot table linking users to redeemed vouchers.
 * Tracks usage count and redemption timestamps.
 * =============================================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'voucher_id',
        'usage_count',
        'redeemed_at',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'redeemed_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }
}
