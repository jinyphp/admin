<?php

namespace Jiny\Admin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminSmsProvider extends Model
{
    use HasFactory;

    protected $table = 'admin_sms_providers';

    protected $fillable = [
        'provider_name',
        'api_key',
        'api_secret',
        'from_number',
        'from_name',
        'config',
        'is_active',
        'is_default',
        'priority',
        'description',
        'sent_count',
        'balance',
        'last_used_at'
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'sent_count' => 'integer',
        'priority' => 'integer',
        'balance' => 'decimal:2',
        'last_used_at' => 'datetime'
    ];
    
    /**
     * SMS 발송 이력과의 관계
     */
    public function sends()
    {
        return $this->hasMany(AdminSmsSend::class, 'provider_id');
    }
}