<?php

namespace Jiny\Admin\Models;

use Jiny\Admin\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminUserPassword extends Model
{
    use HasFactory;

    protected $table = 'admin_user_passwords';

    protected $fillable = [
        'user_id',
        'password_hash',
        'changed_at',
        'expires_at',
        'changed_by_ip',
        'changed_by_user_agent',
        'change_reason',
        'is_temporary',
        'is_expired',
        'strength_score',
        'strength_details',
        'last_used_at',
        'usage_count',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
        'is_temporary' => 'boolean',
        'is_expired' => 'boolean',
        'strength_details' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        if (! $this->expires_at) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    public function daysUntilExpiry(): ?int
    {
        if (! $this->expires_at) {
            return null;
        }

        return now()->diffInDays($this->expires_at, false);
    }

    public function markAsExpired(): void
    {
        $this->update(['is_expired' => true]);
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    public function scopeActive($query)
    {
        return $query->where('is_expired', false);
    }

    public function scopeExpired($query)
    {
        return $query->where('is_expired', true);
    }

    public function scopeTemporary($query)
    {
        return $query->where('is_temporary', true);
    }

    public function scopeExpiringSoon($query, $days = 7)
    {
        return $query->where('is_expired', false)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now()->addDays($days));
    }
}
