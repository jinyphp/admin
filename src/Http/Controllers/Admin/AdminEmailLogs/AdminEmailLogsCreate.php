<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminEmailLogs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Jiny\Admin\Services\JsonConfigService;
use Jiny\Admin\Models\AdminEmailLog;
use Jiny\Admin\Models\AdminEmailTemplate;

/**
 * EmailLogs 생성 컨트롤러
 * 
 * 새로운 EmailLogs를 생성하는 폼 표시 및 처리를 담당합니다.
 * Livewire 컴포넌트(AdminCreate)와 Hook 패턴을 통해 동작합니다.
 * 
 * @package Jiny\Admin\App\Http\Controllers\Admin\AdminEmailLogs
 * @since   1.0.0
 */
class AdminEmailLogsCreate extends Controller
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
     * 생성 폼 표시
     *
     * @param  Request  $request  HTTP 요청 객체
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        // JSON 데이터 확인
        if (! $this->jsonData) {
            return response('Error: JSON 데이터를 로드할 수 없습니다.', 500);
        }

        // 기본값 설정
        $form = [];

        // route 정보를 jsonData에 추가
        if (isset($this->jsonData['route']['name'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route']['name'];
        } elseif (isset($this->jsonData['route']) && is_string($this->jsonData['route'])) {
            // 이전 버전 호환성
            $this->jsonData['currentRoute'] = $this->jsonData['route'];
        }

        // template.create view 경로 확인
        if (! isset($this->jsonData['template']['create'])) {
            return response('Error: 화면을 출력하기 위한 template.create 설정이 필요합니다.', 500);
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__.DIRECTORY_SEPARATOR.'AdminEmailLogs.json';
        $settingsPath = $jsonPath; // settings drawer를 위한 경로

        // 현재 컨트롤러 클래스를 JSON 데이터에 추가
        $this->jsonData['controllerClass'] = get_class($this);

        // 템플릿 선택 기능이 활성화되어 있으면 템플릿 목록 로드
        $templates = collect();
        if ($this->jsonData['create']['enableTemplateSelector'] ?? false) {
            $templates = AdminEmailTemplate::where('is_active', 1)
                ->orderBy('name')
                ->get();
            // jsonData에 templates 추가하여 Livewire 컴포넌트에서 사용 가능하도록 함
            $this->jsonData['templates'] = $templates;
        }

        return view($this->jsonData['template']['create'], [
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'form' => $form,
            'templates' => $templates,
        ]);
    }

    /**
     * Hook: 생성폼이 실행될 때 호출
     *
     * 폼 초기값을 설정하거나 필요한 데이터를 준비합니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  array  $value  초기 폼 값
     * @return array 초기화된 폼 데이터
     */
    public function hookCreating($wire, $value)
    {
        // 기본값 설정
        $defaults = $this->jsonData['create']['defaults'] ??
                   $this->jsonData['store']['defaults'] ?? [];

        // 폼 기본값 설정
        $form = array_merge($defaults, $value);
        
        // 기본 발신자 정보 설정
        $form['from_email'] = $form['from_email'] ?? config('mail.from.address');
        $form['from_name'] = $form['from_name'] ?? config('mail.from.name');
        $form['status'] = 'pending';

        // 템플릿 목록은 __invoke 메소드에서 이미 view에 전달됨
        // hookCreating에서는 폼 데이터만 반환
        
        return $form;
    }

    /**
     * Hook: 데이터 DB 삽입 전 호출
     *
     * 데이터 검증 및 가공을 수행합니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  array  $form  폼 데이터
     * @return array|string 성공시 수정된 form 배열, 실패시 에러 메시지 문자열
     */
    public function hookStoring($wire, $form)
    {
        // 불필요한 필드 제거
        unset($form['_token']);
        unset($form['continue_creating']);

        // 상태를 pending으로 설정
        $form['status'] = 'pending';
        
        // 사용자 정보 추가
        $form['user_id'] = auth()->id();
        $form['ip_address'] = request()->ip();
        $form['user_agent'] = request()->userAgent();

        // timestamps 추가
        $form['created_at'] = now();
        $form['updated_at'] = now();

        // 성공: 배열 반환
        return $form;
    }

    /**
     * Hook: 데이터 DB 삽입 후 호출
     *
     * 이메일 로그 저장 후 처리. 자동 발송하지 않고 pending 상태로 유지.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  array  $form  저장된 데이터
     * @return array 처리된 데이터
     */
    public function hookStored($wire, $form)
    {
        // 이메일은 pending 상태로 저장되고, 별도의 발송 버튼을 통해 발송
        session()->flash('success', '이메일이 저장되었습니다. 목록에서 발송 버튼을 클릭하여 발송하세요.');
        
        return $form;
    }

    /**
     * Hook: 커스텀 액션 처리
     * 
     * 이메일 발송, 테스트 발송 등의 커스텀 액션을 처리합니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  string  $action  액션명
     * @param  array  $params  파라미터
     * @return mixed
     */
    public function hookCustom($wire, $action, $params = [])
    {
        switch ($action) {
            case 'send':
                return $this->sendEmail($wire, $params);
            case 'testSend':
                return $this->testSendEmail($wire, $params);
            case 'loadTemplate':
                return $this->loadTemplate($wire, $params);
            default:
                return null;
        }
    }


    /**
     * 이메일 즉시 발송
     */
    protected function sendEmail($wire, $params)
    {
        $form = $wire->form;
        
        // 먼저 데이터를 저장
        $emailLog = AdminEmailLog::create([
            'template_id' => $form['template_id'] ?? null,
            'to_email' => $form['to_email'],
            'to_name' => $form['to_name'] ?? null,
            'from_email' => $form['from_email'] ?? config('mail.from.address'),
            'from_name' => $form['from_name'] ?? config('mail.from.name'),
            'subject' => $form['subject'],
            'body' => $form['body'],
            'status' => 'processing',
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        try {
            Mail::html($emailLog->body, function ($message) use ($emailLog) {
                $message->to($emailLog->to_email, $emailLog->to_name)
                    ->subject($emailLog->subject)
                    ->from($emailLog->from_email, $emailLog->from_name);
            });

            $emailLog->markAsSent();
            
            session()->flash('success', $this->jsonData['create']['messages']['sent'] ?? '이메일이 발송되었습니다.');
            return redirect()->route('admin.system.mail.logs');

        } catch (\Exception $e) {
            $emailLog->markAsFailed($e->getMessage());
            
            $wire->dispatch('showError', message: '이메일 발송 실패: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 테스트 이메일 발송
     */
    protected function testSendEmail($wire, $params)
    {
        $form = $wire->form;
        
        try {
            Mail::html($form['body'], function ($message) use ($form) {
                $message->to($form['to_email'], $form['to_name'] ?? null)
                    ->subject('[TEST] ' . $form['subject'])
                    ->from(
                        $form['from_email'] ?? config('mail.from.address'),
                        $form['from_name'] ?? config('mail.from.name')
                    );
            });

            $wire->dispatch('showSuccess', message: $this->jsonData['create']['messages']['testSent'] ?? '테스트 이메일이 발송되었습니다.');
            return true;

        } catch (\Exception $e) {
            $wire->dispatch('showError', message: '테스트 발송 실패: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 템플릿 로드
     */
    protected function loadTemplate($wire, $params)
    {
        $templateId = $params['template_id'] ?? null;
        
        if (!$templateId) {
            return null;
        }

        $template = AdminEmailTemplate::find($templateId);
        
        if (!$template) {
            $wire->dispatch('showError', message: '템플릿을 찾을 수 없습니다.');
            return null;
        }

        // 템플릿 내용을 폼에 로드
        $wire->form['subject'] = $template->subject;
        $wire->form['body'] = $template->body;
        
        // 발신자 정보가 템플릿에 있으면 로드
        if ($template->from_email) {
            $wire->form['from_email'] = $template->from_email;
        }
        if ($template->from_name) {
            $wire->form['from_name'] = $template->from_name;
        }
        
        // JavaScript 이벤트 발생
        $wire->dispatch('templateLoaded', data: $template->toArray());
        
        session()->flash('message', '템플릿이 로드되었습니다.');
        return $template;
    }

    /**
     * Hook: 폼 필드 변경시 실시간 검증
     * 
     * hookForm{FieldName} 형태로 각 필드별 검증 메소드를 추가할 수 있습니다.
     * 예: hookFormEmail, hookFormName 등
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  mixed  $value  입력된 값
     * @param  string  $fieldName  필드명
     * @return void
     */
    // public function hookFormFieldName($wire, $value, $fieldName)
    // {
    //     // 필드별 실시간 검증 로직
    // }
}