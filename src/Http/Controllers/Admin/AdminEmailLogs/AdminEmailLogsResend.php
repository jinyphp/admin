<?php
namespace Jiny\Admin\Http\Controllers\Admin\AdminEmailLogs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminEmailLogsResend extends Controller
{
    public function __invoke(Request $request, $id)
    {
        // JSON 설정 파일 읽기
        $jsonConfigService = new \Jiny\Admin\App\Services\JsonConfigService;
        $jsonData = $jsonConfigService->loadFromControllerPath(__DIR__);
        
        // 데이터베이스에서 로그 조회
        $tableName = $jsonData['table']['name'] ?? 'admin_email_logs';
        $log = \DB::table($tableName)->where('id', $id)->first();
        
        if (!$log) {
            return redirect()->route('admin.system.mail.logs')
                ->with('error', '이메일 로그를 찾을 수 없습니다.');
        }
        
        // 재발송 가능한 상태 확인
        $allowedStatuses = $jsonData['resend']['allowedStatuses'] ?? ['failed', 'bounced'];
        if (!in_array($log->status, $allowedStatuses)) {
            return redirect()->back()
                ->with('error', $jsonData['resend']['messages']['notAllowed'] ?? '이 이메일은 재발송할 수 없습니다.');
        }
        
        try {
            // 이메일 재발송 로직
            \Mail::html($log->body, function ($message) use ($log) {
                $message->to($log->to_email, $log->to_name)
                    ->subject($log->subject);
                
                if ($log->from_email) {
                    $message->from($log->from_email, $log->from_name);
                }
            });
            
            // 로그 상태 업데이트
            \DB::table($tableName)
                ->where('id', $id)
                ->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'error_message' => null,
                    'updated_at' => now()
                ]);
            
            return redirect()->back()
                ->with('success', $jsonData['resend']['messages']['success'] ?? '이메일이 재발송되었습니다.');
            
        } catch (\Exception $e) {
            // 오류 메시지 업데이트
            \DB::table($tableName)
                ->where('id', $id)
                ->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'updated_at' => now()
                ]);
            
            $errorMessage = sprintf(
                $jsonData['resend']['messages']['error'] ?? '이메일 재발송 중 오류가 발생했습니다: %s',
                $e->getMessage()
            );
            
            return redirect()->back()->with('error', $errorMessage);
        }
    }
}