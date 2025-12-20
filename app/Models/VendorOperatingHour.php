<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorOperatingHour extends Model
{
    use HasFactory;

    protected $table = 'vendor_operating_hours';
    protected $primaryKey = 'hour_id';

    protected $fillable = [
        'vendor_id',
        'day',
        'opening_time',
        'closing_time',
        'is_open',
    ];

    protected $casts = [
        'is_open' => 'boolean',
    ];

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id', 'user_id');
    }
}
