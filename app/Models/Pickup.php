<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pickup extends Model
{
    use HasFactory;

    protected $table = 'pickups';
    protected $primaryKey = 'pickup_id';

    protected $fillable = [
        'order_id',
        'queue_number',
        'qr_code',
        'status',
    ];
    
    protected $casts = [
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
