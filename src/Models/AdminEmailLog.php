<?php

namespace Jiny\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jiny\Admin\Models\User;

class AdminEmailLog extends Model
{
    use HasFactory;

    protected $table = 'admin_email_logs';

    protected $fillable = [
        'template_id',
        'to_email',
        'to_name',
        'from_email',
        'from_name',
        'subject',
        'body',
        'type',
        'status',
        'cc',
        'bcc',
        'attachments',
        'variables',
        'message_id',
        'error_message',
        'retry_count',
        'sent_at',
        'opened_at',
        'clicked_at',
        'failed_at',
        'bounced_at',
        'clicks',
        'open_count',
        'click_count',
        'ip_address',
        'user_agent',
        'user_id',
        'event_type',
        'metadata'
    ];

    protected $casts = [
        'cc' => 'array',
        'bcc' => 'array',
        'attachments' => 'array',
        'variables' => 'array',
        'clicks' => 'array',
        'metadata' => 'array',
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
        'failed_at' => 'datetime',
        'bounced_at' => 'datetime',
    ];

    /**
     * 템플릿과의 관계
     */
    public function template()
    {
        return $this->belongsTo(AdminEmailTemplate::class, 'template_id');
    }

    /**
     * 사용자와의 관계
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 상태별 스코프
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * 실패한 이메일
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * 발송된 이메일
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * 대기중인 이메일
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * 이메일 재발송 가능 여부
     */
    public function canResend()
    {
        return in_array($this->status, ['failed', 'bounced']) && $this->retry_count < 3;
    }

    /**
     * 이메일 상태 업데이트
     */
    public function markAsSent()
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now()
        ]);
    }

    /**
     * 이메일 실패 처리
     */
    public function markAsFailed($errorMessage = null)
    {
        $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'error_message' => $errorMessage,
            'retry_count' => $this->retry_count + 1
        ]);
    }

    /**
     * 이메일 열람 처리
     */
    public function markAsOpened()
    {
        $this->update([
            'status' => 'opened',
            'opened_at' => $this->opened_at ?: now(),
            'open_count' => $this->open_count + 1
        ]);
    }

    /**
     * 링크 클릭 처리
     */
    public function markAsClicked($url = null)
    {
        $clicks = $this->clicks ?: [];
        if ($url) {
            $clicks[] = [
                'url' => $url,
                'clicked_at' => now()->toDateTimeString()
            ];
        }

        $this->update([
            'status' => 'clicked',
            'clicked_at' => $this->clicked_at ?: now(),
            'click_count' => $this->click_count + 1,
            'clicks' => $clicks
        ]);
    }
}