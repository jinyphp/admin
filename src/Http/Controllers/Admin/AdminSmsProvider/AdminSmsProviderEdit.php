<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminSmsProvider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\admin\App\Services\JsonConfigService;

/**
 * admin 수정 컨트롤러
 * 
 * 기존 admin 정보를 수정하는 폼 표시 및 처리를 담당합니다.
 * Livewire 컴포넌트(AdminEdit)와 Hook 패턴을 통해 동작합니다.
 * 
 * @package Jiny\Admin\App\Http\Controllers\Admin\AdminSmsProvider
 * @since   1.0.0
 */
class AdminSmsProviderEdit extends Controller
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
     * 수정 폼 표시
     *
     * @param  Request  $request  HTTP 요청 객체
     * @param  mixed    $id       수정할 레코드 ID
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function __invoke(Request $request, $id)
    {
        // JSON 데이터 확인
        if (!$this->jsonData) {
            return response('Error: JSON 데이터를 로드할 수 없습니다.', 500);
        }

        // 데이터베이스에서 데이터 조회
        $tableName = $this->jsonData['table']['name'] ?? null;
        if (!$tableName) {
            return response('Error: 테이블 설정이 필요합니다.', 500);
        }

        $data = DB::table($tableName)->where('id', $id)->first();
        
        if (!$data) {
            return response('Error: 데이터를 찾을 수 없습니다.', 404);
        }

        // 객체를 배열로 변환하여 $form 변수 생성
        $form = (array) $data;

        // route 정보를 jsonData에 추가
        if (isset($this->jsonData['route']['name'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route']['name'];
        }

        // template.edit view 경로 확인
        if (!isset($this->jsonData['template']['edit'])) {
            return response('Error: 화면을 출력하기 위한 template.edit 설정이 필요합니다.', 500);
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__ . DIRECTORY_SEPARATOR . 'AdminSmsProvider.json';
        $settingsPath = $jsonPath;

        // 현재 컨트롤러 클래스를 JSON 데이터에 추가
        $this->jsonData['controllerClass'] = get_class($this);

        return view($this->jsonData['template']['edit'], [
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'controllerClass' => static::class,
            'data' => $data,
            'form' => $form,
            'id' => $id,
        ]);
    }

    /**
     * Hook: 수정 폼 초기화
     *
     * @param  mixed  $wire  Livewire 컴포넌트
     * @param  array  $form  기존 데이터
     * @return array
     */
    public function hookEditing($wire, $form)
    {
        // API 키 마스킹 (보안)
        if (!empty($form['api_key'])) {
            $form['api_key_masked'] = substr($form['api_key'], 0, 8) . '****' . substr($form['api_key'], -4);
        }
        
        if (!empty($form['api_secret'])) {
            $form['api_secret_masked'] = '****' . substr($form['api_secret'], -4);
        }
        
        // settings JSON 디코드
        if (!empty($form['settings']) && is_string($form['settings'])) {
            $form['settings_decoded'] = json_decode($form['settings'], true);
        }
        
        return $form;
    }

    /**
     * Hook: 업데이트 전 처리
     *
     * @param  mixed  $wire  Livewire 컴포넌트
     * @param  array  $form  수정된 데이터
     * @return array|string 성공시 배열, 실패시 에러 메시지
     */
    public function hookUpdating($wire, $form)
    {
        // Livewire 컴포넌트의 public 속성 $id에서 ID 가져오기
        $id = null;
        
        // Livewire 컴포넌트의 id 속성 확인
        if (property_exists($wire, 'id') && $wire->id) {
            $id = $wire->id;
        } elseif (request()->route('id')) {
            // URL 파라미터에서 ID 가져오기
            $id = request()->route('id');
        } elseif (!empty($form['id'])) {
            // form 데이터에서 ID 가져오기
            $id = $form['id'];
        }
        
        if (!$id || !is_numeric($id)) {
            return '유효한 레코드 ID를 찾을 수 없습니다. (ID: ' . var_export($id, true) . ')';
        }
        
        // ID가 문자열이 아닌 정수인지 확인
        $id = intval($id);
        
        // 필수 필드 검증
        if (empty($form['provider_name'])) {
            return '제공업체명은 필수입니다.';
        }
        
        // API 키가 비어있으면 기존 값 유지
        if (empty($form['api_key'])) {
            $existing = DB::table('admin_sms_providers')
                ->where('id', $id)
                ->first();
            
            if ($existing) {
                $form['api_key'] = $existing->api_key;
            } else {
                return 'API Key는 필수입니다.';
            }
        }
        
        // API Secret이 비어있으면 기존 값 유지
        if (empty($form['api_secret'])) {
            $existing = $existing ?? DB::table('admin_sms_providers')
                ->where('id', $id)
                ->first();
            
            if ($existing) {
                $form['api_secret'] = $existing->api_secret;
            }
        }
        
        // 기본 제공업체로 설정 시 다른 제공업체의 기본 설정 해제
        if (!empty($form['is_default']) && $form['is_default']) {
            DB::table('admin_sms_providers')
                ->where('id', '!=', $id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }
        
        // 비활성화 시 기본 제공업체 설정 해제
        if (empty($form['is_active']) && !empty($form['is_default'])) {
            $form['is_default'] = false;
        }
        
        // settings 업데이트 (settings_decoded는 임시 필드이므로 제거)
        if (!empty($form['settings_decoded'])) {
            $form['settings'] = json_encode($form['settings_decoded']);
            unset($form['settings_decoded']); // 데이터베이스에 없는 필드 제거
        }
        
        // 데이터베이스에 없는 임시 필드들 제거
        unset($form['api_key_masked']);
        unset($form['api_secret_masked']);
        
        // ID는 WHERE 조건으로만 사용되어야 하므로 업데이트 필드에서 제거
        unset($form['id']);
        
        // updated_at은 자동으로 설정되므로 제거
        unset($form['updated_at']);
        unset($form['created_at']);
        
        return $form;
    }

    /**
     * Hook: driver_type 필드 변경 시 처리
     *
     * @param  mixed  $wire  Livewire 컴포넌트
     * @param  mixed  $value  새로운 값
     * @param  string  $fieldName  필드명
     * @return void
     */
    public function hookFormDriverType($wire, $value, $fieldName)
    {
        // 드라이버별 필드 초기화
        if ($value === 'vonage') {
            // Twilio 필드 초기화
            $wire->form['account_sid'] = null;
            $wire->form['auth_token'] = null;
        } elseif ($value === 'twilio') {
            // Vonage 필드 초기화
            $wire->form['api_key'] = null;
            $wire->form['api_secret'] = null;
        }
    }
    
    /**
     * Hook: 업데이트 완료 후 처리
     *
     * @param  mixed  $wire  Livewire 컴포넌트
     * @param  array  $form  업데이트된 데이터
     * @return void
     */
    public function hookUpdated($wire, $form)
    {
        // 업데이트 성공 메시지
        session()->flash('message', 'SMS 제공업체 정보가 성공적으로 수정되었습니다.');
        
        // API 키 변경 시 경고
        if (!empty($form['api_key'])) {
            session()->flash('warning', 'API 키가 변경되었습니다. 연결 테스트를 권장합니다.');
        }
        
        // 활동 로그 기록
        if (class_exists('\Jiny\Admin\App\Models\AdminUserLogs')) {
            // ID 가져오기
            $id = request()->route('id') ?? null;
            
            \Jiny\Admin\App\Models\AdminUserLogs::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'target_type' => 'sms_provider',
                'target_id' => $id,
                'data' => json_encode([
                    'provider_name' => $form['provider_name'],
                    'updated_fields' => array_keys($form)
                ]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
        }
    }
}