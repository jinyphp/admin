<?php

namespace Jiny\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;

class AdminIpWhitelist extends Model
{
    use HasFactory;

    /**
     * 테이블명
     *
     * @var string
     */
    protected $table = 'admin_ip_whitelist';

    /**
     * 대량 할당 가능한 속성
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ip_address',
        'description',
        'type',
        'ip_range_start',
        'ip_range_end',
        'cidr_prefix',
        'is_active',
        'added_by',
        'expires_at',
        'access_count',
        'last_accessed_at',
        'metadata',
    ];

    /**
     * 속성 캐스팅
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
        'last_accessed_at' => 'datetime',
        'metadata' => 'array',
        'access_count' => 'integer',
        'cidr_prefix' => 'integer',
    ];

    /**
     * 기본 속성값
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'type' => 'single',
        'is_active' => true,
        'access_count' => 0,
    ];

    /**
     * 활성화된 IP만 조회
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * 만료된 IP 조회
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * IP가 현재 활성 상태인지 확인
     *
     * @return bool
     */
    public function isActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * IP 주소가 이 엔트리와 일치하는지 확인
     *
     * @param string $ip
     * @return bool
     */
    public function matchesIp(string $ip): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        switch ($this->type) {
            case 'single':
                return $ip === $this->ip_address;
                
            case 'range':
                return $this->isIpInRange($ip);
                
            case 'cidr':
                return $this->isIpInCidr($ip);
                
            default:
                return false;
        }
    }

    /**
     * IP가 범위 내에 있는지 확인
     *
     * @param string $ip
     * @return bool
     */
    private function isIpInRange(string $ip): bool
    {
        if (!$this->ip_range_start || !$this->ip_range_end) {
            return false;
        }

        $ipLong = ip2long($ip);
        $startLong = ip2long($this->ip_range_start);
        $endLong = ip2long($this->ip_range_end);

        if ($ipLong === false || $startLong === false || $endLong === false) {
            return false;
        }

        return $ipLong >= $startLong && $ipLong <= $endLong;
    }

    /**
     * IP가 CIDR 범위 내에 있는지 확인
     *
     * @param string $ip
     * @return bool
     */
    private function isIpInCidr(string $ip): bool
    {
        if (!$this->cidr_prefix) {
            return false;
        }

        $ipLong = ip2long($ip);
        $baseLong = ip2long($this->ip_address);

        if ($ipLong === false || $baseLong === false) {
            return false;
        }

        $mask = -1 << (32 - $this->cidr_prefix);
        return ($ipLong & $mask) === ($baseLong & $mask);
    }

    /**
     * 접근 정보 업데이트
     *
     * @return void
     */
    public function recordAccess(): void
    {
        $this->increment('access_count');
        $this->update(['last_accessed_at' => now()]);
    }

    /**
     * 캐시 초기화
     *
     * @return void
     */
    public static function clearCache(): void
    {
        Cache::forget('admin_ip_whitelist');
    }

    /**
     * 모델 저장 후 캐시 초기화
     */
    protected static function booted()
    {
        static::saved(function () {
            self::clearCache();
        });

        static::deleted(function () {
            self::clearCache();
        });
    }
}