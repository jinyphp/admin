<?php

namespace Jiny\Admin\Traits;

use Jiny\Admin\Services\NotificationService;

/**
 * 이메일 Hook 기능을 제공하는 트레이트
 * 
 * 컨트롤러에서 이 트레이트를 사용하여 메일 발송 전후 처리를 할 수 있습니다.
 */
trait HasEmailHooks
{
    protected $notificationService;

    /**
     * 이메일 Hook 초기화
     */
    protected function initializeEmailHooks()
    {
        $this->notificationService = app(NotificationService::class);
        
        // Hook 등록
        $this->registerEmailHooks();
    }

    /**
     * 이메일 Hook 등록
     */
    protected function registerEmailHooks()
    {
        // 로그인 실패 전 Hook
        $this->notificationService->registerHook('before_send_login_failed', function ($data, $recipient) {
            return $this->hookBeforeLoginFailedEmail($data, $recipient);
        });

        // 로그인 실패 후 Hook
        $this->notificationService->registerHook('after_send_login_failed', function ($data, $recipient, $result) {
            return $this->hookAfterLoginFailedEmail($data, $recipient, $result);
        });

        // 2FA 변경 전 Hook
        $this->notificationService->registerHook('before_send_two_fa_enabled', function ($data, $recipient) {
            return $this->hookBefore2FAEmail($data, $recipient);
        });

        // 2FA 변경 후 Hook
        $this->notificationService->registerHook('after_send_two_fa_enabled', function ($data, $recipient, $result) {
            return $this->hookAfter2FAEmail($data, $recipient, $result);
        });

        // IP 차단 전 Hook
        $this->notificationService->registerHook('before_send_ip_blocked', function ($data, $recipient) {
            return $this->hookBeforeIPBlockedEmail($data, $recipient);
        });

        // IP 차단 후 Hook
        $this->notificationService->registerHook('after_send_ip_blocked', function ($data, $recipient, $result) {
            return $this->hookAfterIPBlockedEmail($data, $recipient, $result);
        });

        // 비밀번호 변경 전 Hook
        $this->notificationService->registerHook('before_send_password_changed', function ($data, $recipient) {
            return $this->hookBeforePasswordChangedEmail($data, $recipient);
        });

        // 비밀번호 변경 후 Hook
        $this->notificationService->registerHook('after_send_password_changed', function ($data, $recipient, $result) {
            return $this->hookAfterPasswordChangedEmail($data, $recipient, $result);
        });
    }

    /**
     * 로그인 실패 이메일 발송 전 Hook
     * 
     * @param array $data 이메일 데이터
     * @param array $recipient 수신자 정보
     * @return bool|array false 반환시 발송 취소, 배열 반환시 데이터 수정
     */
    protected function hookBeforeLoginFailedEmail($data, $recipient)
    {
        // 기본 구현: 그대로 진행
        return true;
    }

    /**
     * 로그인 실패 이메일 발송 후 Hook
     * 
     * @param array $data 이메일 데이터
     * @param array $recipient 수신자 정보
     * @param bool $result 발송 결과
     */
    protected function hookAfterLoginFailedEmail($data, $recipient, $result)
    {
        // 기본 구현: 로깅
        if ($result) {
            \Log::info("Login failed email sent to {$recipient['email']}");
        }
    }

    /**
     * 2FA 설정 이메일 발송 전 Hook
     */
    protected function hookBefore2FAEmail($data, $recipient)
    {
        return true;
    }

    /**
     * 2FA 설정 이메일 발송 후 Hook
     */
    protected function hookAfter2FAEmail($data, $recipient, $result)
    {
        if ($result) {
            \Log::info("2FA change email sent to {$recipient['email']}");
        }
    }

    /**
     * IP 차단 이메일 발송 전 Hook
     */
    protected function hookBeforeIPBlockedEmail($data, $recipient)
    {
        return true;
    }

    /**
     * IP 차단 이메일 발송 후 Hook
     */
    protected function hookAfterIPBlockedEmail($data, $recipient, $result)
    {
        if ($result) {
            \Log::info("IP blocked email sent to {$recipient['email']}");
        }
    }

    /**
     * 비밀번호 변경 이메일 발송 전 Hook
     */
    protected function hookBeforePasswordChangedEmail($data, $recipient)
    {
        return true;
    }

    /**
     * 비밀번호 변경 이메일 발송 후 Hook
     */
    protected function hookAfterPasswordChangedEmail($data, $recipient, $result)
    {
        if ($result) {
            \Log::info("Password changed email sent to {$recipient['email']}");
        }
    }

    /**
     * 커스텀 이메일 발송
     * 
     * @param string $eventType 이벤트 타입
     * @param array $data 데이터
     * @return bool 발송 성공 여부
     */
    protected function sendCustomEmail(string $eventType, array $data): bool
    {
        return $this->notificationService->notify($eventType, $data);
    }

    /**
     * 이메일 템플릿 직접 발송
     * 
     * @param string $templateSlug 템플릿 슬러그
     * @param string $toEmail 수신자 이메일
     * @param array $variables 템플릿 변수
     * @return bool 발송 성공 여부
     */
    protected function sendTemplateEmail(string $templateSlug, string $toEmail, array $variables = []): bool
    {
        try {
            $templateService = app(\Jiny\Admin\App\Services\EmailTemplateService::class);
            $logService = app(\Jiny\Admin\App\Services\EmailLogService::class);
            
            // 템플릿 가져오기
            $template = $templateService->getTemplate($templateSlug);
            if (!$template) {
                \Log::error("Email template not found: {$templateSlug}");
                return false;
            }

            // 템플릿 렌더링
            $rendered = $templateService->render($template, $variables);

            // 로그 생성
            $logId = $logService->createLog([
                'template_id' => $template->id,
                'template_slug' => $template->slug,
                'to_email' => $toEmail,
                'from_email' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
                'subject' => $rendered['subject'],
                'body' => $rendered['body'],
                'variables' => $variables,
                'event_type' => 'custom',
                'user_id' => $variables['user_id'] ?? null
            ]);

            // 메일 발송
            \Illuminate\Support\Facades\Mail::to($toEmail)->send(
                new \Jiny\Admin\Mail\EmailMailable(
                    $rendered['subject'],
                    $rendered['body'],
                    config('mail.from.address'),
                    config('mail.from.name'),
                    $toEmail
                )
            );

            // 발송 성공 기록
            $logService->markAsSent($logId);

            return true;

        } catch (\Exception $e) {
            \Log::error("Failed to send template email: " . $e->getMessage());
            
            // 발송 실패 기록
            if (isset($logId)) {
                $logService->markAsFailed($logId, $e->getMessage());
            }
            
            return false;
        }
    }
}