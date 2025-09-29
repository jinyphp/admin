<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminEmailLogs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Jiny\Admin\Services\JsonConfigService;
use Jiny\Admin\Models\AdminEmailLog;
use Jiny\Admin\Mail\EmailMailable;

/**
 * EmailLogs 관리 메인 컨트롤러 (목록/인덱스 페이지)
 *
 * EmailLogs 목록을 표시하고 관리하는 기능을 제공합니다.
 * Livewire 컴포넌트(AdminTable)와 Hook 패턴을 통해 동작합니다.
 *
 * @package Jiny\Admin\App\Http\Controllers\Admin\AdminEmailLogs
 * @since   1.0.0
 */
class AdminEmailLogs extends Controller
{
    /**
     * JSON 설정 데이터
     *
     * @var array|null
     */
    private $jsonData;

    /**
     * 컨트롤러 생성자
     *
     * AdminEmailLogs.json 설정 파일을 로드하여 컨트롤러를 초기화합니다.
     */
    public function __construct()
    {
        // 서비스를 사용하여 JSON 파일 로드
        $jsonConfigService = new JsonConfigService;
        $this->jsonData = $jsonConfigService->loadFromControllerPath(__DIR__);
    }

    /**
     * EmailLogs 목록 페이지 표시
     *
     * @param  Request  $request  HTTP 요청 객체
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        // JSON 데이터 확인
        if (! $this->jsonData) {
            return response('Error: JSON configuration file not found or invalid.', 500);
        }

        // template.index view 경로 확인
        if (! isset($this->jsonData['template']['index'])) {
            return response('Error: 화면을 출력하기 위한 template.index 설정이 필요합니다.', 500);
        }

        // route 정보를 jsonData에 추가
        if (isset($this->jsonData['route']['name'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route']['name'];
        } elseif (isset($this->jsonData['route']) && is_string($this->jsonData['route'])) {
            // 이전 버전 호환성
            $this->jsonData['currentRoute'] = $this->jsonData['route'];
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__.DIRECTORY_SEPARATOR.'AdminEmailLogs.json';
        $settingsPath = $jsonPath; // settings drawer를 위한 경로

        // 컨트롤러 클래스를 JSON 데이터에 추가
        $this->jsonData['controllerClass'] = get_class($this);

        return view($this->jsonData['template']['index'], [
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'controllerClass' => static::class,
        ]);
    }

    /**
     * Hook: Livewire 컴포넌트의 데이터 조회 전 실행
     * 데이터베이스 쿼리 조건을 수정하거나 추가 로직을 실행할 수 있습니다.
     *
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @return false|mixed false 반환시 정상 진행, 다른 값 반환시 해당 값이 출력됨
     */
    public function hookIndexing($wire)
    {
        return false;
    }

    /**
     * Hook: 데이터 조회 후 실행
     * 조회된 데이터를 가공하거나 추가 처리를 할 수 있습니다.
     *
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @param mixed $rows 조회된 데이터
     * @return mixed 가공된 데이터
     */
    public function hookIndexed($wire, $rows)
    {
        return $rows;
    }

    /**
     * Hook: 테이블 헤더 커스터마이징
     *
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @return array 커스터마이징된 헤더 설정
     */
    public function hookTableHeader($wire)
    {
        return $this->jsonData['index']['table']['columns'] ?? [];
    }

    /**
     * Hook: 페이지네이션 설정
     *
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @return array 페이지네이션 설정
     */
    public function hookPagination($wire)
    {
        return $this->jsonData['index']['pagination'] ?? [
            'perPage' => 10,
            'perPageOptions' => [10, 25, 50, 100]
        ];
    }

    /**
     * Hook: 정렬 설정
     *
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @return array 정렬 설정
     */
    public function hookSorting($wire)
    {
        return $this->jsonData['index']['sorting'] ?? [
            'default' => 'created_at',
            'direction' => 'desc'
        ];
    }

    /**
     * Hook: 검색 설정
     *
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @return array 검색 설정
     */
    public function hookSearch($wire)
    {
        return $this->jsonData['index']['search'] ?? [
            'placeholder' => 'Search emaillogss...',
            'debounce' => 300
        ];
    }

    /**
     * Hook: 필터 설정
     *
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @return array 필터 설정
     */
    public function hookFilters($wire)
    {
        return $this->jsonData['index']['filters'] ?? [];
    }

