<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorNotification extends Model
{
    use HasFactory;

    protected $table = 'vendor_notifications';
    protected $primaryKey = 'notification_id';

    protected $fillable = [
        'vendor_id',
        'type',
        'title',
        'message',
        'order_id',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id', 'user_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }
}
