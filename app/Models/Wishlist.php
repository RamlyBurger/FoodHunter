<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    protected $table = 'wishlists';
    protected $primaryKey = 'wishlist_id';
    
    protected $fillable = [
        'user_id',
        'item_id'
    ];

    /**
     * Get the user that owns the wishlist item
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the menu item
     */
    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class, 'item_id', 'item_id');
    }
}
