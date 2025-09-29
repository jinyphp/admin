<?php

namespace Jiny\Admin\Console\Commands;

use Jiny\Admin\Models\User;
use Illuminate\Console\Command;
use Jiny\Admin\Models\AdminUserLog;
use Jiny\Admin\Models\AdminUsertype;
use Illuminate\Support\Facades\DB;

class AdminUserDelete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:user-delete 
                            {identifier? : 삭제할 관리자 ID 또는 이메일 주소}
                            {--force : 확인 없이 삭제}
                            {--soft : 소프트 삭제 (복구 가능)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '관리자 계정을 삭제합니다';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== 관리자 계정 삭제 ===');
        $this->newLine();
        
        // 식별자 확인 (ID 또는 이메일)
        $identifier = $this->argument('identifier');
        
        if (!$identifier) {
            // 관리자 목록 표시
            $this->displayAdminList();
            
            $identifier = $this->ask('삭제할 관리자의 ID 또는 이메일을 입력하세요');
        }
        
        // 사용자 확인 (ID 또는 이메일로 검색)
        $user = null;
        if (is_numeric($identifier)) {
            $user = User::find($identifier);
        }
        
        if (!$user) {
            $user = User::where('email', $identifier)->first();
        }
        
        if (!$user) {
            $this->error("사용자를 찾을 수 없습니다: {$identifier}");
            return 1;
        }
        
        if (!$user->isAdmin) {
            $this->warn("경고: {$user->email}은(는) 관리자 계정이 아닙니다.");
            if (!$this->confirm('일반 사용자를 삭제하시겠습니까?')) {
                return 0;
            }
        }
        
        // 사용자 정보 표시
        $this->displayUserInfo($user);
        
        // 마지막 관리자 확인
        if ($user->isAdmin && $this->isLastAdmin($user)) {
            $this->error('마지막 관리자는 삭제할 수 없습니다.');
            $this->warn('시스템에 최소 1명의 관리자가 필요합니다.');
            return 1;
        }
        
        // 삭제 확인
        if (!$this->option('force')) {
            $this->warn('⚠️  경고: 이 작업은 되돌릴 수 없습니다!');
            
            if ($this->option('soft')) {
                $this->info('소프트 삭제를 선택하셨습니다. 나중에 복구할 수 있습니다.');
            }
            
            $confirmMessage = sprintf(
                '정말로 %s (%s)를 삭제하시겠습니까?',
                $user->name,
                $user->email
            );
            
            if (!$this->confirm($confirmMessage)) {
                $this->info('삭제가 취소되었습니다.');
                return 0;
            }
            
            // 이중 확인
            $typed = $this->ask('확인을 위해 "DELETE"를 입력하세요');
            if ($typed !== 'DELETE') {
                $this->info('삭제가 취소되었습니다.');
                return 0;
            }
        }
        
        // 삭제 실행
        try {
            DB::beginTransaction();
            
            // 삭제 전 로그 기록
            AdminUserLog::log('admin_deleted_console', $user, [
                'deleted_by' => 'console',
                'command' => 'admin:user-delete',
                'executor' => get_current_user(),
                'user_data' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name,
                    'utype' => $user->utype,
                    'created_at' => $user->created_at->toDateTimeString(),
                ],
                'soft_delete' => $this->option('soft'),
                'timestamp' => now()->toDateTimeString(),
            ]);
            
            // 사용자 타입 카운트 감소
            if ($user->utype) {
                AdminUsertype::where('code', $user->utype)->decrement('cnt');
            }
            
            // 삭제
            if ($this->option('soft')) {
                // 소프트 삭제를 위해 isAdmin을 false로 변경
                $user->isAdmin = false;
                $user->utype = null;
                $user->deleted_at = now();
                $user->save();
                
                $this->info('✓ 관리자가 소프트 삭제되었습니다.');
            } else {
                // 하드 삭제
                $user->delete();
                
                $this->info('✓ 관리자가 완전히 삭제되었습니다.');
            }
            
            DB::commit();
            
            $this->displayDeleteSummary($user->email);
            
            return 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('삭제 중 오류가 발생했습니다: ' . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * 관리자 목록 표시
     */
    private function displayAdminList()
    {
        $admins = User::where('isAdmin', true)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'email', 'name', 'utype', 'last_login_at', 'created_at']);
        
        if ($admins->isEmpty()) {
            $this->warn('등록된 관리자가 없습니다.');
            return;
        }
        
        $this->info('현재 관리자 목록:');
        $this->table(
            ['⭐ ID', '이메일', '이름', '타입', '마지막 로그인', '생성일'],
            $admins->map(function ($admin) {
                return [
                    $admin->id,
                    $admin->email,
                    $admin->name,
                    $admin->utype ?? 'N/A',
                    $admin->last_login_at ? $admin->last_login_at->diffForHumans() : 'Never',
                    $admin->created_at->format('Y-m-d'),
                ];
            })
        );
        
        // 타입별 통계
        $stats = $admins->groupBy('utype')->map->count();
        $this->newLine();
        $this->info('관리자 타입별 통계:');
        foreach ($stats as $type => $count) {
            $typeName = AdminUsertype::where('code', $type)->value('name') ?? $type;
            $this->line("  • {$typeName}: {$count}명");
        }
        
        $this->newLine();
    }
    
    /**
     * 사용자 정보 표시
     */
    private function displayUserInfo($user)
    {
        $this->info('삭제할 사용자 정보:');
        
        $adminType = null;
        if ($user->utype) {
            $adminType = AdminUsertype::where('code', $user->utype)->first();
        }
        
        $this->table(
            ['항목', '값'],
            [
                ['ID', $user->id],
                ['이메일', $user->email],
                ['이름', $user->name],
                ['관리자', $user->isAdmin ? 'Yes' : 'No'],
                ['타입', $adminType ? "{$adminType->name} ({$user->utype})" : ($user->utype ?? 'N/A')],
                ['생성일', $user->created_at->format('Y-m-d H:i:s')],
                ['마지막 로그인', $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : 'Never'],
                ['로그인 횟수', $user->login_count ?? 0],
            ]
        );
        
        // 관련 데이터 확인
        $this->checkRelatedData($user);
        
        $this->newLine();
    }
    
    /**
     * 관련 데이터 확인
     */
    private function checkRelatedData($user)
    {
        $this->newLine();
        $this->info('관련 데이터:');
        
        $relatedData = [];
        
        // 로그인 로그
        $loginLogs = DB::table('admin_user_logs')
            ->where('user_id', $user->id)
            ->count();
        if ($loginLogs > 0) {
            $relatedData[] = ["로그인 로그", "{$loginLogs}개"];
        }
        
        // 세션
        $sessions = DB::table('admin_user_sessions')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->count();
        if ($sessions > 0) {
            $relatedData[] = ["활성 세션", "{$sessions}개"];
        }
        
        // 비밀번호 로그
        $passwordLogs = DB::table('admin_password_logs')
            ->where('user_id', $user->id)
            ->count();
        if ($passwordLogs > 0) {
            $relatedData[] = ["비밀번호 시도 로그", "{$passwordLogs}개"];
        }
        
        if (empty($relatedData)) {
            $this->line('  • 관련 데이터 없음');
        } else {
            $this->table(['데이터 유형', '개수'], $relatedData);
            $this->warn('이 데이터들도 함께 삭제됩니다.');
        }
    }
    
    /**
     * 마지막 관리자인지 확인
     */
    private function isLastAdmin($user)
    {
        if (!$user->isAdmin) {
            return false;
        }
        
        // Super Admin인 경우
        if ($user->utype === 'super') {
            $superAdminCount = User::where('isAdmin', true)
                ->where('utype', 'super')
                ->where('id', '!=', $user->id)
                ->count();
            
            if ($superAdminCount === 0) {
                $this->warn('이 계정은 마지막 Super Admin입니다.');
                
                // 다른 타입의 관리자가 있는지 확인
                $otherAdminCount = User::where('isAdmin', true)
                    ->where('id', '!=', $user->id)
                    ->count();
                
                if ($otherAdminCount > 0) {
                    $this->info('다른 타입의 관리자가 {$otherAdminCount}명 있습니다.');
                    return false; // Super Admin은 아니어도 다른 관리자가 있으면 삭제 가능
                }
                
                return true; // 완전히 마지막 관리자
            }
        }
        
        // 전체 관리자 수 확인
        $totalAdmins = User::where('isAdmin', true)
            ->where('id', '!=', $user->id)
            ->count();
        
        return $totalAdmins === 0;
    }
    
    /**
     * 삭제 완료 요약
     */
    private function displayDeleteSummary($email)
    {
        $this->newLine();
        
        if ($this->option('soft')) {
            $this->info('소프트 삭제 완료:');
            $this->table(
                ['항목', '값'],
                [
                    ['삭제된 계정', $email],
                    ['삭제 방식', '소프트 삭제 (복구 가능)'],
                    ['삭제 시간', now()->format('Y-m-d H:i:s')],
                    ['실행자', get_current_user()],
                ]
            );
            $this->info('필요시 데이터베이스에서 복구할 수 있습니다.');
        } else {
            $this->info('하드 삭제 완료:');
            $this->table(
                ['항목', '값'],
                [
                    ['삭제된 계정', $email],
                    ['삭제 방식', '완전 삭제 (복구 불가)'],
                    ['삭제 시간', now()->format('Y-m-d H:i:s')],
                    ['실행자', get_current_user()],
                ]
            );
            $this->warn('이 작업은 되돌릴 수 없습니다.');
        }
        
        // 남은 관리자 수 표시
        $remainingAdmins = User::where('isAdmin', true)->count();
        $this->newLine();
        $this->info("현재 시스템의 관리자 수: {$remainingAdmins}명");
    }
}