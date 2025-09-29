<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminSessions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Admin\Services\JsonConfigService;

class AdminSessions extends Controller
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
     * 목록 조회 처리
     */
    public function __invoke(Request $request)
    {
        // JSON 데이터 확인
        if (! $this->jsonData) {
            return response('Error: JSON configuration file not found or invalid.', 500);
        }

        // template.index view 경로 확인
        if (! isset($this->jsonData['template']['index'])) {
            return response('Error: 화면을 출력하기 위한 template.index 설정이 필요합니다.', 500);
        }

        // route 정보를 jsonData에 추가
        if (isset($this->jsonData['route']['name'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route']['name'];
        } elseif (isset($this->jsonData['route']) && is_string($this->jsonData['route'])) {
            // 이전 버전 호환성
            $this->jsonData['currentRoute'] = $this->jsonData['route'];
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__.DIRECTORY_SEPARATOR.'AdminSessions.json';
        $settingsPath = $jsonPath; // settings drawer를 위한 경로

        // 컨트롤러 클래스를 JSON 데이터에 추가
        $this->jsonData['controllerClass'] = get_class($this);

        return view($this->jsonData['template']['index'], [
            'controllerClass' => static::class,  // 현재 컨트롤러 클래스 전달
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
        ]);
    }

    /**
     * Hook: Livewire 컴포넌트의 데이터 조회 전 실행
     * 데이터베이스 쿼리 조건을 수정하거나 추가 로직을 실행할 수 있습니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
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
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  mixed  $rows  조회된 데이터
     * @return mixed 가공된 데이터
     */
    public function hookIndexed($wire, $rows)
    {
        return $rows;
    }

    /**
     * Hook: 테이블 헤더 커스터마이징
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @return array 커스터마이징된 헤더 설정
     */
    public function hookTableHeader($wire)
    {
        return $this->jsonData['index']['table']['columns'] ?? [];
    }

    /**
     * Hook: 페이지네이션 설정
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @return array 페이지네이션 설정
     */
    public function hookPagination($wire)
    {
        return $this->jsonData['index']['pagination'] ?? [
            'perPage' => 10,
            'perPageOptions' => [10, 25, 50, 100],
        ];
    }

    /**
     * Hook: 정렬 설정
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @return array 정렬 설정
     */
    public function hookSorting($wire)
    {
        return $this->jsonData['index']['sorting'] ?? [
            'default' => 'created_at',
            'direction' => 'desc',
        ];
    }

    /**
     * Hook: 검색 설정
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @return array 검색 설정
     */
    public function hookSearch($wire)
    {
        return $this->jsonData['index']['search'] ?? [
            'placeholder' => 'Search sessionss...',
            'debounce' => 300,
        ];
    }

    /**
     * Hook: 필터 설정
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @return array 필터 설정
     */
    public function hookFilters($wire)
    {
        return $this->jsonData['index']['filters'] ?? [];
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

            return false;
        }

        try {
            $session = \Jiny\Admin\Models\AdminUserSession::find($id);

            if (! $session) {
                session()->flash('error', '세션을 찾을 수 없습니다.');

                return false;
            }

            // 자기 자신의 세션은 종료할 수 없음
            if ($session->session_id === session()->getId()) {
                session()->flash('warning', '현재 사용 중인 세션은 종료할 수 없습니다.');

                return false;
            }

            // 이미 종료된 세션인지 확인
            if (! $session->is_active) {
                session()->flash('info', '이미 종료된 세션입니다.');

                return false;
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

            // 테이블 새로고침을 위해 true 반환
            return true;

        } catch (\Exception $e) {
            \Log::error('Session termination failed: '.$e->getMessage());
            session()->flash('error', '세션 종료 중 오류가 발생했습니다: '.$e->getMessage());

            return false;
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
                $session->session_id = $newSessionId;
                $session->last_activity = now();
                $session->save();

                // 로그 기록
                \Jiny\Admin\Models\AdminUserLog::log('session_regenerated', auth()->user(), [
                    'old_session_id' => $session->getOriginal('session_id'),
                    'new_session_id' => $newSessionId,
                    'ip_address' => request()->ip(),
                ]);

                session()->flash('success', '세션이 재발급되었습니다. 새 세션 ID: '.substr($newSessionId, 0, 8).'...');
            } else {
                session()->flash('warning', '현재 사용 중인 세션만 재발급할 수 있습니다.');
            }
        } catch (\Exception $e) {
            \Log::error('Session regeneration failed: '.$e->getMessage());
            session()->flash('error', '세션 재발급 중 오류가 발생했습니다.');
        }
    }

    /**
     * 세션 상세 정보 표시
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show(Request $request, $id)
    {
        // JSON 데이터 확인
        if (! $this->jsonData) {
            return response('Error: JSON configuration file not found or invalid.', 500);
        }

        // template.show view 경로 확인
        if (! isset($this->jsonData['template']['show'])) {
            return response('Error: 화면을 출력하기 위한 template.show 설정이 필요합니다.', 500);
        }

        // 세션 데이터 조회 (관계 포함)
        $session = \Jiny\Admin\Models\AdminUserSession::with(['user', 'logs'])->find($id);

        if (! $session) {
            return redirect()->route('admin.user.sessions.index')
                ->with('error', '세션을 찾을 수 없습니다.');
        }

        // 데이터 배열로 변환
        $data = $session->toArray();

        // JSON 파일 경로 추가
        $jsonPath = __DIR__.DIRECTORY_SEPARATOR.'AdminSessions.json';
        $settingsPath = $jsonPath; // settings drawer를 위한 경로

        return view($this->jsonData['template']['show'], [
            'jsonData' => $this->jsonData,
            'data' => $data,
            'id' => $id,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
        ]);
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

    /**
     * Hook: 상세 보기 전 처리
     * 세션 상세 데이터를 표시하기 전에 가공합니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  array  $data  세션 데이터
     * @return array 가공된 데이터
     */
    public function hookShowing($wire, $data)
    {
        // 활동 로그가 있으면 최근 것부터 정렬
        if (isset($data['logs']) && is_array($data['logs'])) {
            $data['logs'] = array_reverse($data['logs']);
        }

        return $data;
    }
}
