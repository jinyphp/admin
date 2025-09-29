<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminSmsProvider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\admin\App\Services\JsonConfigService;

/**
 * admin 생성 컨트롤러
 * 
 * 새로운 admin를 생성하는 폼 표시 및 처리를 담당합니다.
 * Livewire 컴포넌트(AdminCreate)와 Hook 패턴을 통해 동작합니다.
 * 
 * @package Jiny\Admin\App\Http\Controllers\Admin\AdminSmsProvider
 * @since   1.0.0
 */
class AdminSmsProviderCreate extends Controller
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
        $jsonPath = __DIR__ . DIRECTORY_SEPARATOR . 'AdminSmsProvider.json';
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
        $form['is_active'] = $form['is_active'] ?? true;
        $form['priority'] = $form['priority'] ?? 0;
        $form['sent_count'] = 0;
        $form['failed_count'] = 0;
        
        return $form;
    }

    /**
     * Hook: 저장 전 처리
     *
     * @param  mixed  $wire  Livewire 컴포넌트
     * @param  array  $form  폼 데이터
     * @return array|string 성공시 배열, 실패시 에러 메시지
     */
    public function hookStoring($wire, $form)
    {
        // 데이터 가공 및 검증
        
        // 필수 필드 검증
        if (empty($form['provider_name'])) {
            return '제공업체명은 필수입니다.';
        }
        
        // 드라이버별 필수 필드 검증
        if ($form['driver_type'] === 'twilio') {
            if (empty($form['account_sid'])) {
                return 'Twilio Account SID는 필수입니다.';
            }
            if (empty($form['auth_token'])) {
                return 'Twilio Auth Token은 필수입니다.';
            }
        } else if ($form['driver_type'] === 'vonage') {
            if (empty($form['api_key'])) {
                return 'Vonage API Key는 필수입니다.';
            }
            if (empty($form['api_secret'])) {
                return 'Vonage API Secret은 필수입니다.';
            }
        }
        
        // provider_type이 없으면 driver_type을 사용
        if (empty($form['provider_type'])) {
            $form['provider_type'] = $form['driver_type'] ?? 'custom';
        }
        
        // 기본 제공업체로 설정 시 다른 제공업체의 기본 설정 해제
        if (!empty($form['is_default']) && $form['is_default']) {
            DB::table('admin_sms_providers')
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }
        
        // settings JSON 생성
        $form['settings'] = json_encode([
            'api_endpoint' => $this->getApiEndpoint($form['provider_type'] ?? 'custom'),
            'supports_unicode' => true,
            'max_message_length' => 1600
        ]);
        
        return $form;
    }

    /**
     * Hook: 저장 후 처리
     *
     * @param  mixed  $wire  Livewire 컴포넌트
     * @param  array  $form  저장된 데이터
     * @return void
     */
    public function hookStored($wire, $form)
    {
        // 저장 성공 메시지
        session()->flash('message', 'SMS 제공업체가 성공적으로 등록되었습니다.');
        
        // 연결 테스트 권장 메시지
        if (!empty($form['is_active'])) {
            session()->flash('info', '제공업체가 활성화되었습니다. 연결 테스트를 권장합니다.');
        }
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
     * 제공업체별 API 엔드포인트 반환
     */
    private function getApiEndpoint($providerType)
    {
        $endpoints = [
            'vonage' => 'https://rest.nexmo.com',
            'twilio' => 'https://api.twilio.com',
            'aws_sns' => 'https://sns.amazonaws.com',
            'messagebird' => 'https://rest.messagebird.com',
            'plivo' => 'https://api.plivo.com',
            'sinch' => 'https://sms.api.sinch.com',
            'aligo' => 'https://apis.aligo.in',
            'solutionlink' => 'https://api.coolsms.co.kr',
            'toast' => 'https://api-sms.cloud.toast.com'
        ];
        
        return $endpoints[$providerType] ?? '';
    }
}