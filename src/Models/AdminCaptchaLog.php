<?php

namespace Jiny\Admin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Captcha 검증 로그 모델
 * 
 * @property int $id
 * @property string $provider
 * @property string $action
 * @property float|null $score
 * @property bool $success
 * @property string $ip_address
 * @property string|null $user_agent
 * @property array|null $response_data
 * @property string|null $error_codes
 * @property int|null $user_id
 * @property string|null $session_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class AdminCaptchaLog extends Model
{
    use HasFactory;

    protected $table = 'admin_captcha_logs';

    protected $fillable = [
        'provider',
        'action',
        'score',
        'success',
        'ip_address',
        'user_agent',
        'response_data',
        'error_codes',
        'user_id',
        'session_id',
    ];

    protected $casts = [
        'success' => 'boolean',
        'score' => 'float',
        'response_data' => 'array',
    ];

    /**
     * 연관된 사용자
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 성공한 검증인지 확인
     */
    public function isSuccess(): bool
    {
        return $this->success === true;
    }

    /**
     * 점수가 임계값 이상인지 확인
     */
    public function isScoreAboveThreshold(float $threshold = 0.5): bool
    {
        return $this->score !== null && $this->score >= $threshold;
    }
}