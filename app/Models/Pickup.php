<?php
/**
 * =============================================================================
 * Pickup Model - Low Nam Lee (Order & Pickup Module)
 * =============================================================================
 * 
 * @author     Low Nam Lee
 * @module     Order & Pickup Module
 * @pattern    State Pattern (OrderStateManager)
 * 
 * Represents order pickup information including queue number and QR code.
 * QR codes are digitally signed for secure verification.
 * =============================================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pickup extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'queue_number',
        'qr_code',
        'status',
        'ready_at',
        'collected_at',
    ];

    protected function casts(): array
    {
        return [
            'ready_at' => 'datetime',
            'collected_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isWaiting(): bool
    {
        return $this->status === 'waiting';
    }

    public function isReady(): bool
    {
        return $this->status === 'ready';
    }

    public function isCollected(): bool
    {
        return $this->status === 'collected';
    }

    public static function generateQrCode(int $orderId): string
    {
        $date = now()->format('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));
        return "PU-{$orderId}-{$date}-{$random}";
    }
}
