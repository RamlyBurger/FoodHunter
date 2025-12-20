<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class OrderItem extends Model
{
    use HasFactory;

    protected $table = 'order_items';
    protected $primaryKey = 'order_item_id';

    protected $fillable = [
        'order_id',
        'item_id',
        'quantity',
        'price',
        'special_request',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];
    
    protected $appends = ['subtotal'];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class, 'item_id');
    }
    
    /**
     * Get the subtotal for this order item
     */
    public function getSubtotalAttribute()
    {
        return $this->price * $this->quantity;
    }
}
