<?php

namespace Jiny\Admin\Console\Commands;

use Illuminate\Console\Command;
use Jiny\Admin\Models\AdminPasswordLog;
use Jiny\Admin\Models\AdminUserLog;

/**
 * 비밀번호 시도 차단 해제 명령어
 * 
 * 비밀번호 실패로 차단된 사용자나 IP의 차단을 해제합니다.
 * ResetPasswordAttempts와 달리 시도 횟수는 초기화하지 않고
 * 차단 상태만 해제합니다.
 * 
 * 주요 기능:
 * - 차단된 시도 목록 표시
 * - 특정 이메일/IP 차단 해제
 * - 인터랙티브 모드 지원
 * - 만료된 차단 자동 해제
 * 
 * @package Jiny\Admin
 * @author JinyPHP
 * @since 1.0.0
 */
class UnblockPasswordAttempts extends Command
{
    /**
     * 콘솔 명령어 시그니처
     * 
     * 사용법: php artisan admin:unblock-password-attempts [email] [options]
     * 
     * Arguments:
     *   email : 차단 해제할 이메일 주소 (선택적)
     * 
     * Options:
     *   --ip : 차단 해제할 IP 주소
     *   --all : 모든 차단 해제
     *   --show : 차단된 시도 목록 표시
     *
     * @var string
     */
    protected $signature = 'admin:unblock-password-attempts 
                            {email? : 차단 해제할 이메일 주소}
                            {--ip= : 차단 해제할 IP 주소}
                            {--all : 모든 차단 해제}
                            {--show : 차단된 목록 표시}';

    /**
     * 콘솔 명령어 설명
     * 
     * 비밀번호 실패로 차단된 사용자의 차단 상태만 해제합니다.
     * 시도 횟수는 그대로 유지되며, 필요시 ResetPasswordAttempts
     * 명령을 사용하여 횟수를 초기화할 수 있습니다.
     *
     * @var string
     */
    protected $description = '로그인 실패로 차단된 계정의 차단을 해제합니다';

    /**
     * 명령어 실행 메인 메서드
     * 
     * 전달된 옵션에 따라 적절한 작업을 수행합니다.
     * 우선순위:
     * 1. --show : 차단된 목록 표시
     * 2. --all : 모든 차단 해제
     * 3. email : 특정 이메일 차단 해제
     * 4. --ip : 특정 IP 차단 해제
     * 5. 옵션 없음 : 인터랙티브 모드
     * 
     * @return int 명령어 실행 결과 (0: 성공, 1: 실패)
     */
    public function handle()
    {
        // 차단된 목록 보기
        if ($this->option('show')) {
            return $this->showBlockedAttempts();
        }

        // 모든 차단 해제
        if ($this->option('all')) {
            return $this->unblockAll();
        }

        // 특정 이메일 차단 해제
        if ($email = $this->argument('email')) {
            return $this->unblockByEmail($email);
        }

        // 특정 IP 차단 해제
        if ($ip = $this->option('ip')) {
            return $this->unblockByIp($ip);
        }

        // 인터랙티브 모드
        return $this->interactiveUnblock();
    }

    /**
     * 차단된 시도 목록 표시
     * 
     * 현재 차단된 모든 로그인 시도를 테이블 형태로 표시합니다.
     * 차단 시간 순으로 정렬하여 최근 차단부터 표시합니다.
     * 
     * 표시 항목:
     * - ID, 이메일, IP 주소, 시도 횟수
     * - 차단 시간, 마지막 시도 시간
     * 
     * @return int 명령어 실행 결과
     */
    protected function showBlockedAttempts()
    {
        $blocked = AdminPasswordLog::where('is_blocked', true)
            ->where('status', 'blocked')
            ->orderBy('blocked_at', 'desc')
            ->get();

        if ($blocked->isEmpty()) {
            $this->info('차단된 로그인 시도가 없습니다.');

            return 0;
        }

        $this->table(
            ['ID', '이메일', 'IP 주소', '시도 횟수', '차단 시간', '마지막 시도'],
            $blocked->map(function ($log) {
                return [
                    $log->id,
                    $log->email,
                    $log->ip_address,
                    $log->attempt_count,
                    $log->blocked_at ? $log->blocked_at->format('Y-m-d H:i:s') : '-',
                    $log->last_attempt_at ? $log->last_attempt_at->format('Y-m-d H:i:s') : '-',
                ];
            })
        );

        $this->info("총 {$blocked->count()}개의 차단된 시도가 있습니다.");

        return 0;
    }

    /**
     * 모든 차단 해제
     * 
     * 현재 차단된 모든 사용자와 IP의 차단을 해제합니다.
     * AdminPasswordLog 모델의 unblock() 메서드를 호출하여
     * 각 레코드를 개별적으로 처리합니다.
     * 
     * @return int 명령어 실행 결과
     */
    protected function unblockAll()
    {
        if (! $this->confirm('모든 차단을 해제하시겠습니까?')) {
            $this->info('취소되었습니다.');

            return 0;
        }

        $blocked = AdminPasswordLog::where('is_blocked', true)
            ->where('status', 'blocked')
            ->get();

        $count = 0;
        foreach ($blocked as $log) {
            $log->unblock();
            $count++;
            $this->line("✓ {$log->email} ({$log->ip_address}) 차단 해제됨");
        }

        if ($count > 0) {
            $this->info("총 {$count}개의 차단이 해제되었습니다.");

            // 시스템 로그 기록
            AdminUserLog::log('password_unblocked', null, [
                'email' => 'ALL',
                'unblocked_by' => 'console',
                'count' => $count,
                'command' => 'admin:password-unblock --all',
            ]);
        } else {
            $this->info('차단 해제할 항목이 없습니다.');
        }

        return 0;
    }

