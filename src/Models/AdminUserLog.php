<?php

namespace Jiny\Admin\Models;

use Jiny\Admin\Models\User;
use Illuminate\Database\Eloquent\Model;

class AdminUserLog extends Model
{
    protected $table = 'admin_user_logs';

    protected $fillable = [
        'user_id',
        'email',
        'name',
        'action',
        'ip_address',
        'user_agent',
        'details',
        'session_id',
        'logged_at',
        // 2FA 관련 필드
        'two_factor_used',
        'two_factor_method',
        'two_factor_required',
        'two_factor_verified_at',
    ];

    protected $casts = [
        'details' => 'array',
        'logged_at' => 'datetime',
        'two_factor_used' => 'boolean',
        'two_factor_required' => 'boolean',
        'two_factor_verified_at' => 'datetime',
    ];

    /**
     * 관련 사용자
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 로그 기록
     */
    public static function log($action, $user = null, $details = [])
    {
        $request = request();

        $data = [
            'action' => $action,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => session()->getId(),
            'logged_at' => now(),
            'details' => $details,
        ];

        // 2FA 정보 추가
        if (isset($details['two_factor_used'])) {
            $data['two_factor_used'] = $details['two_factor_used'];
            unset($details['two_factor_used']);
        }

        if (isset($details['two_factor_method'])) {
            $data['two_factor_method'] = $details['two_factor_method'];
            unset($details['two_factor_method']);
        }

        if (isset($details['two_factor_required'])) {
            $data['two_factor_required'] = $details['two_factor_required'];
            unset($details['two_factor_required']);
        }

        if (isset($details['two_factor_verified_at'])) {
            $data['two_factor_verified_at'] = $details['two_factor_verified_at'];
            unset($details['two_factor_verified_at']);
        }

        // two_factor_attempts는 details에 저장 (테이블에 해당 컬럼이 없음)
        // if (isset($details['two_factor_attempts'])) {
        //     $data['two_factor_attempts'] = $details['two_factor_attempts'];
        //     unset($details['two_factor_attempts']);
        // }

        $data['details'] = $details;

        if ($user) {
            $data['user_id'] = $user->id;
            $data['email'] = $user->email;
            $data['name'] = $user->name;
        } elseif ($action === 'failed_login') {
            // 실패한 로그인의 경우 입력된 이메일 저장
            $data['email'] = $request->input('email', 'unknown');
        } elseif ($action === 'password_blocked' || $action === 'password_unblocked') {
            // 비밀번호 차단/해제의 경우 details에서 이메일 가져오기
            $data['email'] = $details['email'] ?? 'unknown';
        } else {
            // 그 외의 경우 기본값 설정
            $data['email'] = $details['email'] ?? 'system';
        }

        return static::create($data);
    }

    /**
     * 액션 레이블 가져오기
     */
    public function getActionLabelAttribute()
    {
        $labels = [
            'login' => '로그인',
            'logout' => '로그아웃',
            'failed_login' => '로그인 실패',
            'password_reset' => '비밀번호 재설정',
            'profile_update' => '프로필 수정',
            'unauthorized_access' => '권한 없는 접근',
            'unauthorized_login' => '권한 없음',
            'password_blocked' => '비밀번호 차단',
            'password_unblocked' => '비밀번호 차단 해제',
            'session_terminated' => '세션 종료',
            'session_regenerated' => '세션 재발급',
        ];

        return $labels[$this->action] ?? $this->action;
    }

    /**
     * 액션 색상 가져오기
     */
    public function getActionColorAttribute()
    {
        $colors = [
            'login' => 'green',
            'logout' => 'blue',
            'failed_login' => 'red',
            'password_reset' => 'yellow',
            'profile_update' => 'indigo',
            'unauthorized_access' => 'red',
        ];

        return $colors[$this->action] ?? 'gray';
    }
}
