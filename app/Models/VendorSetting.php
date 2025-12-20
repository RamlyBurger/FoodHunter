<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorSetting extends Model
{
    use HasFactory;

    protected $table = 'vendor_settings';
    protected $primaryKey = 'setting_id';

    protected $fillable = [
        'vendor_id',
        'store_name',
        'phone',
        'description',
        'logo_path',
        'accepting_orders',
        'notify_new_orders',
        'notify_order_updates',
        'notify_email',
        'payment_methods',
    ];

    protected $casts = [
        'accepting_orders' => 'boolean',
        'notify_new_orders' => 'boolean',
        'notify_order_updates' => 'boolean',
        'notify_email' => 'boolean',
    ];

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id', 'user_id');
    }

    public function getPaymentMethodsArrayAttribute()
    {
        return explode(',', $this->payment_methods);
    }
}
