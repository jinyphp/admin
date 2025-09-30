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
 * EmailLogs 수정 컨트롤러
 * 
 * 기존 EmailLogs 정보를 수정하는 폼 표시 및 처리를 담당합니다.
 * Livewire 컴포넌트(AdminEdit)와 Hook 패턴을 통해 동작합니다.
 * 
 * @package Jiny\Admin\App\Http\Controllers\Admin\AdminEmailLogs
 * @since   1.0.0
 */
class AdminEmailLogsEdit extends Controller
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
     * 수정 폼 표시
     *
     * @param  Request  $request  HTTP 요청 객체
     * @param  int  $id  수정할 레코드 ID
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function __invoke(Request $request, $id)
    {
        // 데이터베이스에서 데이터 조회
        $tableName = $this->jsonData['table']['name'] ?? 'admin_email_logs';
        $data = DB::table($tableName)
            ->where('id', $id)
            ->first();

        if (! $data) {
            if (isset($this->jsonData['route']['name'])) {
                $redirectUrl = route($this->jsonData['route']['name']);
            } elseif (isset($this->jsonData['route']) && is_string($this->jsonData['route'])) {
                $redirectUrl = route($this->jsonData['route']);
            } else {
                $redirectUrl = '/admin/email_logs';
            }

            return redirect($redirectUrl)
                ->with('error', 'EmailLogs을(를) 찾을 수 없습니다.');
        }

        // 객체를 배열로 변환
        $form = (array) $data;

        // route 정보를 jsonData에 추가
        if (isset($this->jsonData['route']['name'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route']['name'];
        } elseif (isset($this->jsonData['route']) && is_string($this->jsonData['route'])) {
            // 이전 버전 호환성
            $this->jsonData['currentRoute'] = $this->jsonData['route'];
        }

        // template.edit view 경로 확인
        if (! isset($this->jsonData['template']['edit'])) {
            return response('Error: 화면을 출력하기 위한 template.edit 설정이 필요합니다.', 500);
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__.DIRECTORY_SEPARATOR.'AdminEmailLogs.json';
        $settingsPath = $jsonPath; // settings drawer를 위한 경로

        // 현재 컨트롤러 클래스를 JSON 데이터에 추가
        $this->jsonData['controllerClass'] = get_class($this);

        return view($this->jsonData['template']['edit'], [
            'controllerClass' => static::class,  // 현재 컨트롤러 클래스 전달
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'form' => $form,
            'id' => $id,
        ]);
    }

    /**
     * Hook: 수정폼이 실행될 때 호출
     *
     * 폼 데이터를 초기화하거나 필요한 데이터를 준비합니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  array  $form  현재 폼 데이터
     * @return array|string 초기화된 폼 데이터 또는 에러 메시지
     */
    public function hookEditing($wire, $form)
    {
        // pending 상태가 아닌 경우 수정 불가
        $editableStatuses = $this->jsonData['edit']['onlyEditableStatuses'] ?? ['pending'];
        
        if (!in_array($form['status'] ?? '', $editableStatuses)) {
            return $this->jsonData['edit']['messages']['notEditable'] ?? '이 상태의 이메일은 수정할 수 없습니다.';
        }
        
        // 템플릿 목록을 wire에 설정
        $templates = AdminEmailTemplate::where('is_active', true)
            ->orderBy('name')
            ->get();
        $wire->templates = $templates;
        
        // 수정 가능한 상태 플래그 설정
        $wire->isEditable = true;
        $wire->canSend = ($form['status'] ?? '') === 'pending';
        
        return $form;
    }

    /**
     * Hook: 데이터 업데이트 전 호출
     *
     * 데이터 검증 및 가공을 수행합니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  array  $form  폼 데이터
     * @return array|string 성공시 수정된 form 배열, 실패시 에러 메시지 문자열
     */
    public function hookUpdating($wire, $form)
    {
        // 불필요한 필드 제거
        unset($form['_token']);
        unset($form['_method']);

        // updated_at 타임스탬프 갱신
        $form['updated_at'] = now();

        // 성공: 배열 반환
        return $form;
    }

    /**
     * Hook: 데이터 업데이트 후 호출
     *
     * 추가 처리가 필요한 경우 구현합니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  array  $form  업데이트된 데이터
     * @return array 처리된 데이터
     */
    public function hookUpdated($wire, $form)
    {
        return $form;
    }

    /**
     * Hook: 커스텀 액션 처리
     * 
     * 이메일 발송 등의 커스텀 액션을 처리합니다.
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
            default:
                return null;
        }
    }

    /**
     * 이메일 발송
     */
    protected function sendEmail($wire, $params)
    {
        $emailLogId = $wire->form['id'] ?? null;
        
        if (!$emailLogId) {
            $wire->dispatch('showError', message: '이메일 정보를 찾을 수 없습니다.');
            return false;
        }
        
        $emailLog = AdminEmailLog::find($emailLogId);
        
        if (!$emailLog) {
            $wire->dispatch('showError', message: '이메일 로그를 찾을 수 없습니다.');
            return false;
        }
        
        // pending 상태인지 확인
        if ($emailLog->status !== 'pending') {
            $wire->dispatch('showError', message: $this->jsonData['send']['messages']['notAllowed'] ?? '대기중 상태의 이메일만 발송할 수 있습니다.');
            return false;
        }
        
        // 수정된 데이터로 업데이트
        $emailLog->update([
            'to_email' => $wire->form['to_email'],
            'to_name' => $wire->form['to_name'] ?? null,
            'from_email' => $wire->form['from_email'] ?? config('mail.from.address'),
            'from_name' => $wire->form['from_name'] ?? config('mail.from.name'),
            'subject' => $wire->form['subject'],
            'body' => $wire->form['body'],
            'status' => 'processing'
        ]);

        try {
            Mail::html($emailLog->body, function ($message) use ($emailLog) {
                $message->to($emailLog->to_email, $emailLog->to_name)
                    ->subject($emailLog->subject)
                    ->from($emailLog->from_email, $emailLog->from_name);
            });

            $emailLog->markAsSent();
            
            session()->flash('success', $this->jsonData['send']['messages']['success'] ?? '이메일이 발송되었습니다.');
            return redirect()->route('admin.system.mail.logs');

        } catch (\Exception $e) {
            $emailLog->markAsFailed($e->getMessage());
            
            $wire->dispatch('showError', message: sprintf(
                $this->jsonData['send']['messages']['error'] ?? '이메일 발송 중 오류가 발생했습니다: %s',
                $e->getMessage()
            ));
            return false;
        }
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