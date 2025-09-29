<?php

namespace Jiny\Admin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Jiny\Admin\Models\User;
use Carbon\Carbon;

/**
 * 관리자 계정 잠금 해제 토큰 모델
 * 
 * 보안 정책에 의해 잠긴 계정을 안전하게 해제하기 위한 일회용 토큰을 관리합니다.
 * 토큰은 SHA256 해시로 저장되며, 60분 후 자동 만료됩니다.
 * 
 * @property int $id
 * @property int $user_id 잠금 해제 대상 사용자 ID
 * @property string $token SHA256 해시로 저장된 보안 토큰
 * @property \Carbon\Carbon $expires_at 토큰 만료 시간
 * @property \Carbon\Carbon|null $used_at 토큰 사용 시간
 * @property \Carbon\Carbon|null $expired_at 수동 만료 처리 시간
 * @property int $attempts 잠금 해제 시도 횟수 (최대 5회)
 * @property string|null $ip_address 토큰 생성 요청 IP 주소
 * @property string|null $user_agent 토큰 생성 요청 User Agent
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property-read User $user 연관된 사용자
 * 
 * @package Jiny\Admin
 * @since 1.0.0
 */
class AdminUnlockToken extends Model
{
    use HasFactory;

    /**
     * 테이블 이름
     *
     * @var string
     */
    protected $table = 'admin_unlock_tokens';

    /**
     * 대량 할당 가능한 속성
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'token',
        'expires_at',
        'used_at',
        'expired_at',
        'attempts',
        'ip_address',
        'user_agent',
    ];

    /**
     * 타입 캐스팅
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'expired_at' => 'datetime',
        'attempts' => 'integer',
    ];

    /**
     * 토큰 유효 시간 (분)
     */
    const TOKEN_EXPIRY_MINUTES = 60;

    /**
     * 최대 시도 횟수
     */
    const MAX_ATTEMPTS = 5;

    /**
     * 연관된 사용자
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 토큰이 만료되었는지 확인
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast() || $this->expired_at !== null;
    }

    /**
     * 토큰이 사용되었는지 확인
     *
     * @return bool
     */
    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    /**
     * 토큰이 유효한지 확인
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return !$this->isUsed() && 
               !$this->isExpired() && 
               $this->attempts < self::MAX_ATTEMPTS;
    }

    /**
     * 토큰 사용 처리
     *
     * @return void
     */
    public function markAsUsed(): void
    {
        $this->update([
            'used_at' => now(),
        ]);
    }

    /**
     * 토큰 수동 만료 처리
     *
     * @return void
     */
    public function expire(): void
    {
        $this->update([
            'expired_at' => now(),
        ]);
    }

    /**
     * 시도 횟수 증가
     *
     * @return void
     */
    public function incrementAttempts(): void
    {
        $this->increment('attempts');
        
        // 최대 시도 횟수 초과 시 자동 사용 처리
        if ($this->attempts >= self::MAX_ATTEMPTS) {
            $this->markAsUsed();
        }
    }

    /**
     * 남은 시도 횟수 반환
     *
     * @return int
     */
    public function remainingAttempts(): int
    {
        return max(0, self::MAX_ATTEMPTS - $this->attempts);
    }

    /**
     * 토큰 생성 (해시로 저장)
     *
     * @param int $userId
     * @param string $rawToken
     * @param string|null $ipAddress
     * @param string|null $userAgent
     * @return static
     */
    public static function createToken(
        int $userId, 
        string $rawToken, 
        ?string $ipAddress = null, 
        ?string $userAgent = null
    ): self {
        return static::create([
            'user_id' => $userId,
            'token' => hash('sha256', $rawToken),
            'expires_at' => Carbon::now()->addMinutes(self::TOKEN_EXPIRY_MINUTES),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * 토큰 검증 및 조회
     *
     * @param string $rawToken
     * @return static|null
     */
    public static function findValidToken(string $rawToken): ?self
    {
        $hashedToken = hash('sha256', $rawToken);
        
        $token = static::where('token', $hashedToken)
            ->whereNull('used_at')
            ->whereNull('expired_at')
            ->where('expires_at', '>', now())
            ->where('attempts', '<', self::MAX_ATTEMPTS)
            ->first();
        
        return $token;
    }

    /**
     * 사용자의 미사용 토큰 모두 만료 처리
     *
     * @param int $userId
     * @param int|null $exceptId 제외할 토큰 ID
     * @return int 만료 처리된 토큰 수
     */
    public static function expireUserTokens(int $userId, ?int $exceptId = null): int
    {
        $query = static::where('user_id', $userId)
            ->whereNull('used_at')
            ->whereNull('expired_at');
        
        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }
        
        return $query->update(['expired_at' => now()]);
    }

    /**
     * 만료된 토큰 정리
     *
     * @param int $days 보관 일수
     * @return int 삭제된 토큰 수
     */
    public static function cleanupOldTokens(int $days = 30): int
    {
        return static::where(function ($query) use ($days) {
            $query->where('created_at', '<', Carbon::now()->subDays($days))
                  ->orWhere('expires_at', '<', Carbon::now()->subDays($days));
        })->delete();
    }

    /**
     * 최근 토큰 발송 확인 (스팸 방지)
     *
     * @param int $userId
     * @param int $minutes 확인할 시간 범위 (분)
     * @return bool
     */
    public static function hasRecentToken(int $userId, int $minutes = 5): bool
    {
        return static::where('user_id', $userId)
            ->where('created_at', '>=', Carbon::now()->subMinutes($minutes))
            ->whereNull('used_at')
            ->exists();
    }
}