<?php

namespace Jiny\Admin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jiny\Admin\Models\AdminUserLog;
use Jiny\Admin\Models\AdminUsertype;
use Symfony\Component\HttpFoundation\Response;

/**
 * 관리자 접근 권한 미들웨어
 * 
 * 목적:
 * - 관리자 페이지 접근 권한 제어
 * - 인증되지 않은 사용자 차단
 * - 권한별 접근 제어
 * - 보안 이벤트 로깅
 * 
 * 주요 기능:
 * 1. 인증 확인 (로그인 상태)
 * 2. 관리자 권한 검증 (isAdmin, utype)
 * 3. 사용자 타입 유효성 검증 (DB 조회)
 * 4. 특정 권한 요구사항 처리
 * 5. 계정 상태 확인 (활성화/차단)
 * 6. 활동 시간 추적
 * 
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * 메소드 호출 플로우:
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * 
 * handle(Request, Closure, ?string)
 * ├─► 로그인 페이지 체크 (예외 처리)
 * │   └─► routeIs() → 로그인 페이지면 통과
 * │
 * ├─► 인증 확인
 * │   ├─ Auth::check() == false
 * │   └─► handleUnauthenticated()
 * │       ├─ session()->flash() [알림 설정]
 * │       ├─ session()->put() [원래 URL 저장]
 * │       └─ redirect() 또는 json() [응답 반환]
 * │
 * ├─► 권한 검증
 * │   └─► validateAdminAccess(Request, User, ?string)
 * │       ├─ isAdmin 체크
 * │       ├─ utype 존재 확인
 * │       ├─ AdminUsertype::where() [DB 검증]
 * │       ├─ 특정 타입 요구사항 체크
 * │       ├─ 활성화 상태 확인
 * │       └─ 차단 상태 확인
 * │           └─► 실패 시: logUnauthorizedAccess() → denyAccess()
 * │
 * ├─► 활동 시간 업데이트
 * │   └─► updateLastActivity()
 * │       └─ 1분 경과 시 DB 업데이트
 * │
 * └─► 통과: $next($request)
 * 
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * 
 * @package Jiny\Admin
 * @since 1.0.0
 */
class AdminMiddleware
{
    /**
     * 로그인 라우트 이름
     * 
     * @var string
     */
    private string $loginRoute = 'admin.login';
    
    /**
     * 로그인 처리 라우트 이름
     * 
     * @var string
     */
    private string $loginPostRoute = 'admin.login.post';
    
    /**
     * 허용된 관리자 타입 (더 이상 하드코딩하지 않음)
     * admin_user_types 테이블에서 동적으로 확인
     *
     * @deprecated
     */
    // protected $allowedTypes = ['super', 'admin'];

    /**
     * HTTP 요청 처리 (메인 엔트리 포인트)
     *
     * @param  Request  $request  HTTP 요청 객체
     * @param  Closure  $next  다음 미들웨어
     * @param  string|null  $requiredType  특정 권한 타입 (옵션)
     * @return Response
     */
    public function handle(Request $request, Closure $next, ?string $requiredType = null): Response
    {
        // STEP 1: 로그인 페이지 예외 처리
        if ($request->routeIs($this->loginRoute, $this->loginPostRoute)) {
            return $next($request);
        }

        // STEP 2: 인증 상태 확인
        if (! Auth::check()) {
            return $this->handleUnauthenticated($request);
        }

        $user = Auth::user();

        // STEP 3: 관리자 권한 검증
        $validationResult = $this->validateAdminAccess($request, $user, $requiredType);
        if ($validationResult !== null) {
            return $validationResult;
        }

        // STEP 4: 활동 시간 기록
        $this->updateLastActivity($user);

        // STEP 5: 모든 검증 통과 - 다음 미들웨어로 진행
        return $next($request);
    }

