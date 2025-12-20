<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRedeemedReward extends Model
{
    use HasFactory;

    protected $table = 'user_redeemed_rewards';
    protected $primaryKey = 'redeemed_id';

    protected $fillable = [
        'user_id',
        'reward_id',
        'voucher_code',
        'is_used',
        'used_at',
    ];

    protected $casts = [
        'redeemed_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reward()
    {
        return $this->belongsTo(Reward::class, 'reward_id');
    }
}
