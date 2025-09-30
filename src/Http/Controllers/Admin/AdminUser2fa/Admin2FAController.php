<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminUser2fa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Admin\Services\TwoFactorAuthService;
use Jiny\Admin\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class Admin2FAController extends Controller
{
    protected $twoFactorService;

    public function __construct(TwoFactorAuthService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    /**
     * SMS 코드 발송
     */
    public function sendSmsCode(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        
        // 권한 확인
        if (!auth()->user()->can('manage-2fa') && auth()->id() !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => '권한이 없습니다.'
            ], 403);
        }

        $result = $this->twoFactorService->sendSmsCode($user);
        
        return response()->json($result);
    }

    /**
     * 이메일 코드 발송
     */
    public function sendEmailCode(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        
        // 권한 확인
        if (!auth()->user()->can('manage-2fa') && auth()->id() !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => '권한이 없습니다.'
            ], 403);
        }

        $result = $this->twoFactorService->sendEmailCode($user);
        
        return response()->json($result);
    }

    /**
     * SMS 코드 검증 및 2FA 활성화
     */
    public function verifySmsCode(Request $request, $userId)
    {
        $request->validate([
            'code' => 'required|string|size:6'
        ]);

        $user = User::findOrFail($userId);
        
        // 권한 확인
        if (!auth()->user()->can('manage-2fa') && auth()->id() !== $user->id) {
            return back()->with('error', '권한이 없습니다.');
        }

        // SMS 코드 검증
        if ($this->twoFactorService->verifySmsCode($user, $request->code)) {
            // 2FA 활성화
            $user->two_factor_enabled = true;
            $user->two_factor_method = 'sms';
            $user->two_factor_confirmed_at = now();
            
            // 백업 코드 생성
            $backupCodes = $this->twoFactorService->generateBackupCodes();
            $user->two_factor_recovery_codes = encrypt(json_encode($backupCodes));
            
            $user->save();
            
            // 세션에 백업 코드 저장 (한 번만 표시)
            session()->flash('backup_codes', $backupCodes);
            
            return redirect()->route('admin.system.user.2fa.show', $userId)
                ->with('success', 'SMS 2FA가 성공적으로 활성화되었습니다.');
        }

        return back()->with('error', '잘못된 인증 코드입니다.');
    }

    /**
     * 이메일 코드 검증 및 2FA 활성화
     */
    public function verifyEmailCode(Request $request, $userId)
    {
        $request->validate([
            'code' => 'required|string|size:6'
        ]);

        $user = User::findOrFail($userId);
        
        // 권한 확인
        if (!auth()->user()->can('manage-2fa') && auth()->id() !== $user->id) {
            return back()->with('error', '권한이 없습니다.');
        }

        // 이메일 코드 검증
        if ($this->twoFactorService->verifyEmailCode($user, $request->code)) {
            // 2FA 활성화
            $user->two_factor_enabled = true;
            $user->two_factor_method = 'email';
            $user->two_factor_confirmed_at = now();
            
            // 백업 코드 생성
            $backupCodes = $this->twoFactorService->generateBackupCodes();
            $user->two_factor_recovery_codes = encrypt(json_encode($backupCodes));
            
            $user->save();
            
            // 세션에 백업 코드 저장 (한 번만 표시)
            session()->flash('backup_codes', $backupCodes);
            
            return redirect()->route('admin.system.user.2fa.show', $userId)
                ->with('success', '이메일 2FA가 성공적으로 활성화되었습니다.');
        }

        return back()->with('error', '잘못된 인증 코드입니다.');
    }

    /**
     * 2FA 방법 변경
     */
    public function changeMethod(Request $request, $userId)
    {
        $request->validate([
            'method' => 'required|in:totp,sms,email'
        ]);

        $user = User::findOrFail($userId);
        
        // 권한 확인
        if (!auth()->user()->can('manage-2fa') && auth()->id() !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => '권한이 없습니다.'
            ], 403);
        }

        if ($this->twoFactorService->changeMethod($user, $request->method)) {
            return response()->json([
                'success' => true,
                'message' => '2FA 방법이 변경되었습니다.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => '2FA 방법 변경에 실패했습니다.'
        ]);
    }

    /**
     * 백업 코드 재생성
     */
    public function regenerateBackupCodes(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        
        // 권한 확인
        if (!auth()->user()->can('manage-2fa') && auth()->id() !== $user->id) {
            return back()->with('error', '권한이 없습니다.');
        }

        $backupCodes = $this->twoFactorService->regenerateBackupCodesEnhanced($user);
        
        if ($backupCodes) {
            // 세션에 백업 코드 저장
            session()->flash('backup_codes', $backupCodes);
            
            return back()->with('success', '백업 코드가 재생성되었습니다.');
        }

        return back()->with('error', '백업 코드 재생성에 실패했습니다.');
    }

    /**
     * 백업 코드 다운로드
     */
    public function downloadBackupCodes(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        
        // 권한 확인
        if (!auth()->user()->can('manage-2fa') && auth()->id() !== $user->id) {
            abort(403);
        }

        $text = $this->twoFactorService->generateBackupCodesText($user);
        
        if (!$text) {
            return back()->with('error', '백업 코드가 없습니다.');
        }

        $filename = "2fa-backup-codes-{$user->id}-" . now()->format('Y-m-d') . ".txt";
        
        return Response::make($text, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    /**
     * 2FA 상태 확인 API
     */
    public function getStatus(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        
        // 권한 확인
        if (!auth()->user()->can('manage-2fa') && auth()->id() !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => '권한이 없습니다.'
            ], 403);
        }

        $status = $this->twoFactorService->getStatusEnhanced($user);
        
        return response()->json([
            'success' => true,
            'data' => $status
        ]);
    }

    /**
     * 전화번호 업데이트 (SMS 2FA용)
     */
    public function updatePhoneNumber(Request $request, $userId)
    {
        $request->validate([
            'phone_number' => 'required|string|regex:/^[\+]?[(]?[0-9]{1,3}[)]?[-\s\.]?[(]?[0-9]{1,4}[)]?[-\s\.]?[0-9]{1,4}[-\s\.]?[0-9]{1,9}$/'
        ]);

        $user = User::findOrFail($userId);
        
        // 권한 확인
        if (!auth()->user()->can('manage-2fa') && auth()->id() !== $user->id) {
            return back()->with('error', '권한이 없습니다.');
        }

        $user->phone_number = $request->phone_number;
        $user->phone_verified = false; // 새 번호는 검증 필요
        $user->save();

        return back()->with('success', '전화번호가 업데이트되었습니다.');
    }

    /**
     * 만료된 2FA 코드 정리 (크론잡용)
     */
    public function cleanupExpiredCodes()
    {
        $deleted = $this->twoFactorService->cleanupExpiredCodes();
        
        return response()->json([
            'success' => true,
            'deleted' => $deleted,
            'message' => "{$deleted}개의 만료된 코드가 삭제되었습니다."
        ]);
    }
}