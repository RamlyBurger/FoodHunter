<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'store_name',
        'slug',
        'description',
        'phone',
        'logo',
        'banner',
        'is_open',
        'is_active',
        'min_order_amount',
        'avg_prep_time',
        'total_orders',
    ];

    protected function casts(): array
    {
        return [
            'is_open' => 'boolean',
            'is_active' => 'boolean',
            'min_order_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function operatingHours(): HasMany
    {
        return $this->hasMany(VendorHour::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOpen($query)
    {
        return $query->where('is_open', true);
    }

    public function isCurrentlyOpen(): bool
    {
        if (!$this->is_open || !$this->is_active) {
            return false;
        }

        $now = now();
        $dayOfWeek = $now->dayOfWeek;
        
        $hours = $this->operatingHours()
            ->where('day_of_week', $dayOfWeek)
            ->where('is_closed', false)
            ->first();

        if (!$hours) {
            return false;
        }

        $currentTime = $now->format('H:i:s');
        return $currentTime >= $hours->open_time && $currentTime <= $hours->close_time;
    }

    public function calculateAvgPrepTime(): int
    {
        $avgMinutes = $this->orders()
            ->where('status', 'completed')
            ->whereNotNull('ready_at')
            ->whereNotNull('confirmed_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, confirmed_at, ready_at)) as avg_time')
            ->value('avg_time');

        return (int) round($avgMinutes ?? $this->avg_prep_time ?? 15);
    }

    public function updateStats(): void
    {
        $this->update([
            'total_orders' => $this->orders()->where('status', 'completed')->count(),
            'avg_prep_time' => $this->calculateAvgPrepTime(),
        ]);
    }
}
