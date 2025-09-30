<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminEmailLogs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Jiny\Admin\Models\AdminEmailLog;
use Jiny\Admin\Services\JsonConfigService;

/**
 * EmailLogs 발송 컨트롤러
 * 
 * 대기중(pending) 상태의 이메일을 발송합니다.
 * 
 * @package Jiny\Admin\App\Http\Controllers\Admin\AdminEmailLogs
 * @since   1.0.0
 */
class AdminEmailLogsSend extends Controller
{
    /**
     * JSON 설정 데이터
     *
     * @var array|null
     */
    private $jsonData;

    /**
     * 컨트롤러 생성자
     */
    public function __construct()
    {
        // 서비스를 사용하여 JSON 파일 로드
        $jsonConfigService = new JsonConfigService;
        $this->jsonData = $jsonConfigService->loadFromControllerPath(__DIR__);
    }

    /**
     * 이메일 발송 처리
     *
     * @param  Request  $request  HTTP 요청 객체
     * @param  int  $id  발송할 이메일 로그 ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, $id)
    {
        $emailLog = AdminEmailLog::find($id);
        
        if (!$emailLog) {
            return redirect()
                ->route('admin.system.mail.logs')
                ->with('error', '이메일 로그를 찾을 수 없습니다.');
        }
        
        // 발송 가능한 상태 확인
        $allowedStatuses = $this->jsonData['send']['allowedStatuses'] ?? ['pending'];
        
        if (!in_array($emailLog->status, $allowedStatuses)) {
            return redirect()
                ->back()
                ->with('error', $this->jsonData['send']['messages']['notAllowed'] ?? '대기중 상태의 이메일만 발송할 수 있습니다.');
        }
        
        // 상태를 처리중으로 변경
        $emailLog->status = 'processing';
        $emailLog->save();
        
        try {
            // 이메일 발송
            Mail::html($emailLog->body, function ($message) use ($emailLog) {
                $message->to($emailLog->to_email, $emailLog->to_name)
                    ->subject($emailLog->subject);
                
                if ($emailLog->from_email) {
                    $message->from($emailLog->from_email, $emailLog->from_name);
                }
                
                // CC 추가 (metadata에 저장된 경우)
                if ($emailLog->metadata && isset($emailLog->metadata['cc'])) {
                    foreach ((array)$emailLog->metadata['cc'] as $cc) {
                        $message->cc($cc);
                    }
                }
                
                // BCC 추가 (metadata에 저장된 경우)
                if ($emailLog->metadata && isset($emailLog->metadata['bcc'])) {
                    foreach ((array)$emailLog->metadata['bcc'] as $bcc) {
                        $message->bcc($bcc);
                    }
                }
            });
            
            // 발송 성공 처리
            $emailLog->markAsSent();
            
            return redirect()
                ->route('admin.system.mail.logs')
                ->with('success', $this->jsonData['send']['messages']['success'] ?? '이메일이 발송되었습니다.');
            
        } catch (\Exception $e) {
            // 발송 실패 처리
            $emailLog->markAsFailed($e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', sprintf(
                    $this->jsonData['send']['messages']['error'] ?? '이메일 발송 중 오류가 발생했습니다: %s',
                    $e->getMessage()
                ));
        }
    }
}