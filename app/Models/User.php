<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $user_id
 * @property string $name
 * @property string $email
 * @property string $role
 * @property string|null $phone
 * 
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MenuItem[] $menuItems
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Order[] $orders
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Order[] $vendorOrders
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CartItem[] $cartItems
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Wishlist[] $wishlists
 * @property-read \App\Models\LoyaltyPoint|null $loyaltyPoints
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\UserRedeemedReward[] $redeemedRewards
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationships
    public function menuItems()
    {
        return $this->hasMany(MenuItem::class, 'vendor_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function vendorOrders()
    {
        return $this->hasMany(Order::class, 'vendor_id');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'user_id');
    }

    /**
     * Get the user's wishlist items
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class, 'user_id');
    }

    public function loyaltyPoints()
    {
        return $this->hasOne(LoyaltyPoint::class, 'user_id');
    }

    public function redeemedRewards()
    {
        return $this->hasMany(UserRedeemedReward::class, 'user_id');
    }
}
