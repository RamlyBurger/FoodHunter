<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyPoint extends Model
{
    use HasFactory;

    protected $table = 'loyalty_points';
    protected $primaryKey = 'lp_id';

    protected $fillable = [
        'user_id',
        'points',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
