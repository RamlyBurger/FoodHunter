<?php
/**
 * =============================================================================
 * VendorHour Model - Lee Kin Hang (Vendor Management Module)
 * =============================================================================
 * 
 * @author     Lee Kin Hang
 * @module     Vendor Management Module
 * 
 * Stores vendor operating hours for each day of the week.
 * Used to determine if a vendor is currently open for orders.
 * =============================================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorHour extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'day_of_week',
        'open_time',
        'close_time',
        'is_closed',
    ];

    protected function casts(): array
    {
        return [
            'is_closed' => 'boolean',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public static function getDayName(int $dayOfWeek): string
    {
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        return $days[$dayOfWeek] ?? '';
    }
}
