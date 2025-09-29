<?php

namespace Jiny\Admin\Tests\Feature\Commands;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Jiny\Admin\Models\User;
use Jiny\Admin\Models\AdminUsertype;
use Jiny\Admin\Models\AdminUserLog;

class AdminUserCreateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // 기본 관리자 타입 생성
        AdminUsertype::create([
            'code' => 'super',
            'name' => 'Super Admin',
            'description' => '최고 관리자',
            'level' => 100,
            'enable' => true,
        ]);
        
        AdminUsertype::create([
            'code' => 'admin',
            'name' => 'Administrator',
            'description' => '일반 관리자',
            'level' => 50,
            'enable' => true,
        ]);
    }

    public function test_create_admin_with_all_options()
    {
        $this->artisan('admin:user-create', [
            '--email' => 'test@example.com',
            '--name' => '테스트 관리자',
            '--password' => 'TestPass123!',
            '--type' => 'super',
            '--force' => true,
        ])
        ->expectsOutput('✓ 관리자 계정이 성공적으로 생성되었습니다!')
        ->assertExitCode(0);

        // 사용자가 생성되었는지 확인
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('테스트 관리자', $user->name);
        $this->assertEquals('super', $user->utype);
        $this->assertTrue($user->isAdmin);

        // 로그가 생성되었는지 확인
        $log = AdminUserLog::where('user_id', $user->id)
            ->where('action', 'admin_created_console')
            ->first();
        $this->assertNotNull($log);
    }

    public function test_create_admin_with_random_password()
    {
        $this->artisan('admin:user-create', [
            '--email' => 'random@example.com',
            '--name' => '랜덤 비밀번호 관리자',
            '--type' => 'admin',
            '--random-password' => true,
            '--force' => true,
        ])
        ->expectsOutput('✓ 관리자 계정이 성공적으로 생성되었습니다!')
        ->assertExitCode(0);

        $user = User::where('email', 'random@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNotEmpty($user->password);
    }

    public function test_prevent_duplicate_email()
    {
        // 첫 번째 사용자 생성
        User::create([
            'email' => 'duplicate@example.com',
            'name' => '기존 사용자',
            'password' => bcrypt('password'),
            'isAdmin' => true,
            'utype' => 'admin',
        ]);

        // 동일한 이메일로 생성 시도
        $this->artisan('admin:user-create', [
            '--email' => 'duplicate@example.com',
            '--name' => '중복 시도',
            '--password' => 'TestPass123!',
            '--type' => 'admin',
            '--force' => true,
        ])
        ->expectsOutput('이미 등록된 이메일입니다: duplicate@example.com')
        ->assertExitCode(1);
    }

    public function test_validate_email_format()
    {
        $this->artisan('admin:user-create', [
            '--email' => 'invalid-email',
            '--name' => '잘못된 이메일',
            '--password' => 'TestPass123!',
            '--type' => 'admin',
            '--force' => true,
        ])
        ->expectsOutputToContain('유효한 이메일 형식이 아닙니다')
        ->assertExitCode(1);
    }

    public function test_validate_password_requirements()
    {
        $this->artisan('admin:user-create', [
            '--email' => 'weak@example.com',
            '--name' => '약한 비밀번호',
            '--password' => 'weak',  // 요구사항 미충족
            '--type' => 'admin',
            '--force' => true,
        ])
        ->expectsOutputToContain('비밀번호가 보안 요구사항을 충족하지 않습니다')
        ->assertExitCode(1);
    }

    public function test_validate_admin_type()
    {
        $this->artisan('admin:user-create', [
            '--email' => 'invalid-type@example.com',
            '--name' => '잘못된 타입',
            '--password' => 'TestPass123!',
            '--type' => 'invalid_type',
            '--force' => true,
        ])
        ->expectsOutputToContain('유효하지 않은 관리자 타입입니다')
        ->assertExitCode(1);
    }

    public function test_create_admin_updates_type_count()
    {
        $initialCount = AdminUsertype::where('code', 'super')->value('cnt');

        $this->artisan('admin:user-create', [
            '--email' => 'count@example.com',
            '--name' => '카운트 테스트',
            '--password' => 'TestPass123!',
            '--type' => 'super',
            '--force' => true,
        ])->assertExitCode(0);

        $newCount = AdminUsertype::where('code', 'super')->value('cnt');
        $this->assertEquals($initialCount + 1, $newCount);
    }

    public function test_password_expiry_is_set()
    {
        // config 설정을 모킹하거나 기본값 사용
        config(['admin.setting.password.expiry_days' => 90]);

        $this->artisan('admin:user-create', [
            '--email' => 'expiry@example.com',
            '--name' => '만료일 테스트',
            '--password' => 'TestPass123!',
            '--type' => 'admin',
            '--force' => true,
        ])->assertExitCode(0);

        $user = User::where('email', 'expiry@example.com')->first();
        $this->assertNotNull($user->password_expires_at);
        $this->assertEquals(90, $user->password_expiry_days);
    }
}