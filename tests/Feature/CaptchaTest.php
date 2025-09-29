<?php

namespace Jiny\Admin\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Jiny\Admin\Services\Captcha\CaptchaManager;
use Jiny\Admin\Services\Captcha\RecaptchaDriver;
use Jiny\Admin\Services\Captcha\HcaptchaDriver;
use Jiny\Admin\Models\User;
use Jiny\Admin\Models\AdminUsertype;

class CaptchaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // 테스트용 관리자 타입 생성
        AdminUsertype::create([
            'code' => 'super',
            'title' => 'Super Admin',
            'enable' => true,
        ]);
    }

    /**
     * CAPTCHA가 비활성화된 경우 로그인 테스트
     */
    public function test_login_without_captcha_when_disabled()
    {
        // CAPTCHA 비활성화
        config(['admin.setting.captcha.enabled' => false]);
        
        // 테스트 사용자 생성
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'isAdmin' => true,
            'utype' => 'super',
        ]);
        
        // 로그인 시도
        $response = $this->post(route('admin.login.post'), [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);
        
        // 성공적으로 리다이렉트되어야 함
        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * CAPTCHA가 활성화되고 조건부 모드에서 첫 번째 시도 테스트
     */
    public function test_first_login_attempt_without_captcha_in_conditional_mode()
    {
        // CAPTCHA 조건부 모드 활성화
        config([
            'admin.setting.captcha.enabled' => true,
            'admin.setting.captcha.mode' => 'conditional',
            'admin.setting.captcha.show_after_attempts' => 3,
        ]);
        
        // 테스트 사용자 생성
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'isAdmin' => true,
            'utype' => 'super',
        ]);
        
        // 첫 번째 로그인 시도 (CAPTCHA 없이)
        $response = $this->post(route('admin.login.post'), [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);
        
        // 성공적으로 리다이렉트되어야 함
        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * 실패 횟수 초과 후 CAPTCHA 필요 테스트
     */
    public function test_captcha_required_after_failed_attempts()
    {
        // CAPTCHA 조건부 모드 활성화
        config([
            'admin.setting.captcha.enabled' => true,
            'admin.setting.captcha.mode' => 'conditional',
            'admin.setting.captcha.show_after_attempts' => 3,
        ]);
        
        // 테스트 사용자 생성
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('correct_password'),
            'isAdmin' => true,
            'utype' => 'super',
        ]);
        
        // 3번 실패 시도
        for ($i = 0; $i < 3; $i++) {
            $this->post(route('admin.login.post'), [
                'email' => 'admin@example.com',
                'password' => 'wrong_password',
            ]);
        }
        
        // 4번째 시도에서 CAPTCHA 없이 시도
        $response = $this->post(route('admin.login.post'), [
            'email' => 'admin@example.com',
            'password' => 'correct_password',
        ]);
        
        // CAPTCHA 에러로 리다이렉트되어야 함
        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors('captcha');
        $this->assertGuest();
    }

    /**
     * CAPTCHA Manager isRequired 메소드 테스트
     */
    public function test_captcha_manager_is_required_method()
    {
        $manager = app(CaptchaManager::class);
        
        // CAPTCHA 비활성화 상태
        config(['admin.setting.captcha.enabled' => false]);
        $this->assertFalse($manager->isRequired('test@example.com', '127.0.0.1'));
        
        // CAPTCHA 활성화, 항상 모드
        config([
            'admin.setting.captcha.enabled' => true,
            'admin.setting.captcha.mode' => 'always',
        ]);
        $this->assertTrue($manager->isRequired());
        
        // CAPTCHA 활성화, 조건부 모드
        config([
            'admin.setting.captcha.enabled' => true,
            'admin.setting.captcha.mode' => 'conditional',
            'admin.setting.captcha.show_after_attempts' => 3,
        ]);
        
        // 실패 횟수가 없을 때
        $this->assertFalse($manager->isRequired('new@example.com', '127.0.0.1'));
        
        // 실패 횟수 추가
        $manager->incrementFailedAttempts('test@example.com', '127.0.0.1');
        $manager->incrementFailedAttempts('test@example.com', '127.0.0.1');
        $manager->incrementFailedAttempts('test@example.com', '127.0.0.1');
        
        // 3회 실패 후에는 CAPTCHA 필요
        $this->assertTrue($manager->isRequired('test@example.com', '127.0.0.1'));
        
        // 실패 횟수 초기화
        $manager->resetFailedAttempts('test@example.com', '127.0.0.1');
        $this->assertFalse($manager->isRequired('test@example.com', '127.0.0.1'));
    }

    /**
     * reCAPTCHA 드라이버 초기화 테스트
     */
    public function test_recaptcha_driver_initialization()
    {
        config([
            'admin.setting.captcha.recaptcha.site_key' => 'test_site_key',
            'admin.setting.captcha.recaptcha.secret_key' => 'test_secret_key',
            'admin.setting.captcha.recaptcha.version' => 'v2',
        ]);
        
        $driver = new RecaptchaDriver(config('admin.setting.captcha.recaptcha'));
        
        $this->assertEquals('test_site_key', $driver->getSiteKey());
        $this->assertStringContainsString('g-recaptcha', $driver->render());
        $this->assertStringContainsString('google.com/recaptcha', $driver->getScript());
    }

    /**
     * hCaptcha 드라이버 초기화 테스트
     */
    public function test_hcaptcha_driver_initialization()
    {
        config([
            'admin.setting.captcha.hcaptcha.site_key' => 'test_site_key',
            'admin.setting.captcha.hcaptcha.secret_key' => 'test_secret_key',
        ]);
        
        $driver = new HcaptchaDriver(config('admin.setting.captcha.hcaptcha'));
        
        $this->assertEquals('test_site_key', $driver->getSiteKey());
        $this->assertStringContainsString('h-captcha', $driver->render());
        $this->assertStringContainsString('hcaptcha.com', $driver->getScript());
    }

    /**
     * CAPTCHA 미들웨어 테스트
     */
    public function test_captcha_middleware()
    {
        // CAPTCHA 활성화
        config([
            'admin.setting.captcha.enabled' => true,
            'admin.setting.captcha.mode' => 'always',
        ]);
        
        // 미들웨어가 적용된 라우트로 POST 요청
        $response = $this->post('/test-captcha-route', [
            'email' => 'test@example.com',
        ]);
        
        // CAPTCHA 없이는 실패해야 함
        $response->assertSessionHasErrors('captcha');
    }

    /**
     * 로그인 페이지에서 CAPTCHA 표시 테스트
     */
    public function test_login_page_shows_captcha_when_required()
    {
        // CAPTCHA 활성화, 항상 모드
        config([
            'admin.setting.captcha.enabled' => true,
            'admin.setting.captcha.mode' => 'always',
            'admin.setting.captcha.driver' => 'recaptcha',
            'admin.setting.captcha.recaptcha.site_key' => 'test_site_key',
        ]);
        
        $response = $this->get(route('admin.login'));
        
        $response->assertStatus(200);
        // CAPTCHA 관련 요소가 페이지에 포함되어 있는지 확인
        $response->assertSee('보안 인증');
        $response->assertSee('g-recaptcha');
    }
}