<?php

namespace Jiny\Admin\Http\Controllers\Web\Login;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Jiny\Admin\Models\AdminUnlockToken;

class UnlockAccount extends Controller
{
    /**
     * 잠금 해제 토큰 유효 시간 (분)
     */
    const TOKEN_EXPIRY_MINUTES = 60;
    
    /**
     * 잠금 해제 페이지 표시
     *
     * @param Request $request
     * @param string $token
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(Request $request, $token)
    {
        // 토큰 검증
        $unlockToken = $this->validateToken($token);
        
        if (!$unlockToken) {
            return redirect()->route('admin.login')
                ->with('error', '유효하지 않거나 만료된 잠금 해제 링크입니다.');
        }
        
        // 사용자 정보 조회
        $user = DB::table('users')->where('id', $unlockToken->user_id)->first();
        
        if (!$user) {
            return redirect()->route('admin.login')
                ->with('error', '사용자를 찾을 수 없습니다.');
        }
        
        // 이미 잠금 해제된 경우
        if (!$user->is_locked) {
            // 토큰 삭제
            DB::table('admin_unlock_tokens')->where('id', $unlockToken->id)->delete();
            
            return redirect()->route('admin.login')
                ->with('info', '계정이 이미 잠금 해제되었습니다.');
        }
        
        return view('jiny-admin::web.login.unlock', [
            'token' => $token,
            'email' => $user->email,
            'masked_email' => $this->maskEmail($user->email)
        ]);
    }
    
    /**
     * 잠금 해제 처리
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function unlock(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'password' => 'required|string|min:8',
            'security_answer' => 'nullable|string'
        ]);
        
        // 토큰 검증
        $unlockToken = $this->validateToken($request->token);
        
        if (!$unlockToken) {
            return back()->with('error', '유효하지 않거나 만료된 잠금 해제 링크입니다.');
        }
        
        // 사용자 조회
        $user = DB::table('users')->where('id', $unlockToken->user_id)->first();
        
        if (!$user) {
            return back()->with('error', '사용자를 찾을 수 없습니다.');
        }
        
        // 비밀번호 확인
        if (!Hash::check($request->password, $user->password)) {
            // 실패 횟수 증가
            DB::table('admin_unlock_tokens')
                ->where('id', $unlockToken->id)
                ->increment('attempts');
            
            // 5회 이상 실패 시 토큰 무효화
            if ($unlockToken->attempts >= 4) {
                DB::table('admin_unlock_tokens')
                    ->where('id', $unlockToken->id)
                    ->update(['used_at' => now()]);
                
                Log::warning('잠금 해제 시도 횟수 초과', [
                    'user_id' => $user->id,
                    'ip' => $request->ip()
                ]);
                
                return redirect()->route('admin.login')
                    ->with('error', '잠금 해제 시도 횟수를 초과했습니다. 새로운 링크를 요청하세요.');
            }
            
            return back()->with('error', '비밀번호가 올바르지 않습니다.');
        }
        
        // 보안 질문 확인 (설정된 경우)
        if ($user->security_question && $request->security_answer) {
            $hashedAnswer = hash('sha256', strtolower(trim($request->security_answer)));
            if ($user->security_answer !== $hashedAnswer) {
                DB::table('admin_unlock_tokens')
                    ->where('id', $unlockToken->id)
                    ->increment('attempts');
                
                return back()->with('error', '보안 답변이 올바르지 않습니다.');
            }
        }
        
        DB::beginTransaction();
        
        try {
            // 계정 잠금 해제
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'is_locked' => false,
                    'locked_at' => null,
                    'lock_reason' => null,
                    'failed_login_attempts' => 0,
                    'unlocked_at' => now()
                ]);
            
            // 토큰 사용 처리
            DB::table('admin_unlock_tokens')
                ->where('id', $unlockToken->id)
                ->update(['used_at' => now()]);
            
            // 잠금 해제 로그 기록
            DB::table('admin_logs')->insert([
                'user_id' => $user->id,
                'action' => 'account_unlocked',
                'description' => '계정 잠금 해제 (토큰 사용)',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => json_encode([
                    'token_id' => $unlockToken->id,
                    'method' => 'token'
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // 다른 미사용 토큰들 무효화
            DB::table('admin_unlock_tokens')
                ->where('user_id', $user->id)
                ->whereNull('used_at')
                ->where('id', '!=', $unlockToken->id)
                ->update(['expired_at' => now()]);
            
            DB::commit();
            
            Log::info('계정 잠금 해제 성공', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);
            
            return redirect()->route('admin.login')
                ->with('success', '계정이 성공적으로 잠금 해제되었습니다. 이제 로그인할 수 있습니다.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('계정 잠금 해제 실패', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', '잠금 해제 중 오류가 발생했습니다. 잠시 후 다시 시도해주세요.');
        }
    }
    
    /**
     * 새 잠금 해제 링크 요청 페이지
     *
     * @return \Illuminate\View\View
     */
    public function requestForm()
    {
        return view('jiny-admin::web.login.request-unlock');
    }
    
