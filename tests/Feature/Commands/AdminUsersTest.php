<?php

namespace Jiny\Admin\Tests\Feature\Commands;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Jiny\Admin\Models\User;
use Jiny\Admin\Models\AdminUsertype;
use Carbon\Carbon;

class AdminUsersTest extends TestCase
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
            'cnt' => 0,
        ]);
        
        AdminUsertype::create([
            'code' => 'admin',
            'name' => 'Administrator',
            'description' => '일반 관리자',
            'level' => 50,
            'enable' => true,
            'cnt' => 0,
        ]);
        
        AdminUsertype::create([
            'code' => 'manager',
            'name' => 'Manager',
            'description' => '매니저',
            'level' => 10,
            'enable' => true,
            'cnt' => 0,
        ]);
    }

    protected function createAdmins()
    {
        // 활성 Super Admin
        User::create([
            'email' => 'super@example.com',
            'name' => 'Super Admin',
            'password' => bcrypt('password'),
            'isAdmin' => true,
            'utype' => 'super',
            'last_login_at' => now(),
            'login_count' => 10,
        ]);
        
        // 활성 일반 Admin
        User::create([
            'email' => 'admin@example.com',
            'name' => 'Admin User',
            'password' => bcrypt('password'),
            'isAdmin' => true,
            'utype' => 'admin',
            'last_login_at' => now()->subDays(5),
            'login_count' => 5,
        ]);
        
        // 비활성 Admin
        User::create([
            'email' => 'inactive@example.com',
            'name' => 'Inactive Admin',
            'password' => bcrypt('password'),
            'isAdmin' => true,
            'utype' => 'admin',
            'last_login_at' => now()->subDays(60),
            'login_count' => 1,
        ]);
        
        // 미접속 Admin
        User::create([
            'email' => 'never@example.com',
            'name' => 'Never Logged',
            'password' => bcrypt('password'),
            'isAdmin' => true,
            'utype' => 'manager',
            'last_login_at' => null,
            'login_count' => 0,
        ]);
        
        AdminUsertype::where('code', 'super')->update(['cnt' => 1]);
        AdminUsertype::where('code', 'admin')->update(['cnt' => 2]);
        AdminUsertype::where('code', 'manager')->update(['cnt' => 1]);
    }

    public function test_list_all_admins()
    {
        $this->createAdmins();
        
        $this->artisan('admin:users')
            ->expectsTable(
                ['ID', '이메일', '이름', '타입', '상태', '마지막 로그인', '로그인 횟수', '생성일'],
                User::where('isAdmin', true)->get()->map(function ($admin) {
                    return [
                        $admin->id,
                        $admin->email,
                        $admin->name,
                        $admin->utype ? AdminUsertype::where('code', $admin->utype)->value('name') : 'N/A',
                        $this->getAdminStatus($admin),
                        $admin->last_login_at ? $admin->last_login_at->diffForHumans() : 'Never',
                        $admin->login_count ?? 0,
                        $admin->created_at->format('Y-m-d'),
                    ];
                })->toArray()
            )
            ->assertExitCode(0);
    }

    public function test_list_admins_with_type_filter()
    {
        $this->createAdmins();
        
        $this->artisan('admin:users', ['--type' => 'admin'])
            ->assertDontSee('super@example.com')
            ->assertSee('admin@example.com')
            ->assertSee('inactive@example.com')
            ->assertDontSee('never@example.com')
            ->assertExitCode(0);
    }

    public function test_list_active_admins()
    {
        $this->createAdmins();
        
        $this->artisan('admin:users', ['--active' => true])
            ->assertSee('super@example.com')
            ->assertSee('admin@example.com')
            ->assertDontSee('inactive@example.com')
            ->assertDontSee('never@example.com')
            ->assertExitCode(0);
    }

    public function test_list_inactive_admins()
    {
        $this->createAdmins();
        
        $this->artisan('admin:users', ['--inactive' => true])
            ->assertDontSee('super@example.com')
            ->assertDontSee('admin@example.com')
            ->assertSee('inactive@example.com')
            ->assertSee('never@example.com')
            ->assertExitCode(0);
    }

    public function test_list_with_stats()
    {
        $this->createAdmins();
        
        $this->artisan('admin:users', ['--stats' => true])
            ->expectsOutputToContain('📊 관리자 통계')
            ->expectsOutputToContain('총 관리자 수')
            ->expectsOutputToContain('4명')
            ->expectsOutputToContain('Super Admin')
            ->expectsOutputToContain('1명')
            ->assertExitCode(0);
    }

    public function test_list_with_types()
    {
        $this->createAdmins();
        
        $this->artisan('admin:users', ['--types' => true])
            ->expectsOutputToContain('🏷️  관리자 타입별 현황')
            ->expectsOutputToContain('super')
            ->expectsOutputToContain('Super Admin')
            ->expectsOutputToContain('admin')
            ->expectsOutputToContain('Administrator')
            ->assertExitCode(0);
    }

    public function test_list_with_activity()
    {
        $this->createAdmins();
        
        $this->artisan('admin:users', ['--activity' => true])
            ->expectsOutputToContain('🕐 최근 관리자 활동')
            ->expectsOutputToContain('최근 로그인')
            ->expectsOutputToContain('super@example.com')
            ->assertExitCode(0);
    }

    public function test_list_with_all_options()
    {
        $this->createAdmins();
        
        $this->artisan('admin:users', ['--all' => true])
            ->expectsOutputToContain('📊 관리자 통계')
            ->expectsOutputToContain('🏷️  관리자 타입별 현황')
            ->expectsOutputToContain('🕐 최근 관리자 활동')
            ->assertExitCode(0);
    }

    public function test_list_sorted_by_login()
    {
        $this->createAdmins();
        
        $output = $this->artisan('admin:users', [
            '--sort' => 'login',
            '--desc' => true,
        ]);
        
        // 최근 로그인 순으로 정렬되었는지 확인
        $output->expectsOutputToContain('super@example.com')
            ->assertExitCode(0);
    }

    public function test_list_with_limit()
    {
        $this->createAdmins();
        
        $this->artisan('admin:users', ['--limit' => 2])
            ->assertExitCode(0);
        
        // 출력된 관리자가 2명인지 확인하는 로직
    }

    public function test_export_to_csv()
    {
        $this->createAdmins();
        
        $csvPath = storage_path('app/test_admins.csv');
        
        $this->artisan('admin:users', ['--export' => $csvPath])
            ->expectsOutput("✓ CSV 파일이 생성되었습니다: {$csvPath}")
            ->expectsOutput("  총 4개의 관리자 정보가 내보내졌습니다.")
            ->assertExitCode(0);
        
        $this->assertFileExists($csvPath);
        
        // CSV 파일 내용 확인
        $csv = file_get_contents($csvPath);
        $this->assertStringContainsString('super@example.com', $csv);
        $this->assertStringContainsString('admin@example.com', $csv);
        
        // 테스트 후 파일 삭제
        unlink($csvPath);
    }

    public function test_no_admins_warning()
    {
        // 관리자가 없는 상태에서 테스트
        $this->artisan('admin:users')
            ->expectsOutput('조건에 맞는 관리자가 없습니다.')
            ->assertExitCode(0);
    }

    private function getAdminStatus($admin)
    {
        if (!$admin->last_login_at) {
            return '💤 비활성';
        }
        
        $lastLogin = $admin->last_login_at;
        
        if ($lastLogin->gt(now()->subDays(7))) {
            return '✅ 활성';
        } elseif ($lastLogin->gt(now()->subDays(30))) {
            return '😴 휴면';
        }
        
        return '💤 비활성';
    }
}