    /**
     * 접근 거부 처리 (403 Forbidden)
     * 
     * @param  Request  $request  HTTP 요청 객체
     * @param  string  $message  거부 사유 메시지
     * @return Response  403 응답 또는 리다이렉트
     */
    protected function denyAccess(Request $request, string $message): Response
    {
        session()->flash('notification', [
            'type' => 'error',
            'title' => '접근 거부',
            'message' => $message,
        ]);

        // AJAX 요청인 경우 JSON 응답
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => $message,
            ], 403);
        }

        // 일반 요청인 경우 로그인 페이지로 리다이렉트
        return redirect()->route($this->loginRoute);
    }

    /**
     * 보안 이벤트 로그 기록 (권한 없는 접근 시도)
     * 
     * @param  Request  $request  HTTP 요청 객체
     * @param  string  $reason  접근 거부 사유
     * @return void
     */
    protected function logUnauthorizedAccess(Request $request, string $reason): void
    {
        $user = Auth::user();

        AdminUserLog::log('unauthorized_access', $user, [
            'url' => $request->url(),
            'method' => $request->method(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'reason' => $reason,
            'user_id' => $user ? $user->id : null,
            'email' => $user ? $user->email : 'Guest',
            'utype' => $user ? $user->utype : null,
        ]);
    }

    /**
     * 사용자 활동 시간 기록 (1분 단위 업데이트)
     * 
     * @param  mixed  $user  현재 인증된 사용자
     * @return void
     */
    protected function updateLastActivity($user): void
    {
        // DB 부하 감소를 위해 1분 단위로 업데이트
        if (! $user->last_activity_at || now()->diffInMinutes($user->last_activity_at) >= 1) {
            $user->last_activity_at = now();
            $user->save();
        }
    }
    
    /**
     * 미인증 사용자 처리 (401 Unauthorized)
     * 
     * @param  Request  $request  HTTP 요청 객체
     * @return Response  401 응답 또는 리다이렉트
     */
    protected function handleUnauthenticated(Request $request): Response
    {
        // 세션에 알림 메시지 설정
        session()->flash('notification', [
            'type' => 'error',
            'title' => '인증 필요',
            'message' => '로그인이 필요한 서비스입니다.',
        ]);

        // AJAX 요청인 경우 JSON 응답
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => '로그인이 필요합니다.',
            ], 401);
        }

        // 접근하려던 URL을 세션에 저장 (로그인 후 리다이렉트용)
        session()->put('url.intended', $request->url());

        return redirect()->route($this->loginRoute);
    }
    
    /**
     * 관리자 권한 검증 체크
     * 
     * 검증 단계:
     * 1. isAdmin 플래그 확인
     * 2. utype 필드 존재 확인
     * 3. DB에서 사용자 타입 유효성 검증
     * 4. 특정 권한 타입 요구사항 검증 (선택사항)
     * 5. 사용자 타입 활성화 상태 확인
     * 6. 계정 차단 여부 확인
     * 
     * @param  Request  $request  HTTP 요청 객체
     * @param  mixed  $user  인증된 사용자 객체
     * @param  string|null  $requiredType  특정 권한 타입 (선택)
     * @return Response|null  실패 시 Response, 성공 시 null
     */
    protected function validateAdminAccess(Request $request, $user, ?string $requiredType = null): ?Response
    {
        // 1. isAdmin 플래그 확인
        if (! $user->isAdmin) {
            $this->logUnauthorizedAccess($request, 'Not an admin user');
            return $this->denyAccess($request, '관리자 권한이 없습니다.');
        }

        // 2. utype 필드 존재 확인
        if (! $user->utype) {
            $this->logUnauthorizedAccess($request, 'User type not set');
            return $this->denyAccess($request, '사용자 타입이 설정되지 않았습니다.');
        }

        // 3. DB에서 사용자 타입 유효성 검증
        $adminUserType = AdminUsertype::where('code', $user->utype)->first();
        if (! $adminUserType) {
            $this->logUnauthorizedAccess($request, "User type not found in admin_user_types: {$user->utype}");
            return $this->denyAccess($request, '등록되지 않은 사용자 타입입니다.');
        }

        // 4. 특정 권한 타입 요구사항 검증 (선택사항)
        if ($requiredType) {
            if ($user->utype !== $requiredType) {
                // 요청된 타입이 admin_user_types에 있는지도 확인
                $requiredAdminType = AdminUsertype::where('code', $requiredType)->first();
                if (! $requiredAdminType) {
                    $this->logUnauthorizedAccess($request, "Invalid required type: {$requiredType}");
                    return $this->denyAccess($request, "잘못된 권한 타입입니다: {$requiredType}");
                }

                $this->logUnauthorizedAccess($request, "Required type: {$requiredType}, User type: {$user->utype}");
                return $this->denyAccess($request, "이 기능은 {$requiredAdminType->title} 권한이 필요합니다.");
            }
        }

        // 5. 사용자 타입 활성화 상태 확인
        if (isset($adminUserType->enable) && ! $adminUserType->enable) {
            $this->logUnauthorizedAccess($request, "User type is disabled: {$user->utype}");
            return $this->denyAccess($request, '비활성화된 사용자 타입입니다.');
        }

        // 6. 계정 차단 여부 확인
        if (isset($user->is_blocked) && $user->is_blocked) {
            $this->logUnauthorizedAccess($request, 'User account is blocked');

            // 차단된 계정은 즉시 로그아웃 처리
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return $this->denyAccess($request, '계정이 차단되었습니다. 관리자에게 문의하세요.');
        }
        
        // 모든 검증 통과
        return null;
    }
}
