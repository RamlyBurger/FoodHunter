<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $table = 'cart_items';
    protected $primaryKey = 'cart_id';

    protected $fillable = [
        'user_id',
        'item_id',
        'quantity',
        'special_request',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class, 'item_id');
    }
}
