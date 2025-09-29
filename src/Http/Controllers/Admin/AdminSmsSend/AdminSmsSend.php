<?php
namespace Jiny\Admin\Http\Controllers\Admin\AdminSmsSend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Jiny\Admin\Services\JsonConfigService;
use Jiny\Admin\Services\Sms\SmsManager;

class AdminSmsSend extends Controller
{
    private $jsonData;

    public function __construct()
    {
        // 서비스를 사용하여 JSON 파일 로드
        $jsonConfigService = new JsonConfigService();
        $this->jsonData = $jsonConfigService->loadFromControllerPath(__DIR__);
    }

    /**
     * Display a listing of the resource.
     */
    public function __invoke(Request $request)
    {
        // JSON 데이터 확인
        if (!$this->jsonData) {
            return response("Error: JSON 데이터를 로드할 수 없습니다.", 500);
        }

        // template.index view 경로 확인
        if(!isset($this->jsonData['template']['index'])) {
            return response("Error: 화면을 출력하기 위한 template.index 설정이 필요합니다.", 500);
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__ . DIRECTORY_SEPARATOR . 'AdminSmsSend.json';
        $settingsPath = $jsonPath; // settings drawer를 위한 경로

        // currentRoute 설정
        $this->jsonData['currentRoute'] = $this->jsonData['route']['name'] ?? 'admin.sms_send';
        
        // 컨트롤러 클래스를 JSON 데이터에 추가
        $this->jsonData['controllerClass'] = get_class($this);

        return view($this->jsonData['template']['index'], [
            'controllerClass' => static::class,  // 현재 컨트롤러 클래스 전달
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'title' => $this->jsonData['title'] ?? 'SmsSend Management',
            'subtitle' => $this->jsonData['subtitle'] ?? 'Manage sms_sends'
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
     * Hook: 커스텀 데이터 조회
     * 기본 데이터 조회 로직을 완전히 대체합니다.
     *
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @return mixed|false false 반환시 기본 조회 로직 사용
     */
    public function hookCustomRows($wire)
    {
        // 테이블 이름
        $tableName = $this->jsonData['table']['name'] ?? 'admin_sms_sends';
        
        // 쿼리 생성
        $query = DB::table($tableName);
        
        // 검색어 적용
        if (!empty($wire->search)) {
            $query->where(function($q) use ($wire) {
                $q->where('to_number', 'like', '%' . $wire->search . '%')
                  ->orWhere('message', 'like', '%' . $wire->search . '%')
                  ->orWhere('message_id', 'like', '%' . $wire->search . '%');
            });
        }
        
        // 필터 적용
        if (!empty($wire->filter)) {
            foreach ($wire->filter as $key => $value) {
                if ($value !== '' && $value !== null) {
                    $query->where($key, $value);
                }
            }
        }
        
        // 정렬 처리 - sent_at이 NULL인 경우 created_at 사용
        if ($wire->sortField === 'sent_at') {
            $query->orderByRaw("COALESCE(sent_at, created_at) " . $wire->sortDirection);
        } else {
            $query->orderBy($wire->sortField, $wire->sortDirection);
        }
        
        // 페이지네이션
        return $query->paginate($wire->perPage);
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
            'default' => 'sent_at',
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
            'placeholder' => 'Search sms_sends...',
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
     * SMS 발송 처리
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function send(Request $request, $id)
    {
        try {
            $sms = DB::table('admin_sms_sends')->where('id', $id)->first();
            
            if (!$sms) {
                return response()->json([
                    'success' => false,
                    'message' => 'SMS 기록을 찾을 수 없습니다.'
                ], 404);
            }

            // 이미 발송된 경우 체크
            if ($sms->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => '이미 발송된 SMS입니다.'
                ], 400);
            }

            // SMS Manager를 통한 발송
            $smsManager = new SmsManager();
            $result = $smsManager->withProvider($sms->provider_id)->send($sms->to_number, $sms->message, $sms->from_number);
            
            if ($result['success']) {
                // 발송 성공
                DB::table('admin_sms_sends')
                    ->where('id', $id)
                    ->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                        'message_id' => $result['message_id'] ?? null,
                        'cost' => $result['message_price'] ?? null,
                        'response_data' => json_encode($result['response_data'] ?? []),
                        'updated_at' => now()
                    ]);

                // 제공업체 통계 업데이트
                if ($sms->provider_id) {
                    DB::table('admin_sms_providers')
                        ->where('id', $sms->provider_id)
                        ->increment('sent_count');
                        
                    // 잔액 업데이트
                    if (isset($result['remaining_balance'])) {
                        DB::table('admin_sms_providers')
                            ->where('id', $sms->provider_id)
                            ->update([
                                'balance' => $result['remaining_balance'],
                                'last_used_at' => now()
                            ]);
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'SMS가 성공적으로 발송되었습니다.',
                    'message_id' => $result['message_id'] ?? null,
                    'cost' => $result['message_price'] ?? null
                ]);
            } else {
                // 발송 실패
                DB::table('admin_sms_sends')
                    ->where('id', $id)
                    ->update([
                        'status' => 'failed',
                        'failed_at' => now(),
                        'error_code' => $result['error_code'] ?? null,
                        'error_message' => $result['error_message'] ?? '알 수 없는 오류',
                        'response_data' => json_encode($result['response_data'] ?? []),
                        'updated_at' => now()
                    ]);

                // 제공업체 실패 카운트 증가
                if ($sms->provider_id) {
                    DB::table('admin_sms_providers')
                        ->where('id', $sms->provider_id)
                        ->increment('failed_count');
                }

                return response()->json([
                    'success' => false,
                    'message' => 'SMS 발송 실패: ' . ($result['error_message'] ?? '알 수 없는 오류'),
                    'error_code' => $result['error_code'] ?? null
                ]);
            }
        } catch (\Exception $e) {
            // 예외 발생 시 실패 처리
            if (isset($id)) {
                DB::table('admin_sms_sends')
                    ->where('id', $id)
                    ->update([
                        'status' => 'failed',
                        'failed_at' => now(),
                        'error_message' => $e->getMessage(),
                        'updated_at' => now()
                    ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'SMS 발송 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hook: 데이터 저장 전 처리
     * 
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @param array $data 저장할 데이터
     * @return array 수정된 데이터
     */
    public function hookStoring($wire, $data)
    {
        \Log::info('hookStoring called for SMS', [
            'controller' => get_class($this),
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
     * Hook: 데이터 저장 후 처리 (즉시 발송)
     * 
     * @param mixed $wire Livewire 컴포넌트 인스턴스
     * @param array $data 저장된 데이터
     */
    public function hookStored($wire, $data)
    {
        \Log::info('hookStored called', [
            'data_id' => $data['id'] ?? 'unknown',
            'to_number' => $data['to_number'] ?? 'unknown',
            'testSendFlag' => $wire->testSendFlag ?? false
        ]);
        
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
    
    /**
     * SMS 재발송 처리
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function resend(Request $request, $id)
    {
        try {
            $sms = DB::table('admin_sms_sends')->where('id', $id)->first();
            
            if (!$sms) {
                return response()->json([
                    'success' => false,
                    'message' => 'SMS 기록을 찾을 수 없습니다.'
                ], 404);
            }

            // 실패한 메시지만 재발송 가능
            if ($sms->status !== 'failed') {
                return response()->json([
                    'success' => false,
                    'message' => '실패한 SMS만 재발송할 수 있습니다.'
                ], 400);
            }

            // SMS Manager를 통한 재발송
            $smsManager = new SmsManager();
            $result = $smsManager->withProvider($sms->provider_id)->send(
                $sms->to_number, 
                $sms->message, 
                $sms->from_number
            );
            
            if ($result['success']) {
                // 발송 성공
                DB::table('admin_sms_sends')
                    ->where('id', $id)
                    ->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                        'message_id' => $result['message_id'] ?? null,
                        'cost' => $result['message_price'] ?? $result['price'] ?? null,
                        'response_data' => json_encode($result['response_data'] ?? []),
                        'error_message' => null,
                        'error_code' => null,
                        'retry_count' => DB::raw('retry_count + 1'),
                        'updated_at' => now()
                    ]);
                
                // 제공업체 통계 업데이트
                if ($sms->provider_id) {
                    DB::table('admin_sms_providers')
                        ->where('id', $sms->provider_id)
                        ->increment('sent_count');
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'SMS가 성공적으로 재발송되었습니다.',
                    'data' => $result
                ]);
            } else {
                // 발송 실패
                DB::table('admin_sms_sends')
                    ->where('id', $id)
                    ->update([
                        'status' => 'failed',
                        'failed_at' => now(),
                        'error_code' => $result['error_code'] ?? null,
                        'error_message' => $result['error_message'] ?? '알 수 없는 오류',
                        'response_data' => json_encode($result['response_data'] ?? []),
                        'retry_count' => DB::raw('retry_count + 1'),
                        'updated_at' => now()
                    ]);
                
                // 제공업체 실패 카운트 증가
                if ($sms->provider_id) {
                    DB::table('admin_sms_providers')
                        ->where('id', $sms->provider_id)
                        ->increment('failed_count');
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'SMS 재발송 실패: ' . ($result['error_message'] ?? '알 수 없는 오류'),
                    'data' => $result
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'SMS 재발송 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }
}