<?php

namespace Jiny\Admin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Webhook 로그 모델
 * 
 * @property int $id
 * @property int $config_id
 * @property string $event
 * @property array $payload
 * @property int|null $status_code
 * @property string|null $response
 * @property string $status
 * @property int $attempts
 * @property string|null $error_message
 * @property \Carbon\Carbon|null $sent_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class AdminWebhookLog extends Model
{
    use HasFactory;

    protected $table = 'admin_webhook_logs';

    protected $fillable = [
        'config_id',
        'event',
        'payload',
        'status_code',
        'response',
        'status',
        'attempts',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'status_code' => 'integer',
        'attempts' => 'integer',
        'sent_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'pending',
        'attempts' => 0,
    ];

    /**
     * Webhook 설정
     */
    public function config(): BelongsTo
    {
        return $this->belongsTo(AdminWebhookConfig::class, 'config_id');
    }

    /**
     * 성공 여부 확인
     */
    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    /**
     * 실패 여부 확인
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * 대기 중인 상태 확인
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}