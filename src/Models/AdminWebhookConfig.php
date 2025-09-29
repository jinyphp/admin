<?php

namespace Jiny\Admin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Webhook 설정 모델
 * 
 * @property int $id
 * @property string $name
 * @property string $url
 * @property string $method
 * @property array|null $headers
 * @property array $events
 * @property string|null $secret
 * @property bool $is_active
 * @property int $retry_times
 * @property int $timeout
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class AdminWebhookConfig extends Model
{
    use HasFactory;

    protected $table = 'admin_webhook_configs';

    protected $fillable = [
        'name',
        'url',
        'method',
        'headers',
        'events',
        'secret',
        'is_active',
        'retry_times',
        'timeout',
        'metadata',
    ];

    protected $casts = [
        'headers' => 'array',
        'events' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'retry_times' => 'integer',
        'timeout' => 'integer',
    ];

    protected $attributes = [
        'method' => 'POST',
        'is_active' => true,
        'retry_times' => 3,
        'timeout' => 30,
    ];

    /**
     * Webhook 로그들
     */
    public function logs(): HasMany
    {
        return $this->hasMany(AdminWebhookLog::class, 'config_id');
    }

    /**
     * 특정 이벤트를 구독하는지 확인
     */
    public function subscribesToEvent(string $event): bool
    {
        return in_array($event, $this->events ?? []);
    }

    /**
     * 활성화된 Webhook만 조회
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}