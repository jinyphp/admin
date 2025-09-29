<?php

namespace Jiny\Admin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminSmsSend extends Model
{
    use HasFactory;

    protected $table = 'admin_sms_sends';

    protected $fillable = [
        'provider_id',
        'provider_name',
        'to_number',
        'to_name',
        'from_number',
        'from_name',
        'message',
        'message_length',
        'message_count',
        'message_id',
        'status',
        'error_code',
        'error_message',
        'cost',
        'currency',
        'response',
        'sent_at',
        'delivered_at',
        'failed_at',
        'sent_by',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'response' => 'array',
        'message_length' => 'integer',
        'message_count' => 'integer',
        'cost' => 'decimal:4',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'failed_at' => 'datetime'
    ];
    
    /**
     * SMS 제공업체와의 관계
     */
    public function provider()
    {
        return $this->belongsTo(AdminSmsProvider::class, 'provider_id');
    }
    
    /**
     * 발송자와의 관계
     */
    public function sender()
    {
        return $this->belongsTo(\App\Models\User::class, 'sent_by');
    }
}