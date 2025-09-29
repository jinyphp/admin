<?php

namespace Jiny\Admin\Console\Commands;

use Jiny\Admin\Models\User;
use Illuminate\Console\Command;
use Jiny\Admin\Models\AdminUsertype;
use Illuminate\Support\Facades\DB;

class AdminUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:users 
                            {--type= : 특정 관리자 타입만 표시}
                            {--active : 활성 세션이 있는 관리자만 표시}
                            {--inactive : 비활성 관리자만 표시}
                            {--sort=created : 정렬 기준 (created, login, name, email)}
                            {--desc : 내림차순 정렬}
                            {--limit= : 표시할 최대 개수}
                            {--export= : 결과를 CSV 파일로 내보내기}
                            {--s|stats : 통계 표시}
                            {--t|types : 관리자 타입별 현황 표시}
                            {--a|activity : 최근 활동 표시}
                            {--all : 모든 정보 표시 (기본 + 통계 + 타입 + 활동)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '관리자 계정 목록을 표시합니다 (기본적으로 사용자 테이블만 표시, 옵션으로 추가 정보 표시 가능)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== 관리자 계정 목록 ===');
        $this->newLine();
        
        // 쿼리 빌드
        $query = User::where('isAdmin', true);
        
        // 타입 필터
        if ($type = $this->option('type')) {
            $query->where('utype', $type);
        }
        
        // 활성/비활성 필터
        if ($this->option('active')) {
            // 활성 사용자: 최근 30일 내에 로그인한 사용자
            $query->where('last_login_at', '>', now()->subDays(30));
        } elseif ($this->option('inactive')) {
            $thirtyDaysAgo = now()->subDays(30);
            $query->where(function($q) use ($thirtyDaysAgo) {
                $q->whereNull('last_login_at')
                  ->orWhere('last_login_at', '<', $thirtyDaysAgo);
            });
        }
        
        // 정렬
        $sortField = $this->getSortField($this->option('sort'));
        $sortDirection = $this->option('desc') ? 'desc' : 'asc';
        $query->orderBy($sortField, $sortDirection);
        
        // 제한
        if ($limit = $this->option('limit')) {
            $query->limit($limit);
        }
        
        // 데이터 가져오기
        $admins = $query->get();
        
        if ($admins->isEmpty()) {
            $this->warn('조건에 맞는 관리자가 없습니다.');
            return 0;
        }
        
        // CSV 내보내기
        if ($exportPath = $this->option('export')) {
            $this->exportToCsv($admins, $exportPath);
            return 0;
        }
        
        // 테이블 표시 (항상 표시)
        $this->displayAdminTable($admins);
        
        // 추가 정보 표시 여부 결정
        $showStats = $this->option('stats') || $this->option('all');
        $showTypes = $this->option('types') || $this->option('all');
        $showActivity = $this->option('activity') || $this->option('all');
        
        // 통계 표시
        if ($showStats) {
            $this->displayStatistics($admins);
        }
        
        // 타입별 요약
        if ($showTypes) {
            $this->displayTypeSummary();
        }
        
        // 최근 활동 정보
        if ($showActivity) {
            $this->displayRecentActivity();
        }
        
        return 0;
    }
    
    /**
     * 관리자 테이블 표시
     */
    private function displayAdminTable($admins)
    {
        $headers = ['ID', '이메일', '이름', '타입', '상태', '마지막 로그인', '로그인 횟수', '생성일'];
        
        $rows = $admins->map(function ($admin) {
            return [
                $admin->id,
                $admin->email,
                $admin->name,
                $this->getAdminTypeDisplay($admin->utype),
                $this->getAdminStatus($admin),
                $this->formatLastLogin($admin->last_login_at),
                $admin->login_count ?? 0,
                $admin->created_at->format('Y-m-d'),
            ];
        });
        
        $this->table($headers, $rows);
    }
    
    /**
     * 통계 표시
     */
    private function displayStatistics($admins)
    {
        $this->newLine();
        $this->info('📊 관리자 통계');
        
        $stats = [
            ['총 관리자 수', $admins->count() . '명'],
            ['Super Admin', $admins->where('utype', 'super')->count() . '명'],
            ['일반 Admin', $admins->where('utype', 'admin')->count() . '명'],
            ['Manager', $admins->where('utype', 'manager')->count() . '명'],
            ['기타', $admins->whereNotIn('utype', ['super', 'admin', 'manager'])->count() . '명'],
        ];
        
        // 활성 사용자
        $activeCount = $admins->filter(function ($admin) {
            if (!$admin->last_login_at) {
                return false;
            }
            $lastLogin = is_string($admin->last_login_at) 
                ? \Carbon\Carbon::parse($admin->last_login_at) 
                : $admin->last_login_at;
            return $lastLogin->gt(now()->subDays(30));
        })->count();
        $stats[] = ['최근 30일 활성', $activeCount . '명'];
        
        // 비활성 사용자
        $inactiveCount = $admins->filter(function ($admin) {
            if (!$admin->last_login_at) {
                return true;
            }
            $lastLogin = is_string($admin->last_login_at) 
                ? \Carbon\Carbon::parse($admin->last_login_at) 
                : $admin->last_login_at;
            return $lastLogin->lt(now()->subDays(30));
        })->count();
        $stats[] = ['30일 이상 비활성', $inactiveCount . '명'];
        
        // 2FA 사용자
        $twoFaCount = $admins->where('two_factor_enabled', true)->count();
        if ($twoFaCount > 0) {
            $stats[] = ['2FA 활성화', $twoFaCount . '명'];
        }
        
        // 비밀번호 만료 예정
        $expiringCount = $admins->filter(function ($admin) {
            if (!$admin->password_expires_at) {
                return false;
            }
            $expiresAt = is_string($admin->password_expires_at) 
                ? \Carbon\Carbon::parse($admin->password_expires_at) 
                : $admin->password_expires_at;
            return $expiresAt->between(now(), now()->addDays(7));
        })->count();
        if ($expiringCount > 0) {
            $stats[] = ['7일 내 비밀번호 만료', $expiringCount . '명'];
        }
        
        $this->table(['항목', '값'], $stats);
    }
    
    /**
     * 타입별 요약 표시
     */
    private function displayTypeSummary()
    {
        $types = AdminUsertype::where('enable', true)
            ->orderBy('level', 'desc')
            ->get();
        
        if ($types->isEmpty()) {
            return;
        }
        
        $this->newLine();
        $this->info('🏷️  관리자 타입별 현황');
        
        $typeData = $types->map(function ($type) {
            $count = User::where('isAdmin', true)
                ->where('utype', $type->code)
                ->count();
            
            $activeCount = User::where('isAdmin', true)
                ->where('utype', $type->code)
                ->where('last_login_at', '>', now()->subDays(30))
                ->count();
            
            return [
                $type->code,
                $type->name,
                $type->level,
                $count,
                $activeCount,
                $type->description ?? '-',
            ];
        })->filter(function ($item) {
            return $item[3] > 0; // 사용자가 있는 타입만 표시
        });
        
        if ($typeData->isNotEmpty()) {
            $this->table(
                ['코드', '이름', '레벨', '전체', '활성', '설명'],
                $typeData
            );
        }
    }
    
    /**
     * 최근 활동 표시
     */
    private function displayRecentActivity()
    {
        $this->newLine();
        $this->info('🕐 최근 관리자 활동');
        
        // 최근 로그인
        $recentLogins = User::where('isAdmin', true)
            ->whereNotNull('last_login_at')
            ->orderBy('last_login_at', 'desc')
            ->limit(5)
            ->get(['name', 'email', 'last_login_at']);
        
        if ($recentLogins->isNotEmpty()) {
            $this->line('최근 로그인:');
            foreach ($recentLogins as $admin) {
                $this->line(sprintf(
                    "  • %s (%s) - %s",
                    $admin->name,
                    $admin->email,
                    $admin->last_login_at ? $admin->last_login_at->diffForHumans() : 'Never'
                ));
            }
        }
        
        // 최근 생성된 관리자
        $recentCreated = User::where('isAdmin', true)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get(['name', 'email', 'created_at']);
        
        if ($recentCreated->isNotEmpty()) {
            $this->newLine();
            $this->line('최근 생성된 관리자:');
            foreach ($recentCreated as $admin) {
                $this->line(sprintf(
                    "  • %s (%s) - %s",
                    $admin->name,
                    $admin->email,
                    $admin->created_at->diffForHumans()
                ));
            }
        }
        
        // 비밀번호 만료 예정
        $expiringPasswords = User::where('isAdmin', true)
            ->whereNotNull('password_expires_at')
            ->where('password_expires_at', '>', now())
            ->where('password_expires_at', '<', now()->addDays(7))
            ->orderBy('password_expires_at', 'asc')
            ->get(['name', 'email', 'password_expires_at']);
        
        if ($expiringPasswords->isNotEmpty()) {
            $this->newLine();
            $this->warn('⚠️  7일 내 비밀번호 만료 예정:');
            foreach ($expiringPasswords as $admin) {
                $this->line(sprintf(
                    "  • %s (%s) - %s 만료",
                    $admin->name,
                    $admin->email,
                    $admin->password_expires_at->diffForHumans()
                ));
            }
        }
        
        // 장기 미접속자
        $inactiveAdmins = User::where('isAdmin', true)
            ->where(function($q) {
                $q->whereNull('last_login_at')
                  ->orWhere('last_login_at', '<', now()->subDays(90));
            })
            ->limit(5)
            ->get(['name', 'email', 'last_login_at']);
        
        if ($inactiveAdmins->isNotEmpty()) {
            $this->newLine();
            $this->warn('⚠️  90일 이상 미접속 관리자:');
            foreach ($inactiveAdmins as $admin) {
                $lastLogin = $admin->last_login_at 
                    ? $admin->last_login_at->diffForHumans() 
                    : '한번도 로그인하지 않음';
                $this->line(sprintf(
                    "  • %s (%s) - %s",
                    $admin->name,
                    $admin->email,
                    $lastLogin
                ));
            }
        }
    }
    
    /**
     * CSV로 내보내기
     */
    private function exportToCsv($admins, $path)
    {
        $this->info("CSV 파일로 내보내는 중: {$path}");
        
        $handle = fopen($path, 'w');
        
        // BOM 추가 (Excel에서 한글 깨짐 방지)
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // 헤더
        fputcsv($handle, [
            'ID',
            '이메일',
            '이름',
            '관리자 타입',
            '상태',
            '마지막 로그인',
            '로그인 횟수',
            '2FA 활성화',
            '생성일',
            '비밀번호 변경일',
            '비밀번호 만료일',
        ]);
        
        // 데이터
        foreach ($admins as $admin) {
            fputcsv($handle, [
                $admin->id,
                $admin->email,
                $admin->name,
                $admin->utype ?? 'N/A',
                $this->getAdminStatus($admin),
                $admin->last_login_at ? $admin->last_login_at->format('Y-m-d H:i:s') : '',
                $admin->login_count ?? 0,
                $admin->two_factor_enabled ? 'Yes' : 'No',
                $admin->created_at->format('Y-m-d H:i:s'),
                $admin->password_changed_at ? 
                    (is_string($admin->password_changed_at) ? 
                        $admin->password_changed_at : 
                        $admin->password_changed_at->format('Y-m-d H:i:s')
                    ) : '',
                $admin->password_expires_at ? $admin->password_expires_at->format('Y-m-d') : '',
            ]);
        }
        
        fclose($handle);
        
        $this->info("✓ CSV 파일이 생성되었습니다: {$path}");
        $this->info("  총 {$admins->count()}개의 관리자 정보가 내보내졌습니다.");
    }
    
    /**
     * 정렬 필드 가져오기
     */
    private function getSortField($sort)
    {
        return match($sort) {
            'login' => 'last_login_at',
            'name' => 'name',
            'email' => 'email',
            default => 'created_at',
        };
    }
    
    /**
     * 관리자 타입 표시
     */
    private function getAdminTypeDisplay($utype)
    {
        if (!$utype) {
            return 'N/A';
        }
        
        $adminType = AdminUsertype::where('code', $utype)->first();
        return $adminType ? "{$adminType->name}" : $utype;
    }
    
    /**
     * 관리자 상태 가져오기
     */
    private function getAdminStatus($admin)
    {
        // 차단 상태 확인
        $blocked = DB::table('admin_password_logs')
            ->where('user_id', $admin->id)
            ->where('is_blocked', true)
            ->whereNull('unblocked_at')
            ->exists();
        
        if ($blocked) {
            return '🔒 차단됨';
        }
        
        // 비밀번호 만료 확인
        if ($admin->password_expires_at) {
            $expiresAt = is_string($admin->password_expires_at) 
                ? \Carbon\Carbon::parse($admin->password_expires_at) 
                : $admin->password_expires_at;
            if ($expiresAt->isPast()) {
                return '⚠️ 비밀번호 만료';
            }
        }
        
        // 강제 비밀번호 변경 필요
        if ($admin->force_password_change || $admin->password_must_change) {
            return '🔑 비밀번호 변경 필요';
        }
        
        // 활성 상태 확인
        if ($admin->last_login_at) {
            $lastLogin = is_string($admin->last_login_at) 
                ? \Carbon\Carbon::parse($admin->last_login_at) 
                : $admin->last_login_at;
            
            if ($lastLogin->gt(now()->subDays(7))) {
                return '✅ 활성';
            } elseif ($lastLogin->gt(now()->subDays(30))) {
                return '😴 휴면';
            }
        }
        
        return '💤 비활성';
    }
    
    /**
     * 마지막 로그인 포맷
     */
    private function formatLastLogin($lastLogin)
    {
        if (!$lastLogin) {
            return 'Never';
        }
        
        // Carbon 인스턴스로 변환
        $lastLoginDate = is_string($lastLogin) 
            ? \Carbon\Carbon::parse($lastLogin) 
            : $lastLogin;
        
        // 미래 시간인 경우 현재 시간으로 처리
        if ($lastLoginDate->isFuture()) {
            return '방금 전';
        }
        
        return $lastLoginDate->diffForHumans();
    }
}