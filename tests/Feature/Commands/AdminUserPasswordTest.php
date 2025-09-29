<?php

namespace Jiny\Admin\Tests\Feature\Commands;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Jiny\Admin\Models\User;
use Jiny\Admin\Models\AdminPasswordLog;
use Jiny\Admin\Models\AdminUserLog;
use Illuminate\Support\Facades\Hash;

class AdminUserPasswordTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function createAdmin($email = 'admin@example.com')
    {
        return User::create([
            'email' => $email,
            'name' => '테스트 관리자',
            'password' => bcrypt('OldPassword123!'),
            'isAdmin' => true,
            'utype' => 'admin',
        ]);
    }

    protected function createBlockedLog($email, $userId = null)
    {
        return AdminPasswordLog::create([
            'email' => $email,
            'user_id' => $userId,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Test Browser',
            'browser' => 'Chrome',
            'platform' => 'Windows',
            'device' => 'Desktop',
            'attempt_count' => 5,
            'first_attempt_at' => now()->subMinutes(10),
            'last_attempt_at' => now()->subMinutes(1),
            'is_blocked' => true,
            'blocked_at' => now()->subMinutes(1),
            'status' => 'blocked',
        ]);
    }

    // 비밀번호 재설정 테스트
    public function test_reset_password_with_specific_password()
    {
        $admin = $this->createAdmin();
        
        $this->artisan('admin:user-password', [
            'action' => 'reset',
            'identifier' => $admin->email,
            '--password' => 'NewPassword123!',
        ])
        ->expectsOutput('✓ 비밀번호가 성공적으로 재설정되었습니다.')
        ->assertExitCode(0);

        $admin->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $admin->password));
        $this->assertNotNull($admin->password_changed_at);
        $this->assertFalse($admin->password_must_change);
        $this->assertFalse($admin->force_password_change);
    }

    public function test_reset_password_with_random()
    {
        $admin = $this->createAdmin();
        
        $this->artisan('admin:user-password', [
            'action' => 'reset',
            'identifier' => $admin->id,
            '--random' => true,
        ])
        ->expectsOutput('랜덤 비밀번호가 생성되었습니다.')
        ->expectsOutput('✓ 비밀번호가 성공적으로 재설정되었습니다.')
        ->assertExitCode(0);

        $admin->refresh();
        // 비밀번호가 변경되었는지 확인 (이전 비밀번호와 다름)
        $this->assertFalse(Hash::check('OldPassword123!', $admin->password));
    }

    public function test_reset_password_unblocks_account()
    {
        $admin = $this->createAdmin('blocked@example.com');
        $this->createBlockedLog('blocked@example.com', $admin->id);
        
        $this->artisan('admin:user-password', [
            'action' => 'reset',
            'identifier' => 'blocked@example.com',
            '--password' => 'NewPassword123!',
        ])
        ->expectsOutputToContain('차단 해제됨')
        ->assertExitCode(0);

        $blockedLog = AdminPasswordLog::where('email', 'blocked@example.com')
            ->where('is_blocked', true)
            ->where('status', 'blocked')
            ->first();
        
        $this->assertNull($blockedLog);
    }

    public function test_reset_password_for_nonexistent_user()
    {
        $this->artisan('admin:user-password', [
            'action' => 'reset',
            'identifier' => 'nonexistent@example.com',
        ])
        ->expectsOutput('사용자를 찾을 수 없습니다: nonexistent@example.com')
        ->assertExitCode(1);
    }

    public function test_reset_password_validates_weak_password()
    {
        $admin = $this->createAdmin();
        
        $this->artisan('admin:user-password', [
            'action' => 'reset',
            'identifier' => $admin->email,
            '--password' => 'weak',
        ])
        ->expectsOutputToContain('비밀번호가 보안 요구사항을 충족하지 않습니다')
        ->assertExitCode(1);
    }

    // 차단 해제 테스트
    public function test_unblock_specific_account()
    {
        $admin = $this->createAdmin('blocked@example.com');
        $this->createBlockedLog('blocked@example.com', $admin->id);
        
        $this->artisan('admin:user-password', [
            'action' => 'unblock',
            'identifier' => 'blocked@example.com',
        ])
        ->expectsOutput("✓ blocked@example.com의 1개 차단이 해제되었습니다.")
        ->assertExitCode(0);

        $blockedLog = AdminPasswordLog::where('email', 'blocked@example.com')
            ->where('is_blocked', true)
            ->first();
        
        $this->assertNull($blockedLog);
    }

    public function test_unblock_by_user_id()
    {
        $admin = $this->createAdmin('blocked@example.com');
        $this->createBlockedLog('blocked@example.com', $admin->id);
        
        $this->artisan('admin:user-password', [
            'action' => 'unblock',
            'identifier' => $admin->id,
        ])
        ->expectsOutput("✓ blocked@example.com의 1개 차단이 해제되었습니다.")
        ->assertExitCode(0);
    }

    public function test_unblock_all_accounts()
    {
        $admin1 = $this->createAdmin('blocked1@example.com');
        $admin2 = $this->createAdmin('blocked2@example.com');
        $this->createBlockedLog('blocked1@example.com', $admin1->id);
        $this->createBlockedLog('blocked2@example.com', $admin2->id);
        
        $this->artisan('admin:user-password', [
            'action' => 'unblock',
            '--all' => true,
        ])
        ->expectsQuestion('모든 차단을 해제하시겠습니까?', 'yes')
        ->expectsOutput('✓ 총 2개의 차단이 해제되었습니다.')
        ->assertExitCode(0);

        $blockedCount = AdminPasswordLog::where('is_blocked', true)
            ->where('status', 'blocked')
            ->count();
        
        $this->assertEquals(0, $blockedCount);
    }

    public function test_unblock_by_ip()
    {
        $admin = $this->createAdmin('blocked@example.com');
        $this->createBlockedLog('blocked@example.com', $admin->id);
        
        $this->artisan('admin:user-password', [
            'action' => 'unblock',
            '--ip' => '192.168.1.1',
        ])
        ->expectsOutput('✓ IP 192.168.1.1의 1개 차단이 해제되었습니다.')
        ->assertExitCode(0);
    }

    public function test_unblock_old_attempts()
    {
        $admin = $this->createAdmin('old@example.com');
        
        // 오래된 차단 로그 생성
        AdminPasswordLog::create([
            'email' => 'old@example.com',
            'user_id' => $admin->id,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Test',
            'browser' => 'Chrome',
            'platform' => 'Windows',
            'device' => 'Desktop',
            'attempt_count' => 5,
            'first_attempt_at' => now()->subDays(10),
            'last_attempt_at' => now()->subDays(8),
            'is_blocked' => true,
            'blocked_at' => now()->subDays(8),
            'status' => 'blocked',
        ]);
        
        $this->artisan('admin:user-password', [
            'action' => 'unblock',
            '--days' => 7,
        ])
        ->expectsQuestion('7일 이전의 1개 차단을 해제하시겠습니까?', 'yes')
        ->expectsOutput('✓ 7일 이전의 1개 차단이 해제되었습니다.')
        ->assertExitCode(0);
    }

    public function test_unblock_nonexistent_account()
    {
        $this->artisan('admin:user-password', [
            'action' => 'unblock',
            'identifier' => 'nonexistent@example.com',
        ])
        ->expectsOutput("'nonexistent@example.com'에 대한 차단된 기록이 없습니다.")
        ->assertExitCode(1);
    }

    public function test_unblock_with_no_blocked_accounts()
    {
        $this->artisan('admin:user-password', [
            'action' => 'unblock',
            '--all' => true,
        ])
        ->expectsQuestion('모든 차단을 해제하시겠습니까?', 'yes')
        ->expectsOutput('차단된 기록이 없습니다.')
        ->assertExitCode(0);
    }

    // 로그 기록 테스트
    public function test_password_reset_creates_log()
    {
        $admin = $this->createAdmin();
        
        $this->artisan('admin:user-password', [
            'action' => 'reset',
            'identifier' => $admin->email,
            '--password' => 'NewPassword123!',
        ])->assertExitCode(0);

        $log = AdminUserLog::where('user_id', $admin->id)
            ->where('action', 'password_reset_console')
            ->first();
        
        $this->assertNotNull($log);
        $details = json_decode($log->details, true);
        $this->assertEquals('admin:user-password reset', $details['command']);
    }

    public function test_unblock_creates_log()
    {
        $admin = $this->createAdmin('blocked@example.com');
        $this->createBlockedLog('blocked@example.com', $admin->id);
        
        $this->artisan('admin:user-password', [
            'action' => 'unblock',
            'identifier' => 'blocked@example.com',
        ])->assertExitCode(0);

        $log = AdminUserLog::where('action', 'password_attempts_reset')
            ->whereJsonContains('details->email', 'blocked@example.com')
            ->first();
        
        $this->assertNotNull($log);
    }

    // 기본 액션 테스트
    public function test_default_action_is_reset()
    {
        $admin = $this->createAdmin();
        
        $this->artisan('admin:user-password', [
            'identifier' => $admin->email,
            '--password' => 'NewPassword123!',
        ])
        ->expectsOutput('✓ 비밀번호가 성공적으로 재설정되었습니다.')
        ->assertExitCode(0);
    }

    public function test_invalid_action()
    {
        $this->artisan('admin:user-password', [
            'action' => 'invalid',
        ])
        ->expectsOutput("잘못된 액션입니다. 'reset' 또는 'unblock'을 사용하세요.")
        ->assertExitCode(1);
    }
}