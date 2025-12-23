<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailVerification extends Model
{
    protected $fillable = [
        'email',
        'code',
        'type',
        'user_id',
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function generateCode(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public static function createForSignup(string $email): self
    {
        // Delete any existing codes for this email
        static::where('email', $email)->where('type', 'signup')->delete();

        return static::create([
            'email' => $email,
            'code' => static::generateCode(),
            'type' => 'signup',
            'expires_at' => now()->addMinutes(15),
        ]);
    }

    public static function createForEmailChange(int $userId, string $newEmail): self
    {
        // Delete any existing codes for this user
        static::where('user_id', $userId)->where('type', 'email_change')->delete();

        return static::create([
            'email' => $newEmail,
            'code' => static::generateCode(),
            'type' => 'email_change',
            'user_id' => $userId,
            'expires_at' => now()->addMinutes(15),
        ]);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function verify(): bool
    {
        if ($this->isExpired() || $this->isVerified()) {
            return false;
        }

        $this->update(['verified_at' => now()]);
        return true;
    }

    public static function findValidCode(string $email, string $code, string $type): ?self
    {
        return static::where('email', $email)
            ->where('code', $code)
            ->where('type', $type)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->first();
    }
}
