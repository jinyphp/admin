<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminDashboard;

use App\Http\Controllers\Controller;
use Jiny\Admin\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Admin\Models\AdminPasswordLog;
use Jiny\Admin\Models\AdminUserLog;
use Jiny\Admin\Models\AdminUserSession;
use Jiny\Admin\Services\JsonConfigService;

/**
 * 관리자 대시보드 컨트롤러
 *
 * 시스템 전체 현황과 통계를 시각화하여 관리자에게 제공합니다.
 * Single Action Controller 패턴을 사용하여 __invoke 메서드로 처리합니다.
 *
 * @package Jiny\Admin\App\Http\Controllers\Admin\AdminDashboard
 * @author  @jiny/admin Team
 * @since   1.0.0
 * 
 * ## 메소드 호출 트리
 * ```
 * __invoke()
 * ├── loadJsonData() [via JsonConfigService in constructor]
 * ├── [AJAX 요청 처리]
 * │   ├── getLoginTrend()
 * │   └── getBrowserStats()
 * └── [일반 요청 처리]
 *     ├── 통계 데이터 수집
 *     │   ├── User::count()
 *     │   ├── AdminUserSession::where()->count()
 *     │   ├── AdminUserLog::where()->count()
 *     │   └── AdminPasswordLog::where()->count()
 *     ├── getLoginTrend()
 *     ├── getBrowserStats()
 *     ├── getActionColor() [for each activity]
 *     ├── getActionLabel() [for each activity]
 *     ├── getActionIcon() [for each activity]
 *     └── view() 렌더링
 * ```
 */
class AdminDashboard extends Controller
{
    /**
     * JSON 설정 데이터
     *
     * @var array|null
     */
    private $jsonData;

    /**
     * 컨트롤러 생성자
     *
     * AdminDashboard.json 설정 파일을 로드하여 컨트롤러를 초기화합니다.
     */
    public function __construct()
    {
        // 서비스를 사용하여 JSON 파일 로드
        $jsonConfigService = new JsonConfigService;
        $this->jsonData = $jsonConfigService->loadFromControllerPath(__DIR__);

        // JSON 파일이 없으면 기본값 사용
        // if (!$this->jsonData) {
        //     $this->jsonData = $this->getDefaultJsonData();
        // }
    }

    /**
     * 기본 JSON 데이터 반환
     *
     * JSON 파일이 없거나 읽기 실패 시 사용할 기본 설정값을 반환합니다.
     *
     * @return array 기본 설정 데이터
     */
    // private function getDefaultJsonData()
    // {
    //     return [
    //         'title' => '관리자 대시보드',
    //         'subtitle' => '시스템 전체 현황을 한눈에 확인하세요',
    //         'template' => [
    //             'layout' => 'jiny-admin::layouts.admin',
    //         ]
    //     ];
    // }

