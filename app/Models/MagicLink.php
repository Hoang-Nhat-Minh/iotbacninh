<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MagicLink extends Model
{
    use HasFactory;

    protected $table = 'magic_links';

    protected $fillable = [
        'user_id',
        'token',
        'name',
        'is_active',
        'expires_at',
        'max_uses',
        'used_count',
        'last_used_at',
        'created_by',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    /**
     * Get the user that owns the magic link.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the admin who created the magic link.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: only active links.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Kiểm tra link còn hiệu lực để sử dụng không.
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return false;
        }

        $user = $this->user;
        if (!$user || $user->status !== 'active' || $user->isLocked()) {
            return false;
        }

        return true;
    }

    /**
     * Ghi nhận một lần sử dụng link.
     */
    public function recordUse(string $ip, string $userAgent): void
    {
        $this->increment('used_count');
        $this->update([
            'last_used_at' => now(),
            'ip_address' => $ip,
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * Sinh URL đầy đủ cho magic link.
     */
    public function getUrlAttribute(): string
    {
        return url('/magic-login/' . $this->token);
    }
}