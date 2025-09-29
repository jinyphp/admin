<?php

namespace jiny\admin\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminIpAttempt extends Model
{
    protected $table = 'admin_ip_attempts';
    
    protected $fillable = [
        'ip_address',
        'attempts',
        'last_attempt_at',
        'last_success_at',
        'user_id',
        'is_blocked',
        'blocked_until',
        'metadata'
    ];
    
    protected $casts = [
        'is_blocked' => 'boolean',
        'metadata' => 'array',
        'last_attempt_at' => 'datetime',
        'last_success_at' => 'datetime',
        'blocked_until' => 'datetime'
    ];
    
    /**
     * 관련 사용자
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * 차단 상태 확인
     */
    public function isBlocked(): bool
    {
        if (!$this->is_blocked) {
            return false;
        }
        
        if ($this->blocked_until && $this->blocked_until->isPast()) {
            // 차단 시간이 지났으면 자동 해제
            $this->update([
                'is_blocked' => false,
                'blocked_until' => null,
                'attempts' => 0
            ]);
            return false;
        }
        
        return true;
    }
    
    /**
     * 시도 횟수 증가
     */
    public function incrementAttempts(): void
    {
        $this->increment('attempts');
        $this->update(['last_attempt_at' => now()]);
    }
    
    /**
     * 시도 횟수 리셋
     */
    public function resetAttempts(): void
    {
        $this->update([
            'attempts' => 0,
            'is_blocked' => false,
            'blocked_until' => null
        ]);
    }
    
    /**
     * 성공 기록
     */
    public function recordSuccess(?int $userId = null): void
    {
        $this->update([
            'attempts' => 0,
            'last_success_at' => now(),
            'user_id' => $userId,
            'is_blocked' => false,
            'blocked_until' => null
        ]);
    }
    
    /**
     * IP 차단
     */
    public function block(int $minutes = 60): void
    {
        $this->update([
            'is_blocked' => true,
            'blocked_until' => now()->addMinutes($minutes)
        ]);
    }
}