    /**
     * Hook: 이메일 발송
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  array  $params  파라미터
     * @return mixed
     */
    public function hookCustomSend($wire, $params = [])
    {
        $id = $params['id'] ?? null;

        if (!$id) {
            $wire->dispatch('showError', message: 'ID가 필요합니다.');
            return false;
        }

        $emailLog = AdminEmailLog::find($id);

        if (!$emailLog) {
            $wire->dispatch('showError', message: '이메일 로그를 찾을 수 없습니다.');
            return false;
        }

        if ($emailLog->status !== 'pending') {
            $wire->dispatch('showError', message: '대기중 상태의 이메일만 발송할 수 있습니다.');
            return false;
        }

        // 메일 설정 로드
        $adminMailConfig = $this->loadMailConfig();

        // 트래킹 토큰 생성
        $trackingToken = bin2hex(random_bytes(32));
        $emailLog->tracking_token = $trackingToken;
        $emailLog->status = 'processing';
        $emailLog->save();

        try {
            // EmailMailable 사용하여 메일 발송 (트래킹 토큰 포함)
            $mailable = new EmailMailable(
                $emailLog->subject,
                $emailLog->body,
                $emailLog->from_email ?: $adminMailConfig['from_address'],
                $emailLog->from_name ?: $adminMailConfig['from_name'],
                $emailLog->to_email,
                $trackingToken,  // 트래킹 토큰
                true  // 트래킹 활성화
            );

            // CC/BCC 처리
            if ($emailLog->cc) {
                $cc = is_string($emailLog->cc) ? json_decode($emailLog->cc, true) : $emailLog->cc;
                if (is_array($cc)) {
                    foreach ($cc as $email) {
                        $mailable->cc($email);
                    }
                }
            }

            if ($emailLog->bcc) {
                $bcc = is_string($emailLog->bcc) ? json_decode($emailLog->bcc, true) : $emailLog->bcc;
                if (is_array($bcc)) {
                    foreach ($bcc as $email) {
                        $mailable->bcc($email);
                    }
                }
            }

            // 메일 발송
            Mail::to($emailLog->to_email, $emailLog->to_name)->send($mailable);

            $emailLog->status = 'sent';
            $emailLog->sent_at = now();
            $emailLog->save();

            session()->flash('success', '이메일이 발송되었습니다.');
            return true;

        } catch (\Exception $e) {
            \Log::error('이메일 발송 실패', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $emailLog->status = 'failed';
            $emailLog->error_message = $e->getMessage();
            $emailLog->failed_at = now();
            $emailLog->save();

            session()->flash('error', '이메일 발송 실패: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Hook: 이메일 재발송
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  array  $params  파라미터
     * @return mixed
     */
    public function hookCustomResend($wire, $params = [])
    {
        $id = $params['id'] ?? null;

        if (!$id) {
            $wire->dispatch('showError', message: 'ID가 필요합니다.');
            return false;
        }

        $emailLog = AdminEmailLog::find($id);

        if (!$emailLog) {
            $wire->dispatch('showError', message: '이메일 로그를 찾을 수 없습니다.');
            return false;
        }

        if (!in_array($emailLog->status, ['failed', 'bounced'])) {
            $wire->dispatch('showError', message: '실패 또는 반송된 이메일만 재발송할 수 있습니다.');
            return false;
        }

        // 메일 설정 로드
        $adminMailConfig = $this->loadMailConfig();

        // 재발송 시 새로운 트래킹 토큰 생성
        $trackingToken = bin2hex(random_bytes(32));
        $emailLog->tracking_token = $trackingToken;
        $emailLog->status = 'processing';
        $emailLog->retry_count = $emailLog->retry_count + 1;
        // 재발송 시 이전 트래킹 정보 초기화
        $emailLog->opened_at = null;
        $emailLog->open_count = 0;
        $emailLog->first_opened_at = null;
        $emailLog->open_details = null;
        $emailLog->link_clicks = null;
        $emailLog->total_clicks = 0;
        $emailLog->save();

        try {
            // EmailMailable 사용하여 메일 발송 (트래킹 토큰 포함)
            $mailable = new EmailMailable(
                $emailLog->subject,
                $emailLog->body,
                $emailLog->from_email ?: $adminMailConfig['from_address'],
                $emailLog->from_name ?: $adminMailConfig['from_name'],
                $emailLog->to_email,
                $trackingToken,  // 트래킹 토큰
                true  // 트래킹 활성화
            );

            // CC/BCC 처리
            if ($emailLog->cc) {
                $cc = is_string($emailLog->cc) ? json_decode($emailLog->cc, true) : $emailLog->cc;
                if (is_array($cc)) {
                    foreach ($cc as $email) {
                        $mailable->cc($email);
                    }
                }
            }

            if ($emailLog->bcc) {
                $bcc = is_string($emailLog->bcc) ? json_decode($emailLog->bcc, true) : $emailLog->bcc;
                if (is_array($bcc)) {
                    foreach ($bcc as $email) {
                        $mailable->bcc($email);
                    }
                }
            }

            // 메일 발송
            Mail::to($emailLog->to_email, $emailLog->to_name)->send($mailable);

            $emailLog->status = 'sent';
            $emailLog->sent_at = now();
            $emailLog->save();

            session()->flash('success', '이메일이 재발송되었습니다.');
            return true;

        } catch (\Exception $e) {
            $emailLog->status = 'failed';
            $emailLog->error_message = $e->getMessage();
            $emailLog->failed_at = now();
            $emailLog->save();

            session()->flash('error', '이메일 재발송 실패: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Hook: 대량 삭제
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  array  $params  파라미터
     * @return mixed
     */
    public function hookCustomBulkDelete($wire, $params = [])
    {
        $ids = $params['ids'] ?? [];

        if (empty($ids)) {
            $wire->dispatch('showError', message: '삭제할 항목을 선택해주세요.');
            return false;
        }

        try {
            AdminEmailLog::whereIn('id', $ids)->delete();
            $wire->dispatch('showSuccess', message: count($ids) . '개의 이메일 로그가 삭제되었습니다.');
            $wire->dispatch('refreshTable');
            return true;
        } catch (\Exception $e) {
            $wire->dispatch('showError', message: '삭제 실패: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Hook: 대량 발송
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  array  $params  파라미터
     * @return mixed
     */
    public function hookCustomBulkSend($wire, $params = [])
    {
        $ids = $params['ids'] ?? [];

        if (empty($ids)) {
            $wire->dispatch('showError', message: '발송할 이메일을 선택해주세요.');
            return false;
        }

        $emailLogs = AdminEmailLog::whereIn('id', $ids)
            ->where('status', 'pending')
            ->get();

        if ($emailLogs->isEmpty()) {
            $wire->dispatch('showError', message: '발송 가능한 이메일이 없습니다.');
            return false;
        }

        // 메일 설정 로드
        $adminMailConfig = $this->loadMailConfig();

        $successCount = 0;
        $failCount = 0;

        foreach ($emailLogs as $emailLog) {
            $emailLog->status = 'processing';
            $emailLog->save();

            try {
                // EmailMailable 사용하여 메일 발송
                $mailable = new EmailMailable(
                    $emailLog->subject,
                    $emailLog->body,
                    $emailLog->from_email ?: $adminMailConfig['from_address'],
                    $emailLog->from_name ?: $adminMailConfig['from_name'],
                    $emailLog->to_email
                );

                // CC/BCC 처리
                if ($emailLog->cc) {
                    $cc = is_string($emailLog->cc) ? json_decode($emailLog->cc, true) : $emailLog->cc;
                    if (is_array($cc)) {
                        foreach ($cc as $email) {
                            $mailable->cc($email);
                        }
                    }
                }

                if ($emailLog->bcc) {
                    $bcc = is_string($emailLog->bcc) ? json_decode($emailLog->bcc, true) : $emailLog->bcc;
                    if (is_array($bcc)) {
                        foreach ($bcc as $email) {
                            $mailable->bcc($email);
                        }
                    }
                }

                // 메일 발송
                Mail::to($emailLog->to_email, $emailLog->to_name)->send($mailable);

                $emailLog->status = 'sent';
                $emailLog->sent_at = now();
                $emailLog->save();
                $successCount++;
            } catch (\Exception $e) {
                $emailLog->status = 'failed';
                $emailLog->error_message = $e->getMessage();
                $emailLog->failed_at = now();
                $emailLog->save();
                $failCount++;
            }
        }

        $message = "{$successCount}개 발송 성공";
        if ($failCount > 0) {
            $message .= ", {$failCount}개 발송 실패";
        }

        $wire->dispatch('showSuccess', message: $message);
        $wire->dispatch('refreshTable');
        return true;
    }


    /**
     * 메일 설정 로드 및 적용
     * @return array 메일 설정 배열
     */
    protected function loadMailConfig()
    {
        // jiny/admin/config/mail.php 파일에서 직접 읽기
        $configPath = base_path('jiny/admin/config/mail.php');
        if (file_exists($configPath)) {
            $adminMailConfig = include $configPath;
        } else {
            // 파일이 없으면 기본 config 사용
            $adminMailConfig = config('admin.mail', [
                'mailer' => 'smtp',
                'host' => 'smtp.mailgun.org',
                'port' => 587,
                'username' => '',
                'password' => '',
                'encryption' => 'tls',
                'from_address' => 'hello@example.com',
                'from_name' => 'Example',
            ]);
        }

        // 런타임 메일 설정 적용
        config([
            'mail.default' => $adminMailConfig['mailer'],
            'mail.mailers.smtp.host' => $adminMailConfig['host'],
            'mail.mailers.smtp.port' => $adminMailConfig['port'],
            'mail.mailers.smtp.username' => $adminMailConfig['username'],
            'mail.mailers.smtp.password' => $adminMailConfig['password'],
            'mail.mailers.smtp.encryption' => $adminMailConfig['encryption'] === 'null' ? null : $adminMailConfig['encryption'],
            'mail.from.address' => $adminMailConfig['from_address'],
            'mail.from.name' => $adminMailConfig['from_name'],
        ]);

        // 메일러가 smtp가 아닌 경우 추가 설정
        if ($adminMailConfig['mailer'] !== 'smtp') {
            switch ($adminMailConfig['mailer']) {
                case 'sendmail':
                    config(['mail.mailers.sendmail.path' => '/usr/sbin/sendmail -bs']);
                    break;
                case 'log':
                    config(['mail.mailers.log.channel' => env('MAIL_LOG_CHANNEL', 'mail')]);
                    break;
            }
        }

        return $adminMailConfig;
    }
}
