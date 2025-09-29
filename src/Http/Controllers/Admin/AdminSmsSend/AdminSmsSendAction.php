<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminSmsSend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Admin\Services\SmsService;
use Jiny\Admin\Models\AdminSmsSend;

/**
 * SMS 발송 액션 컨트롤러
 * 
 * 저장된 SMS 메시지를 실제로 발송하는 기능을 처리합니다.
 * 
 * @package Jiny\Admin\App\Http\Controllers\Admin\AdminSmsSend
 * @since   1.0.0
 */
class AdminSmsSendAction extends Controller
{
    /**
     * SMS 즉시 발송
     * 목록에서 발송 버튼 클릭 시 호출
     *
     * @param  Request  $request
     * @param  int      $id  발송할 SMS 레코드 ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function send(Request $request, $id)
    {
        try {
            // SMS 레코드 조회
            $smsRecord = AdminSmsSend::findOrFail($id);
            
            // 이미 발송된 경우 체크
            if ($smsRecord->status === 'sent') {
                return response()->json([
                    'success' => false,
                    'message' => '이미 발송된 메시지입니다.'
                ], 400);
            }
            
            // SMS 서비스 초기화
            $smsService = new SmsService();
            
            // 제공업체 설정
            if ($smsRecord->provider_id) {
                $smsService->setProvider($smsRecord->provider_id);
            }
            
            // SMS 발송
            $result = $smsService->send(
                $smsRecord->to_number,
                $smsRecord->message,
                $smsRecord->from_number
            );
            
            // 발송 성공 시 상태 업데이트
            $smsRecord->update([
                'status' => 'sent',
                'sent_at' => now(),
                'response_data' => json_encode($result)
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'SMS가 성공적으로 발송되었습니다.',
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            // 발송 실패 시 상태 업데이트
            if (isset($smsRecord)) {
                $smsRecord->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'SMS 발송 실패: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * SMS 대량 발송
     * 선택된 여러 메시지를 한번에 발송
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendBulk(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:admin_sms_sends,id'
        ]);
        
        $successCount = 0;
        $failCount = 0;
        $errors = [];
        
        $smsService = new SmsService();
        
        foreach ($request->ids as $id) {
            try {
                $smsRecord = AdminSmsSend::find($id);
                
                // 이미 발송된 경우 건너뛰기
                if ($smsRecord->status === 'sent') {
                    continue;
                }
                
                // 제공업체 설정
                if ($smsRecord->provider_id) {
                    $smsService->setProvider($smsRecord->provider_id);
                }
                
                // SMS 발송
                $result = $smsService->send(
                    $smsRecord->to_number,
                    $smsRecord->message,
                    $smsRecord->from_number
                );
                
                // 발송 성공 시 상태 업데이트
                $smsRecord->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'response_data' => json_encode($result)
                ]);
                
                $successCount++;
                
            } catch (\Exception $e) {
                $failCount++;
                $errors[] = "ID {$id}: " . $e->getMessage();
                
                // 발송 실패 시 상태 업데이트
                if (isset($smsRecord)) {
                    $smsRecord->update([
                        'status' => 'failed',
                        'error_message' => $e->getMessage()
                    ]);
                }
            }
        }
        
        $message = "발송 완료: 성공 {$successCount}건, 실패 {$failCount}건";
        
        if (!empty($errors)) {
            $message .= "\n실패 상세:\n" . implode("\n", $errors);
        }
        
        return response()->json([
            'success' => $failCount === 0,
            'message' => $message,
            'data' => [
                'success_count' => $successCount,
                'fail_count' => $failCount,
                'errors' => $errors
            ]
        ]);
    }
    
    /**
     * SMS 재발송
     * 실패한 메시지를 다시 발송
     *
     * @param  Request  $request
     * @param  int      $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function resend(Request $request, $id)
    {
        try {
            $smsRecord = AdminSmsSend::findOrFail($id);
            
            // 실패한 메시지만 재발송 가능
            if ($smsRecord->status !== 'failed') {
                return response()->json([
                    'success' => false,
                    'message' => '실패한 메시지만 재발송할 수 있습니다.'
                ], 400);
            }
            
            // SMS 서비스 초기화
            $smsService = new SmsService();
            
            // 제공업체 설정
            if ($smsRecord->provider_id) {
                $smsService->setProvider($smsRecord->provider_id);
            }
            
            // SMS 재발송
            $result = $smsService->send(
                $smsRecord->to_number,
                $smsRecord->message,
                $smsRecord->from_number
            );
            
            // 발송 성공 시 상태 업데이트
            $smsRecord->update([
                'status' => 'sent',
                'sent_at' => now(),
                'response_data' => json_encode($result),
                'error_message' => null
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'SMS가 성공적으로 재발송되었습니다.',
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            // 재발송 실패 시 에러 메시지 업데이트
            if (isset($smsRecord)) {
                $smsRecord->update([
                    'error_message' => $e->getMessage()
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'SMS 재발송 실패: ' . $e->getMessage()
            ], 500);
        }
    }
}