    /**
     * 대시보드 메인 페이지 표시
     *
     * 다양한 통계 데이터를 수집하고 대시보드 뷰를 렌더링합니다:
     * - 사용자 통계 (전체, 관리자, 활성 세션 등)
     * - 보안 통계 (2FA 사용률, 차단된 IP, 실패한 로그인 시도)
     * - 최근 활동 로그
     * - 활성 세션 목록
     * - 시간별 로그인 트렌드
     * - 브라우저 사용 통계
     * - 시스템 상태 정보
     *
     * @param  Request  $request  HTTP 요청 객체
     * @return \Illuminate\View\View 대시보드 뷰
     */
    public function __invoke(Request $request)
    {
        // AJAX 요청 처리 (차트 데이터만 반환)
        if ($request->ajax() || $request->input('ajax')) {
            return response()->json([
                'login_trend' => $this->getLoginTrend(),
                'browser_stats' => $this->getBrowserStats(),
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ]);
        }

        $data = $this->jsonData;

        // 기본 통계
        $data['stats'] = [
            'total_users' => User::count(),
            'admin_users' => User::where('utype', 'super')->orWhere('utype', 'admin')->count(),
            'active_sessions' => AdminUserSession::where('is_active', true)->count(),
            'today_logins' => AdminUserLog::where('action', 'login')
                ->whereDate('logged_at', Carbon::today())
                ->count(),
        ];

        // 2FA 통계
        $data['security'] = [
            'two_factor_enabled' => User::where('two_factor_enabled', true)->count(),
            'two_factor_percentage' => User::count() > 0
                ? round((User::where('two_factor_enabled', true)->count() / User::count()) * 100, 1)
                : 0,
            'blocked_ips' => AdminPasswordLog::where('is_blocked', true)
                ->where('status', 'blocked')
                ->distinct('ip_address')
                ->count('ip_address'),
            'failed_attempts_today' => AdminPasswordLog::whereDate('created_at', Carbon::today())
                ->whereIn('status', ['failed', 'blocked'])
                ->count(),
        ];

        // 최근 활동
        $data['recent_activities'] = AdminUserLog::with('user')
            ->orderBy('logged_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'user_id' => $log->user_id,
                    'email' => $log->email,
                    'name' => $log->name,
                    'action' => $log->action,
                    'ip_address' => $log->ip_address,
                    'browser' => $log->browser,
                    'logged_at' => $log->logged_at,
                    'details' => $log->details,
                    'color' => $this->getActionColor($log->action),
                    'label' => $this->getActionLabel($log->action),
                    'icon' => $this->getActionIcon($log->action),
                ];
            });

        // 활성 세션
        $data['active_sessions'] = AdminUserSession::with('user')
            ->where('is_active', true)
            ->orderBy('last_activity_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'user_id' => $session->user_id,
                    'email' => $session->user ? $session->user->email : 'Unknown',
                    'ip_address' => $session->ip_address,
                    'browser' => $session->browser,
                    'last_activity' => $session->last_activity_at,
                    'login_at' => $session->login_at,
                    'is_current' => $session->session_id === session()->getId(),
                ];
            });

        // 시간별 로그인 트렌드 (최근 24시간)
        $data['login_trend'] = $this->getLoginTrend();

        // 브라우저 통계
        $data['browser_stats'] = $this->getBrowserStats();

        // 최근 차단된 IP
        $data['recent_blocks'] = AdminPasswordLog::where('is_blocked', true)
            ->orderBy('blocked_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'email' => $log->email,
                    'ip_address' => $log->ip_address,
                    'attempt_count' => $log->attempt_count,
                    'blocked_at' => $log->blocked_at,
                    'browser' => $log->browser,
                ];
            });

        // 시스템 상태
        $data['system_status'] = [
            'php_version' => phpversion(),
            'laravel_version' => app()->version(),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
            'debug_mode' => config('app.debug') ? 'On' : 'Off',
            'environment' => app()->environment(),
        ];

        return view('jiny-admin::admin.admin_dashboard.dashboard', $data);
    }

    /**
     * 최근 24시간 로그인 트렌드 데이터 생성
     *
     * 시간별 로그인 횟수를 집계하여 차트 데이터를 생성합니다.
     *
     * @return array 시간 라벨과 로그인 횟수 데이터
     */
    private function getLoginTrend()
    {
        $hours = [];
        $counts = [];

        // 시간대 설정 (Asia/Seoul)
        $timezone = config('app.timezone', 'Asia/Seoul');

        for ($i = 23; $i >= 0; $i--) {
            $hour = Carbon::now($timezone)->subHours($i);
            $hours[] = $hour->format('H:00');

            // 로그인 + 2FA 인증 성공 모두 포함
            $count = AdminUserLog::whereIn('action', ['login', '2fa_verified'])
                ->whereBetween('logged_at', [
                    $hour->copy()->startOfHour()->utc(),
                    $hour->copy()->endOfHour()->utc(),
                ])
                ->count();

            $counts[] = $count;
        }

        return [
            'labels' => $hours,
            'data' => $counts,
        ];
    }

    /**
     * 브라우저별 사용 통계 생성
     *
     * 현재 활성 세션의 브라우저 분포를 분석합니다.
     *
     * @return array 브라우저명과 사용 횟수 데이터
     */
    private function getBrowserStats()
    {
        $stats = AdminUserSession::select('browser', DB::raw('count(*) as count'))
            ->where('is_active', true)
            ->groupBy('browser')
            ->orderByDesc('count')
            ->take(5)
            ->get();

        return [
            'labels' => $stats->pluck('browser')->toArray(),
            'data' => $stats->pluck('count')->toArray(),
        ];
    }

    /**
     * 액션별 색상 테마 반환
     *
     * 로그 액션 타입에 따라 UI에 표시할 색상을 결정합니다.
     *
     * @param  string  $action  액션 타입
     * @return string Tailwind CSS 색상명
     */
    private function getActionColor($action)
    {
        return match ($action) {
            'login' => 'green',
            'logout' => 'blue',
            'failed_login' => 'red',
            'password_blocked' => 'red',
            'password_unblocked' => 'green',
            'user_created' => 'indigo',
            'user_updated' => 'yellow',
            'user_deleted' => 'red',
            '2fa_enabled' => 'green',
            '2fa_disabled' => 'yellow',
            '2fa_verified' => 'green',
            '2fa_failed' => 'red',
            default => 'gray'
        };
    }

    /**
     * 액션별 한글 라벨 반환
     *
     * 로그 액션 타입을 사용자 친화적인 한글 라벨로 변환합니다.
     *
     * @param  string  $action  액션 타입
     * @return string 한글 라벨
     */
    private function getActionLabel($action)
    {
        return match ($action) {
            'login' => '로그인',
            'logout' => '로그아웃',
            'failed_login' => '로그인 실패',
            'password_blocked' => '비밀번호 차단',
            'password_unblocked' => '차단 해제',
            'user_created' => '사용자 생성',
            'user_updated' => '사용자 수정',
            'user_deleted' => '사용자 삭제',
            '2fa_enabled' => '2FA 활성화',
            '2fa_disabled' => '2FA 비활성화',
            '2fa_verified' => '2FA 인증',
            '2fa_failed' => '2FA 실패',
            default => $action
        };
    }

    /**
     * 액션별 SVG 아이콘 경로 반환
     *
     * 로그 액션 타입에 따라 표시할 SVG 아이콘의 경로를 반환합니다.
     *
     * @param  string  $action  액션 타입
     * @return string SVG path 데이터
     */
    private function getActionIcon($action)
    {
        return match ($action) {
            'login' => 'M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1',
            'logout' => 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1',
            'failed_login' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
            'password_blocked' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
            'user_created' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
            '2fa_enabled' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
            default => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
        };
    }
}
