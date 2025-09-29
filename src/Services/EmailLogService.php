<?php

namespace Jiny\Admin\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * 이메일 발송 로그 관리 서비스
 * 
 * 메일 발송 기록, 상태 추적, 재발송 등의 기능을 제공합니다.
 */
class EmailLogService
{
    /**
     * 메일 발송 로그 생성
     */
    public function createLog(array $data): int
    {
        try {
            return DB::table('admin_email_logs')->insertGetId([
                'template_id' => $data['template_id'] ?? null,
                'template_slug' => $data['template_slug'] ?? null,
                'to_email' => $data['to_email'],
                'to_name' => $data['to_name'] ?? null,
                'cc_emails' => $data['cc_emails'] ?? null,
                'bcc_emails' => $data['bcc_emails'] ?? null,
                'from_email' => $data['from_email'],
                'from_name' => $data['from_name'] ?? null,
                'subject' => $data['subject'],
                'body' => $data['body'],
                'variables' => isset($data['variables']) ? json_encode($data['variables']) : null,
                'attachments' => isset($data['attachments']) ? json_encode($data['attachments']) : null,
                'status' => $data['status'] ?? 'pending',
                'event_type' => $data['event_type'] ?? null,
                'ip_address' => $data['ip_address'] ?? request()->ip(),
                'user_agent' => $data['user_agent'] ?? request()->userAgent(),
                'user_id' => $data['user_id'] ?? null,
                'triggered_by' => $data['triggered_by'] ?? auth()->id(),
                'priority' => $data['priority'] ?? 'normal',
                'queue_name' => $data['queue_name'] ?? null,
                'job_id' => $data['job_id'] ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (Exception $e) {
            Log::error('Failed to create email log: ' . $e->getMessage(), [
                'data' => $data,
                'exception' => $e
            ]);
            throw $e;
        }
    }

    /**
     * 발송 성공 기록
     */
    public function markAsSent(int $logId): bool
    {
        return DB::table('admin_email_logs')
            ->where('id', $logId)
            ->update([
                'status' => 'sent',
                'sent_at' => now(),
                'updated_at' => now()
            ]) > 0;
    }

    /**
     * 발송 실패 기록
     */
    public function markAsFailed(int $logId, string $errorMessage): bool
    {
        $log = DB::table('admin_email_logs')->where('id', $logId)->first();
        
        return DB::table('admin_email_logs')
            ->where('id', $logId)
            ->update([
                'status' => 'failed',
                'error_message' => $errorMessage,
                'failed_at' => now(),
                'retry_count' => ($log->retry_count ?? 0) + 1,
                'updated_at' => now()
            ]) > 0;
    }

    /**
     * 메일 오픈 기록
     */
    public function markAsOpened(int $logId): bool
    {
        $log = DB::table('admin_email_logs')->where('id', $logId)->first();
        
        // 이미 오픈된 경우 중복 기록하지 않음
        if ($log && $log->opened_at) {
            return true;
        }

        return DB::table('admin_email_logs')
            ->where('id', $logId)
            ->update([
                'opened_at' => now(),
                'updated_at' => now()
            ]) > 0;
    }

    /**
     * 링크 클릭 기록
     */
    public function markAsClicked(int $logId): bool
    {
        $log = DB::table('admin_email_logs')->where('id', $logId)->first();
        
        // 이미 클릭된 경우 중복 기록하지 않음
        if ($log && $log->clicked_at) {
            return true;
        }

        return DB::table('admin_email_logs')
            ->where('id', $logId)
            ->update([
                'clicked_at' => now(),
                'updated_at' => now()
            ]) > 0;
    }

    /**
     * 재발송 가능한 실패 메일 조회
     */
    public function getRetryableEmails(int $maxRetries = 3)
    {
        return DB::table('admin_email_logs')
            ->where('status', 'failed')
            ->where('retry_count', '<', $maxRetries)
            ->where('created_at', '>', now()->subDays(7)) // 7일 이내 메일만
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->limit(100)
            ->get();
    }

    /**
     * 특정 사용자의 메일 발송 이력 조회
     */
    public function getUserEmailHistory(int $userId, int $days = 30)
    {
        return DB::table('admin_email_logs')
            ->where('user_id', $userId)
            ->where('created_at', '>', now()->subDays($days))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * 이메일 통계 조회
     */
    public function getStatistics(string $period = 'today'): array
    {
        $query = DB::table('admin_email_logs');

        switch ($period) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->where('created_at', '>', now()->subWeek());
                break;
            case 'month':
                $query->where('created_at', '>', now()->subMonth());
                break;
        }

        $stats = $query->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN opened_at IS NOT NULL THEN 1 ELSE 0 END) as opened,
                SUM(CASE WHEN clicked_at IS NOT NULL THEN 1 ELSE 0 END) as clicked
            ')
            ->first();

        // 템플릿별 통계
        $templateStats = $query->selectRaw('
                template_slug,
                COUNT(*) as count,
                SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent_count
            ')
            ->groupBy('template_slug')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // 시간대별 발송량
        $hourlyStats = DB::table('admin_email_logs')
            ->whereDate('created_at', today())
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        return [
            'summary' => $stats,
            'by_template' => $templateStats,
            'hourly' => $hourlyStats,
            'open_rate' => $stats->sent > 0 ? round(($stats->opened / $stats->sent) * 100, 2) : 0,
            'click_rate' => $stats->opened > 0 ? round(($stats->clicked / $stats->opened) * 100, 2) : 0,
            'failure_rate' => $stats->total > 0 ? round(($stats->failed / $stats->total) * 100, 2) : 0,
        ];
    }

    /**
     * 중복 발송 체크
     */
    public function isDuplicateSend(string $toEmail, string $subject, int $minutes = 5): bool
    {
        $exists = DB::table('admin_email_logs')
            ->where('to_email', $toEmail)
            ->where('subject', $subject)
            ->where('status', 'sent')
            ->where('sent_at', '>', now()->subMinutes($minutes))
            ->exists();

        return $exists;
    }

    /**
     * 스로틀링 체크 (발송 제한)
     */
    public function checkThrottle(string $toEmail, string $eventType = null, int $limit = 10, int $minutes = 60): bool
    {
        $query = DB::table('admin_email_logs')
            ->where('to_email', $toEmail)
            ->where('created_at', '>', now()->subMinutes($minutes));

        if ($eventType) {
            $query->where('event_type', $eventType);
        }

        $count = $query->count();

        return $count < $limit;
    }

    /**
     * 로그 정리 (오래된 로그 삭제)
     */
    public function cleanOldLogs(int $days = 90): int
    {
        return DB::table('admin_email_logs')
            ->where('created_at', '<', now()->subDays($days))
            ->delete();
    }

    /**
     * 특정 로그 상세 조회
     */
    public function getLog(int $logId)
    {
        $log = DB::table('admin_email_logs')->where('id', $logId)->first();
        
        if ($log) {
            // JSON 필드 디코드
            if ($log->variables) {
                $log->variables = json_decode($log->variables, true);
            }
            if ($log->attachments) {
                $log->attachments = json_decode($log->attachments, true);
            }
        }

        return $log;
    }

    /**
     * 발송 큐에 추가
     */
    public function queueEmail(int $logId, string $queueName = 'default', string $jobId = null): bool
    {
        return DB::table('admin_email_logs')
            ->where('id', $logId)
            ->update([
                'queue_name' => $queueName,
                'job_id' => $jobId,
                'status' => 'pending',
                'updated_at' => now()
            ]) > 0;
    }

    /**
     * 바운스 처리
     */
    public function markAsBounced(string $toEmail, string $reason = null): int
    {
        return DB::table('admin_email_logs')
            ->where('to_email', $toEmail)
            ->where('status', 'sent')
            ->where('created_at', '>', now()->subDays(7))
            ->update([
                'status' => 'bounced',
                'error_message' => $reason ?? 'Email bounced',
                'updated_at' => now()
            ]);
    }
}