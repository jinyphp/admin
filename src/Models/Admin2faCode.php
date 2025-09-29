<?php

namespace Jiny\Admin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 2FA 코드 모델
 * 
 * @property int $id
 * @property int $user_id
 * @property string $code
 * @property string $method
 * @property bool $is_used
 * @property \Carbon\Carbon|null $used_at
 * @property \Carbon\Carbon $expires_at
 * @property string|null $ip_address
 * @property int $attempts
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Admin2faCode extends Model
{
    use HasFactory;

    protected $table = 'admin_2fa_codes';

    protected $fillable = [
        'user_id',
        'code',
        'method',
        'is_used',
        'used_at',
        'expires_at',
        'ip_address',
        'attempts',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
        'attempts' => 'integer',
    ];

    /**
     * 연관된 사용자
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 코드가 만료되었는지 확인
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * 코드가 사용 가능한지 확인
     */
    public function isValid(): bool
    {
        return !$this->is_used && !$this->isExpired();
    }

    /**
     * 코드를 사용 처리
     */
    public function markAsUsed(): void
    {
        $this->update([
            'is_used' => true,
            'used_at' => now(),
        ]);
    }

    /**
     * 시도 횟수 증가
     */
    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }
}