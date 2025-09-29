<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminSmsSend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Jiny\admin\App\Services\JsonConfigService;
use Jiny\Admin\Services\Sms\SmsManager;

/**
 * admin 생성 컨트롤러
 * 
 * 새로운 admin를 생성하는 폼 표시 및 처리를 담당합니다.
 * Livewire 컴포넌트(AdminCreate)와 Hook 패턴을 통해 동작합니다.
 * 
 * @package Jiny\Admin\App\Http\Controllers\Admin\AdminSmsSend
 * @since   1.0.0
 */
class AdminSmsSendCreate extends Controller
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
     * Single Action __invoke method
     * 생성 폼 표시
     *
     * @param  Request  $request  HTTP 요청 객체
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        // JSON 데이터 확인
        if (!$this->jsonData) {
            return response('Error: JSON 데이터를 로드할 수 없습니다.', 500);
        }

        // 기본값 설정
        $form = [];

        // route 정보를 jsonData에 추가
        if (isset($this->jsonData['route']['name'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route']['name'];
        }

        // template.create view 경로 확인
        if (!isset($this->jsonData['template']['create'])) {
            return response('Error: 화면을 출력하기 위한 template.create 설정이 필요합니다.', 500);
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__ . DIRECTORY_SEPARATOR . 'AdminSmsSend.json';
        $settingsPath = $jsonPath;

        // 현재 컨트롤러 클래스를 JSON 데이터에 추가
        $this->jsonData['controllerClass'] = get_class($this);

        return view($this->jsonData['template']['create'], [
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'controllerClass' => static::class,
            'form' => $form,
        ]);
    }

    /**
     * Hook: 폼 초기화
     *
     * @param  mixed  $wire  Livewire 컴포넌트
     * @param  array  $form  폼 데이터
     * @return array
     */
    public function hookCreating($wire, $form)
    {
        // 기본값 설정
        return $form;
    }

    /**
     * Hook: 저장 전 처리
     *
     * @param  mixed  $wire  Livewire 컴포넌트
     * @param  array  $data  폼 데이터
     * @return array|string 성공시 배열, 실패시 에러 메시지
     */
    public function hookStoring($wire, $data)
    {
        \Log::info('AdminSmsSendCreate::hookStoring called', [
            'data_keys' => array_keys($data),
            'to_number' => $data['to_number'] ?? 'not set',
            'message' => substr($data['message'] ?? '', 0, 50)
        ]);
        
        // 필수 필드 설정
        if (!isset($data['provider_id']) || empty($data['provider_id'])) {
            // 기본 제공업체 찾기
            $defaultProvider = DB::table('admin_sms_providers')
                ->where('is_active', 1)
                ->orderBy('priority', 'desc')
                ->first();
            
            if ($defaultProvider) {
                $data['provider_id'] = $defaultProvider->id;
                $data['provider_name'] = $defaultProvider->provider_name;
            }
        } else {
            // 제공업체 이름 설정
            $provider = DB::table('admin_sms_providers')
                ->where('id', $data['provider_id'])
                ->first();
            if ($provider) {
                $data['provider_name'] = $provider->provider_name;
            }
        }
        
        // 메시지 길이 계산
        $data['message_length'] = mb_strlen($data['message']);
        
        // 메시지 건수 계산 (한글 기준)
        if ($data['message_length'] <= 70) {
            $data['message_count'] = 1;
        } else {
            $data['message_count'] = ceil($data['message_length'] / 67);
        }
        
        // 상태 설정
        $data['status'] = 'pending';
        
        // 타임스탬프
        $data['created_at'] = now();
        $data['updated_at'] = now();
        
        // IP 주소 및 User Agent
        $data['ip_address'] = request()->ip();
        $data['user_agent'] = request()->userAgent();
        
        // 발송자 정보
        if (Auth::check()) {
            $data['sent_by'] = Auth::id();
        }
        
        return $data;
    }

    /**
     * Hook Custom: SMS 즉시 발송
     * 
     * @param mixed $wire Livewire 컴포넌트
     * @param array $params 파라미터
     * @return bool
     */
    public function hookCustomSendSms($wire, $params = [])
    {
        \Log::info('hookCustomSendSms called');
        
        // 발송 플래그 설정
        $wire->sendFlag = true;
        $wire->testSendFlag = false;
        
        // 저장 및 발송
        $wire->save(false);
        
        return true;
    }
    
    /**
     * Hook Custom: 테스트 SMS 발송
     * 
     * @param mixed $wire Livewire 컴포넌트
     * @param array $params 파라미터
     * @return bool
     */
    public function hookCustomTestSend($wire, $params = [])
    {
        \Log::info('hookCustomTestSend called');
        
        // 발송 플래그 설정
        $wire->sendFlag = true;
        $wire->testSendFlag = true;
        
        // 저장 및 발송
        $wire->save(false);
        
        return true;
    }
    
    /**
     * Hook: 저장 후 처리 (즉시 발송)
     *
     * @param  mixed  $wire  Livewire 컴포넌트
     * @param  array  $data  저장된 데이터
     * @return void
     */
    public function hookStored($wire, $data)
    {
        \Log::info('AdminSmsSendCreate::hookStored called', [
            'data_id' => $data['id'] ?? 'unknown',
            'to_number' => $data['to_number'] ?? 'unknown',
            'sendFlag' => $wire->sendFlag ?? false,
            'testSendFlag' => $wire->testSendFlag ?? false
        ]);
        
        // 발송 플래그가 설정된 경우에만 발송
        if (!isset($wire->sendFlag) || !$wire->sendFlag) {
            \Log::info('SendFlag not set, skipping SMS send');
            session()->flash('success', 'SMS 메시지가 저장되었습니다.');
            return;
        }
        
        // 버튼 타입 확인 (testSend 메서드에서 설정된 플래그)
        $isTestSend = $wire->testSendFlag ?? false;
        
        if ($isTestSend) {
            // 테스트 발송: 관리자 번호로 변경
            $adminPhone = env('ADMIN_TEST_PHONE', '01039113106');
            DB::table('admin_sms_sends')
                ->where('id', $data['id'])
                ->update([
                    'to_number' => $adminPhone,
                    'message' => '[테스트] ' . $data['message']
                ]);
            $data['to_number'] = $adminPhone;
            $data['message'] = '[테스트] ' . $data['message'];
        }
        
        // SMS 즉시 발송
        try {
            $smsManager = new SmsManager();
            $result = $smsManager->withProvider($data['provider_id'])->send(
                $data['to_number'], 
                $data['message'], 
                $data['from_number'] ?? null
            );
            
            if ($result['success']) {
                // 발송 성공
                DB::table('admin_sms_sends')
                    ->where('id', $data['id'])
                    ->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                        'message_id' => $result['message_id'] ?? null,
                        'cost' => $result['message_price'] ?? null,
                        'response_data' => json_encode($result['response_data'] ?? []),
                        'updated_at' => now()
                    ]);
                
                // 제공업체 통계 업데이트
                if ($data['provider_id']) {
                    DB::table('admin_sms_providers')
                        ->where('id', $data['provider_id'])
                        ->increment('sent_count');
                    
                    if (isset($result['remaining_balance'])) {
                        DB::table('admin_sms_providers')
                            ->where('id', $data['provider_id'])
                            ->update([
                                'balance' => $result['remaining_balance'],
                                'last_used_at' => now()
                            ]);
                    }
                }
                
                // 성공 메시지 설정
                $message = $isTestSend ? 
                    "테스트 SMS가 관리자 번호({$adminPhone})로 발송되었습니다." :
                    "SMS가 성공적으로 발송되었습니다.";
                    
                session()->flash('success', $message);
                
            } else {
                // 발송 실패
                DB::table('admin_sms_sends')
                    ->where('id', $data['id'])
                    ->update([
                        'status' => 'failed',
                        'failed_at' => now(),
                        'error_code' => $result['error_code'] ?? null,
                        'error_message' => $result['error_message'] ?? '알 수 없는 오류',
                        'response_data' => json_encode($result['response_data'] ?? []),
                        'updated_at' => now()
                    ]);
                
                // 제공업체 실패 카운트 증가
                if ($data['provider_id']) {
                    DB::table('admin_sms_providers')
                        ->where('id', $data['provider_id'])
                        ->increment('failed_count');
                }
                
                session()->flash('error', 'SMS 발송 실패: ' . ($result['error_message'] ?? '알 수 없는 오류'));
            }
        } catch (\Exception $e) {
            // 예외 발생 시 실패 처리
            DB::table('admin_sms_sends')
                ->where('id', $data['id'])
                ->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'error_message' => $e->getMessage(),
                    'updated_at' => now()
                ]);
            
            session()->flash('error', 'SMS 발송 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
}