    /**
     * 특정 이메일의 차단 해제
     * 
     * 지정된 이메일에 대한 모든 차단된 레코드를 찾아
     * 차단을 해제합니다. 여러 IP에서 차단된 경우
     * 모든 IP의 차단이 해제됩니다.
     * 
     * @param string $email 차단 해제할 이메일 주소
     * @return int 명령어 실행 결과
     */
    protected function unblockByEmail($email)
    {
        $logs = AdminPasswordLog::where('email', $email)
            ->where('is_blocked', true)
            ->where('status', 'blocked')
            ->get();

        if ($logs->isEmpty()) {
            $this->error("이메일 '{$email}'에 대한 차단된 시도가 없습니다.");

            return 1;
        }

        foreach ($logs as $log) {
            $log->unblock();
            $this->info("✓ {$log->email} (IP: {$log->ip_address}) 차단이 해제되었습니다.");
        }

        $this->info("총 {$logs->count()}개의 차단이 해제되었습니다.");

        return 0;
    }

    /**
     * 특정 IP의 차단 해제
     * 
     * 지정된 IP 주소에서 발생한 모든 차단된 레코드를 찾아
     * 차단을 해제합니다. 하나의 IP에서 여러 이메일이
     * 차단된 경우 모두 해제됩니다.
     * 
     * @param string $ip 차단 해제할 IP 주소
     * @return int 명령어 실행 결과
     */
    protected function unblockByIp($ip)
    {
        $logs = AdminPasswordLog::where('ip_address', $ip)
            ->where('is_blocked', true)
            ->where('status', 'blocked')
            ->get();

        if ($logs->isEmpty()) {
            $this->error("IP '{$ip}'에 대한 차단된 시도가 없습니다.");

            return 1;
        }

        foreach ($logs as $log) {
            $log->unblock();
            $this->info("✓ {$log->email} (IP: {$log->ip_address}) 차단이 해제되었습니다.");
        }

        $this->info("총 {$logs->count()}개의 차단이 해제되었습니다.");

        return 0;
    }

    /**
     * 인터랙티브 차단 해제 모드
     * 
     * 옵션이 전달되지 않은 경우 실행되는 대화형 모드입니다.
     * 차단된 목록을 표시하고 사용자가 해제할 항목을
     * 선택할 수 있도록 합니다.
     * 
     * 선택 옵션:
     * - 개별 이메일/IP 선택
     * - 모두 해제
     * - 취소
     * 
     * @return int 명령어 실행 결과
     */
    protected function interactiveUnblock()
    {
        // 차단된 목록 표시
        $this->showBlockedAttempts();

        $blocked = AdminPasswordLog::where('is_blocked', true)
            ->where('status', 'blocked')
            ->get();

        if ($blocked->isEmpty()) {
            return 0;
        }

        // 선택 옵션 생성
        $choices = [];
        foreach ($blocked as $log) {
            $choices[] = "{$log->email} - {$log->ip_address} (시도: {$log->attempt_count}회)";
        }
        $choices[] = '모두 해제';
        $choices[] = '취소';

        $choice = $this->choice(
            '차단을 해제할 항목을 선택하세요',
            $choices,
            count($choices) - 1
        );

        if ($choice === '취소') {
            $this->info('취소되었습니다.');

            return 0;
        }

        if ($choice === '모두 해제') {
            return $this->unblockAll();
        }

        // 선택된 항목 찾기
        $index = array_search($choice, $choices);
        if ($index !== false && isset($blocked[$index])) {
            $log = $blocked[$index];
            $log->unblock();
            $this->info("✓ {$log->email} ({$log->ip_address}) 차단이 해제되었습니다.");
        }

        return 0;
    }

    /**
     * 만료된 차단 자동 해제
     * 
     * 지정된 시간이 경과한 차단을 자동으로 해제합니다.
     * 주로 크론 작업(cron job)에서 사용하기 위한 메서드입니다.
     * 
     * 예시 크론 설정:
     * 0 * * * * php artisan admin:password-unblock --auto=24
     * 
     * @param int $hours 차단 해제 기준 시간 (기본값: 24시간)
     * @return int 해제된 차단 건수
     */
    public function autoUnblockExpired($hours = 24)
    {
        $expired = AdminPasswordLog::where('is_blocked', true)
            ->where('status', 'blocked')
            ->where('blocked_at', '<=', now()->subHours($hours))
            ->get();

        $count = 0;
        foreach ($expired as $log) {
            $log->unblock();
            $count++;
        }

        if ($count > 0) {
            $this->info("{$hours}시간이 지난 {$count}개의 차단이 자동 해제되었습니다.");
        }

        return $count;
    }
}
