<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminSessions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Admin\Services\JsonConfigService;

/**
 * AdminSessionsShow Controller
 */
class AdminSessionsShow extends Controller
{
    private $jsonData;

    public function __construct()
    {
        // 서비스를 사용하여 JSON 파일 로드
        $jsonConfigService = new JsonConfigService;
        $this->jsonData = $jsonConfigService->loadFromControllerPath(__DIR__);
    }

    /**
     * Single Action __invoke method
     * 상세 정보 표시
     */
    public function __invoke(Request $request, $id)
    {
        // Eloquent 모델로 데이터 조회 (관계 포함)
        $session = \Jiny\Admin\Models\AdminUserSession::with('user')->find($id);

        if (! $session) {
            $redirectUrl = isset($this->jsonData['route']['name'])
                ? route($this->jsonData['route']['name'])
                : '/admin/user/sessions';

            return redirect($redirectUrl)
                ->with('error', '세션을 찾을 수 없습니다.');
        }

        // 모델을 배열로 변환
        $data = $session->toArray();

        // 추가 정보 계산
        if ($session->last_activity) {
            $lastActivity = \Carbon\Carbon::parse($session->last_activity);
            $data['last_activity_human'] = $lastActivity->diffForHumans();
            $data['session_duration'] = $lastActivity->diffInMinutes(\Carbon\Carbon::parse($session->login_at));
        }

        // 현재 세션인지 확인
        $data['is_current_session'] = ($session->session_id === session()->getId());

        // Apply hookShowing if exists
        if (method_exists($this, 'hookShowing')) {
            $data = $this->hookShowing(null, $data);
        }

        // route 정보를 jsonData에 추가
        if (isset($this->jsonData['route']['name'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route']['name'];
        } elseif (isset($this->jsonData['route']) && is_string($this->jsonData['route'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route'];
        }

        // template.show view 경로 확인
        if (! isset($this->jsonData['template']['show'])) {
            return response('Error: 화면을 출력하기 위한 template.show 설정이 필요합니다.', 500);
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__.DIRECTORY_SEPARATOR.'AdminSessions.json';
        $settingsPath = $jsonPath; // settings drawer를 위한 경로

        // Set title from data or use default
        $title = '세션 상세 정보';
        if (isset($data['user']['name'])) {
            $subtitle = $data['user']['name'].'의 세션';
        } else {
            $subtitle = '세션 ID: '.substr($data['session_id'], 0, 8).'...';
        }

        // controllerClass를 jsonData에 추가 (AdminSessions 클래스로 지정)
        $this->jsonData['controllerClass'] = \Jiny\Admin\App\Http\Controllers\Admin\AdminSessions\AdminSessions::class;

        return view($this->jsonData['template']['show'], [
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'data' => $data,
            'id' => $id,
            'title' => $title,
            'subtitle' => $subtitle,
            'session' => $session,
            'controllerClass' => self::class,
        ]);
    }

    /**
     * 상세보기 표시 전에 호출됩니다.
     */
    public function hookShowing($wire, $data)
    {
        // 날짜 형식 지정
        $dateFormat = $this->jsonData['show']['display']['datetimeFormat'] ?? 'Y-m-d H:i:s';

        if (isset($data['created_at'])) {
            $data['created_at_formatted'] = date($dateFormat, strtotime($data['created_at']));
        }

        if (isset($data['updated_at'])) {
            $data['updated_at_formatted'] = date($dateFormat, strtotime($data['updated_at']));
        }

        // Boolean 라벨 처리
        $booleanLabels = $this->jsonData['show']['display']['booleanLabels'] ?? [
            'true' => 'Enabled',
            'false' => 'Disabled',
        ];

        if (isset($data['enable'])) {
            $data['enable_label'] = $data['enable'] ? $booleanLabels['true'] : $booleanLabels['false'];
        }

        return $data;
    }

    /**
     * Hook: 조회 후 데이터 가공
     */
    public function hookShowed($wire, $data)
    {
        return $data;
    }

    /**
     * 커스텀 Hook: 세션 종료
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  array  $params  파라미터
     * @return void
     */
    public function hookCustomTerminate($wire, $params)
    {
        $id = $params['id'] ?? null;

        if (! $id) {
            session()->flash('error', '세션 ID가 필요합니다.');

            return;
        }

        try {
            $session = \Jiny\Admin\Models\AdminUserSession::find($id);

            if (! $session) {
                session()->flash('error', '세션을 찾을 수 없습니다.');

                return;
            }

            // 자기 자신의 세션은 종료할 수 없음
            if ($session->session_id === session()->getId()) {
                session()->flash('warning', '현재 사용 중인 세션은 종료할 수 없습니다.');

                return;
            }

            // 이미 종료된 세션인지 확인
            if (! $session->is_active) {
                session()->flash('info', '이미 종료된 세션입니다.');

                return;
            }

            // 세션을 비활성 상태로 변경
            $session->is_active = false;
            $session->save();

            // 로그 기록
            \Jiny\Admin\Models\AdminUserLog::log('session_terminated', auth()->user(), [
                'terminated_session_id' => $session->session_id,
                'terminated_user_id' => $session->user_id,
                'terminated_user_email' => $session->user->email ?? 'Unknown',
                'ip_address' => request()->ip(),
            ]);

            session()->flash('success', '세션이 성공적으로 종료되었습니다.');

            // 목록 페이지로 리다이렉트를 위해 특별한 플래그 반환
            return ['redirect' => route('admin.user.sessions')];
        } catch (\Exception $e) {
            \Log::error('Session termination failed: '.$e->getMessage());
            session()->flash('error', '세션 종료 중 오류가 발생했습니다.');
        }
    }

    /**
     * 커스텀 Hook: 세션 재발급
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  array  $params  파라미터
     * @return void
     */
    public function hookCustomRegenerate($wire, $params)
    {
        $id = $params['id'] ?? null;

        if (! $id) {
            session()->flash('error', '세션 ID가 필요합니다.');

            return;
        }

        try {
            $session = \Jiny\Admin\Models\AdminUserSession::find($id);

            if (! $session) {
                session()->flash('error', '세션을 찾을 수 없습니다.');

                return;
            }

            // 현재 사용 중인 세션만 재발급 가능
            if ($session->session_id === session()->getId()) {
                // 새로운 세션 ID 생성
                request()->session()->regenerate();

                // 데이터베이스 업데이트
                $newSessionId = session()->getId();
                $oldSessionId = $session->session_id;
                $session->session_id = $newSessionId;
                $session->last_activity = now();
                $session->save();

                // 로그 기록
                \Jiny\Admin\Models\AdminUserLog::log('session_regenerated', auth()->user(), [
                    'old_session_id' => $oldSessionId,
                    'new_session_id' => $newSessionId,
                    'ip_address' => request()->ip(),
                ]);

                session()->flash('success', '세션이 재발급되었습니다. 새 세션 ID: '.substr($newSessionId, 0, 8).'...');

                // 데이터 새로고침
                if ($wire && method_exists($wire, 'refreshData')) {
                    $wire->refreshData();
                }
            } else {
                session()->flash('warning', '현재 사용 중인 세션만 재발급할 수 있습니다.');
            }
        } catch (\Exception $e) {
            \Log::error('Session regeneration failed: '.$e->getMessage());
            session()->flash('error', '세션 재발급 중 오류가 발생했습니다.');
        }
    }

    /**
     * Hook: 삭제 전 처리
     * 세션 삭제 전에 추가 검증이나 처리를 수행합니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  array  $ids  삭제할 ID 목록
     * @param  string  $type  'single' 또는 'multiple'
     * @return mixed false 반환시 삭제 중단, 문자열 반환시 에러 메시지
     */
    public function hookDeleting($wire, $ids, $type)
    {
        // 각 세션에 대해 검증
        foreach ($ids as $id) {
            $session = \Jiny\Admin\Models\AdminUserSession::find($id);

            if ($session && $session->session_id === session()->getId()) {
                return '현재 사용 중인 세션은 삭제할 수 없습니다.';
            }
        }

        // 삭제 로그 기록
        foreach ($ids as $id) {
            $session = \Jiny\Admin\Models\AdminUserSession::find($id);
            if ($session) {
                \Jiny\Admin\Models\AdminUserLog::log('session_deleted', auth()->user(), [
                    'deleted_session_id' => $session->session_id,
                    'deleted_user_id' => $session->user_id,
                    'deleted_user_email' => $session->user->email ?? 'Unknown',
                    'ip_address' => request()->ip(),
                ]);
            }
        }

        // 삭제 진행
        return true;
    }

    /**
     * Hook: 삭제 후 처리
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  array  $ids  삭제된 ID 목록
     * @param  int  $deletedCount  실제 삭제된 개수
     * @return void
     */
    public function hookDeleted($wire, $ids, $deletedCount)
    {
        // 필요 시 삭제 후 추가 처리
    }
}
