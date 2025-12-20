<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    use HasFactory;

    protected $table = 'rewards';
    protected $primaryKey = 'reward_id';

    protected $fillable = [
        'title',
        'description',
        'points_required',
    ];

    public function redeemedByUsers()
    {
        return $this->hasMany(UserRedeemedReward::class, 'reward_id');
    }
}
