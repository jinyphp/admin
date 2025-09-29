<?php

namespace Jiny\Admin\Tests\Feature\Commands;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Jiny\Admin\Models\User;
use Jiny\Admin\Models\AdminUsertype;
use Jiny\Admin\Models\AdminUserLog;

class AdminUserDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // 기본 관리자 타입 생성
        AdminUsertype::create([
            'code' => 'super',
            'name' => 'Super Admin',
            'level' => 100,
            'enable' => true,
        ]);
        
        AdminUsertype::create([
            'code' => 'admin',
            'name' => 'Administrator',
            'level' => 50,
            'enable' => true,
        ]);
    }

    protected function createAdmin($email = 'admin@example.com', $type = 'admin')
    {
        return User::create([
            'email' => $email,
            'name' => '테스트 관리자',
            'password' => bcrypt('password'),
            'isAdmin' => true,
            'utype' => $type,
        ]);
    }

    public function test_delete_admin_by_id()
    {
        $admin = $this->createAdmin();
        
        $this->artisan('admin:user-delete', [
            'identifier' => $admin->id,
            '--force' => true,
        ])
        ->expectsOutput('✓ 관리자가 완전히 삭제되었습니다.')
        ->assertExitCode(0);

        $this->assertNull(User::find($admin->id));
    }

    public function test_delete_admin_by_email()
    {
        $admin = $this->createAdmin('delete@example.com');
        
        $this->artisan('admin:user-delete', [
            'identifier' => 'delete@example.com',
            '--force' => true,
        ])
        ->expectsOutput('✓ 관리자가 완전히 삭제되었습니다.')
        ->assertExitCode(0);

        $this->assertNull(User::where('email', 'delete@example.com')->first());
    }

    public function test_soft_delete_admin()
    {
        $admin = $this->createAdmin('soft@example.com');
        
        $this->artisan('admin:user-delete', [
            'identifier' => $admin->id,
            '--soft' => true,
            '--force' => true,
        ])
        ->expectsOutput('✓ 관리자가 소프트 삭제되었습니다.')
        ->assertExitCode(0);

        $user = User::find($admin->id);
        $this->assertNotNull($user);
        $this->assertFalse($user->isAdmin);
        $this->assertNull($user->utype);
        $this->assertNotNull($user->deleted_at);
    }

    public function test_prevent_deleting_last_admin()
    {
        // 모든 관리자 삭제
        User::where('isAdmin', true)->delete();
        
        // 마지막 관리자 생성
        $lastAdmin = $this->createAdmin('last@example.com', 'super');
        
        $this->artisan('admin:user-delete', [
            'identifier' => $lastAdmin->id,
            '--force' => true,
        ])
        ->expectsOutput('마지막 관리자는 삭제할 수 없습니다.')
        ->assertExitCode(1);

        $this->assertNotNull(User::find($lastAdmin->id));
    }

    public function test_prevent_deleting_last_super_admin()
    {
        // 일반 관리자 생성
        $this->createAdmin('regular@example.com', 'admin');
        
        // Super Admin 생성
        $superAdmin = $this->createAdmin('super@example.com', 'super');
        
        $this->artisan('admin:user-delete', [
            'identifier' => $superAdmin->id,
            '--force' => true,
        ])
        ->expectsOutputToContain('이 계정은 마지막 Super Admin입니다')
        ->assertExitCode(0); // 다른 관리자가 있으므로 삭제 가능

        $this->assertNull(User::find($superAdmin->id));
    }

    public function test_delete_nonexistent_user()
    {
        $this->artisan('admin:user-delete', [
            'identifier' => 'nonexistent@example.com',
            '--force' => true,
        ])
        ->expectsOutput('사용자를 찾을 수 없습니다: nonexistent@example.com')
        ->assertExitCode(1);
    }

    public function test_delete_logs_activity()
    {
        $admin = $this->createAdmin();
        
        $this->artisan('admin:user-delete', [
            'identifier' => $admin->id,
            '--force' => true,
        ])->assertExitCode(0);

        $log = AdminUserLog::where('action', 'admin_deleted_console')
            ->where('target_user_id', $admin->id)
            ->first();
        
        $this->assertNotNull($log);
        $details = json_decode($log->details, true);
        $this->assertEquals('admin:user-delete', $details['command']);
    }

    public function test_delete_decrements_type_count()
    {
        // 초기 카운트 설정
        AdminUsertype::where('code', 'admin')->update(['cnt' => 5]);
        
        $admin = $this->createAdmin();
        
        $this->artisan('admin:user-delete', [
            'identifier' => $admin->id,
            '--force' => true,
        ])->assertExitCode(0);

        $count = AdminUsertype::where('code', 'admin')->value('cnt');
        $this->assertEquals(4, $count);
    }

    public function test_warn_when_deleting_non_admin()
    {
        // 일반 사용자 생성
        $user = User::create([
            'email' => 'regular@example.com',
            'name' => '일반 사용자',
            'password' => bcrypt('password'),
            'isAdmin' => false,
        ]);
        
        $this->artisan('admin:user-delete', [
            'identifier' => $user->id,
        ])
        ->expectsOutput('경고: regular@example.com은(는) 관리자 계정이 아닙니다.')
        ->expectsQuestion('일반 사용자를 삭제하시겠습니까?', 'no')
        ->assertExitCode(0);

        $this->assertNotNull(User::find($user->id));
    }
}