    /**
     * 새 잠금 해제 링크 생성 및 발송
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendUnlockLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);
        
        // 사용자 조회
        $user = DB::table('users')->where('email', $request->email)->first();
        
        // 보안을 위해 사용자가 없어도 동일한 메시지 표시
        if (!$user) {
            return back()->with('success', '입력하신 이메일로 잠금 해제 링크를 발송했습니다. (등록된 경우)');
        }
        
        // 잠긴 계정인지 확인
        if (!$user->is_locked) {
            return back()->with('info', '해당 계정은 잠겨있지 않습니다.');
        }
        
        // 최근 발송 이력 확인 (스팸 방지)
        $recentToken = DB::table('admin_unlock_tokens')
            ->where('user_id', $user->id)
            ->where('created_at', '>=', Carbon::now()->subMinutes(5))
            ->whereNull('used_at')
            ->first();
        
        if ($recentToken) {
            return back()->with('warning', '최근에 이미 잠금 해제 링크를 발송했습니다. 5분 후에 다시 시도해주세요.');
        }
        
        DB::beginTransaction();
        
        try {
            // 새 토큰 생성
            $token = $this->generateSecureToken();
            $expiresAt = Carbon::now()->addMinutes(self::TOKEN_EXPIRY_MINUTES);
            
            DB::table('admin_unlock_tokens')->insert([
                'user_id' => $user->id,
                'token' => hash('sha256', $token), // 해시로 저장
                'expires_at' => $expiresAt,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // 잠금 해제 URL 생성
            $unlockUrl = route('account.unlock.show', ['token' => $token]);
            
            // 알림 서비스 사용
            $notificationService = app(\jiny\admin\App\Services\NotificationService::class);
            
            // 이메일 발송
            $notificationService->notifyAccountLocked(
                $user->id,
                $user->lock_reason ?? '보안 정책 위반',
                [
                    'unlock_url' => $unlockUrl,
                    'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
                    'expires_in_minutes' => self::TOKEN_EXPIRY_MINUTES
                ]
            );
            
            // SMS 발송 (전화번호가 있는 경우)
            if ($user->phone_number) {
                $smsService = app(\jiny\admin\App\Services\SmsService::class);
                if ($smsService->isEnabled()) {
                    $smsService->sendAccountLockedSms(
                        $user->phone_number,
                        $user->name ?? 'User',
                        $unlockUrl,
                        self::TOKEN_EXPIRY_MINUTES
                    );
                }
            }
            
            DB::commit();
            
            Log::info('잠금 해제 링크 발송', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);
            
            return back()->with('success', '입력하신 이메일로 잠금 해제 링크를 발송했습니다.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('잠금 해제 링크 발송 실패', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', '링크 발송 중 오류가 발생했습니다. 잠시 후 다시 시도해주세요.');
        }
    }
    
    /**
     * 토큰 검증
     *
     * @param string $token
     * @return object|null
     */
    protected function validateToken($token)
    {
        // 모델의 검증 메서드 사용 가능하지만, 기존 로직 유지를 위해 DB 쿼리 사용
        $hashedToken = hash('sha256', $token);
        
        return DB::table('admin_unlock_tokens')
            ->where('token', $hashedToken)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();
    }
    
    /**
     * 안전한 토큰 생성
     *
     * @return string
     */
    protected function generateSecureToken()
    {
        return Str::random(64);
    }
    
    /**
     * 이메일 마스킹
     *
     * @param string $email
     * @return string
     */
    protected function maskEmail($email)
    {
        $parts = explode('@', $email);
        $name = $parts[0];
        $domain = $parts[1] ?? '';
        
        if (strlen($name) <= 3) {
            $masked = str_repeat('*', strlen($name));
        } else {
            $masked = substr($name, 0, 2) . str_repeat('*', strlen($name) - 3) . substr($name, -1);
        }
        
        return $masked . '@' . $domain;
    }
}