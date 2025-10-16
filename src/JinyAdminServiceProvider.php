<?php

namespace Jiny\Admin;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Livewire\Livewire;
use Jiny\Admin\Services\Captcha\CaptchaManager;

/**
 * Jiny Admin Service Provider
 * 
 * Laravel 애플리케이션에 Jiny Admin 패키지를 등록하고 설정하는 서비스 프로바이더
 * 
 * @package Jiny\Admin
 * @author JinyPHP Team
 * @version 1.0.0
 */
class JinyAdminServiceProvider extends ServiceProvider
{
    /**
     * 패키지 식별자
     * 
     * @var string
     */
    private $package = 'jiny-admin';

    /**
     * 패키지 부팅 메서드
     * 
     * 라우트, 뷰, 마이그레이션, 명령어 등을 등록합니다.
     * 
     * @return void
     */
    public function boot()
    {
        // ========================================
        // 1. 미들웨어 등록
        // ========================================
        $this->registerMiddleware();

        // ========================================
        // 1-1. 미들웨어 설정 (쿠키 등)
        // ========================================
        $this->configureMiddleware();

        // ========================================
        // 2. 라우트 파일 로드
        // ========================================
        $this->loadRoutes();

        // ========================================
        // 3. 뷰 리소스 등록
        // ========================================
        $this->loadViewsFrom(__DIR__.'/../resources/views', $this->package);

        // ========================================
        // 4. 설정 파일 퍼블리싱
        // ========================================
        $this->publishConfiguration();

        // ========================================
        // 5. 데이터베이스 마이그레이션
        // ========================================
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // ========================================
        // 6. Artisan 명령어 등록 (콘솔 환경에서만)
        // ========================================
        if ($this->app->runningInConsole()) {
            $this->registerCommands();
            
            // 패키지 설치/업데이트 시 Tailwind 자동 설정
            $this->autoConfigureTailwind();
        }
    }

    /**
     * 패키지 등록 메서드
     * 
     * 설정 파일 병합 및 서비스 컨테이너 바인딩을 처리합니다.
     * 
     * @return void
     */
    public function register()
    {
        // ========================================
        // 1. 설정 파일 병합
        // ========================================
        $this->mergeConfiguration();

        // ========================================
        // 2. 서비스 컨테이너 바인딩
        // ========================================
        $this->registerServices();

        // ========================================
        // 3. Livewire 컴포넌트 등록
        // ========================================
        $this->registerLivewireComponents();
    }

    /**
     * 미들웨어 등록
     *
     * @return void
     */
    protected function registerMiddleware()
    {
        $router = $this->app->make(Router::class);

        // 관리자 접근 제어 미들웨어
        $router->aliasMiddleware('admin', \Jiny\Admin\Http\Middleware\AdminMiddleware::class);

        // IP 화이트리스트 미들웨어
        $router->aliasMiddleware('ip.whitelist', \Jiny\Admin\Http\Middleware\IpWhitelistMiddleware::class);

        // CAPTCHA 검증 미들웨어
        $router->aliasMiddleware('captcha', \Jiny\Admin\Http\Middleware\CaptchaMiddleware::class);

        // 비밀번호 변경 체크 미들웨어
        $router->aliasMiddleware('check.password.change', \Jiny\Admin\Http\Middleware\CheckPasswordChange::class);
    }

    /**
     * 미들웨어 설정 (쿠키 암호화 제외 등)
     *
     * @return void
     */
    protected function configureMiddleware()
    {
        // Admin 관련 쿠키 암호화 제외가 필요한 경우 여기에 추가
        $this->app->booted(function () {
            try {
                // Admin 패키지에서 암호화 제외할 쿠키가 있으면 여기에 추가
                $adminCookies = [
                    // 예: 'admin_session', 'admin_token' 등
                ];

                if (empty($adminCookies)) {
                    return; // 제외할 쿠키가 없으면 종료
                }

                // EncryptCookies 미들웨어 인스턴스 가져오기
                $encryptCookies = $this->app->make(\Illuminate\Cookie\Middleware\EncryptCookies::class);

                // Reflection을 사용하여 protected $except 속성에 접근
                $reflection = new \ReflectionClass($encryptCookies);

                if ($reflection->hasProperty('except')) {
                    $exceptProperty = $reflection->getProperty('except');
                    $exceptProperty->setAccessible(true);

                    // 기존 제외 목록 가져오기
                    $except = $exceptProperty->getValue($encryptCookies);

                    // Admin 쿠키 추가
                    $except = array_unique(array_merge((array)$except, $adminCookies));

                    // 업데이트된 목록 설정
                    $exceptProperty->setValue($encryptCookies, $except);
                }
            } catch (\Exception $e) {
                // 에러 발생 시에도 앱 실행 계속
                if (config('app.debug')) {
                    \Log::warning('JinyAdmin: Failed to configure cookie encryption exceptions', [
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });
    }

    /**
     * 라우트 파일 로드
     * 
     * @return void
     */
    protected function loadRoutes()
    {
        // 웹 라우트 (로그인, 로그아웃 등)
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        
        // API 라우트
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        
        // 관리자 라우트 (관리자 페이지, 2FA 포함)
        $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');
    }

    /**
     * 설정 파일 퍼블리싱
     * 
     * php artisan vendor:publish --tag=jiny-admin-config
     * php artisan vendor:publish --tag=jiny-admin-assets
     * 
     * @return void
     */
    protected function publishConfiguration()
    {
        // 설정 파일 퍼블리싱
        $this->publishes([
            __DIR__.'/../config/setting.php' => config_path('admin/setting.php'),
            __DIR__.'/../config/captcha.php' => config_path('captcha.php'),
            __DIR__.'/../config/mail.php' => config_path('admin/mail.php'),
        ], 'jiny-admin-config');

        // 에셋 파일 퍼블리싱 (필요한 경우)
        $this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/jiny-admin'),
        ], 'jiny-admin-assets');
    }


    /**
     * Artisan 명령어 등록
     * 
     * @return void
     */
    protected function registerCommands()
    {
        $this->commands([
            // ========================================
            // Admin 모듈 생성/관리 명령어
            // ========================================
            \Jiny\Admin\Console\Commands\AdminMakeCommand::class,          // admin:make
            \Jiny\Admin\Console\Commands\AdminRemoveCommand::class,        // admin:remove
            \Jiny\Admin\Console\Commands\AdminRouteAddCommand::class,      // admin:route-add
            \Jiny\Admin\Console\Commands\AdminMakeJsonCommand::class,      // admin:make-json
            \Jiny\Admin\Console\Commands\AdminMakeControllerCommand::class,// admin:make-controller
            \Jiny\Admin\Console\Commands\AdminMakeViewCommand::class,      // admin:make-view
            
            // ========================================
            // 설치 명령어
            // ========================================
            \Jiny\Admin\Console\Commands\AdminInstallCommand::class,       // admin:install

            // ========================================
            // 관리자 계정 관리 명령어
            // ========================================
            \Jiny\Admin\Console\Commands\AdminUserCreate::class,           // admin:user-create
            \Jiny\Admin\Console\Commands\AdminUserDelete::class,           // admin:user-delete
            \Jiny\Admin\Console\Commands\AdminUsers::class,                // admin:users
            \Jiny\Admin\Console\Commands\AdminUserPassword::class,         // admin:user-password

            // ========================================
            // 보안 관련 명령어
            // ========================================
            \Jiny\Admin\Console\Commands\UnblockPasswordAttempts::class,   // admin:unblock-password
            \Jiny\Admin\Console\Commands\CaptchaLogs::class,              // admin:captcha-logs
            
            // ========================================
            // IP 관리 명령어
            // ========================================
            \Jiny\Admin\Console\Commands\IpCleanup::class,                // admin:ip-cleanup
            \Jiny\Admin\Console\Commands\IpUnblock::class,                // admin:ip-unblock
            \Jiny\Admin\Console\Commands\IpStats::class,                  // admin:ip-stats
            
            // ========================================
            // 유틸리티 명령어
            // ========================================
            \Jiny\Admin\Console\Commands\SyncUserTypeCount::class,        // admin:sync-usertype-count
        ]);
    }

    /**
     * 설정 파일 병합
     * 
     * @return void
     */
    protected function mergeConfiguration()
    {
        // 기본 설정 파일 병합
        $this->mergeConfigFrom(
            __DIR__.'/../config/setting.php', 'admin.setting'
        );

        // 메일 설정 파일 병합
        $this->mergeConfigFrom(
            __DIR__.'/../config/mail.php', 'admin.mail'
        );

        // CAPTCHA 설정 파일 병합
        $this->mergeConfigFrom(
            __DIR__.'/../config/captcha.php', 'captcha'
        );
    }

    /**
     * 서비스 컨테이너 바인딩 등록
     * 
     * @return void
     */
    protected function registerServices()
    {
        // CAPTCHA Manager 싱글톤 등록
        $this->app->singleton(CaptchaManager::class, function ($app) {
            return new CaptchaManager($app);
        });

        // 추가 서비스 바인딩이 필요한 경우 여기에 등록
        // $this->app->singleton(SmsService::class, function ($app) {
        //     return new SmsService($app);
        // });
    }

    /**
     * Livewire 컴포넌트 등록
     * 
     * @return void
     */
    protected function registerLivewireComponents()
    {
        $this->app->afterResolving(BladeCompiler::class, function () {
            if (class_exists(Livewire::class)) {
                // ========================================
                // 기본 Admin CRUD 컴포넌트
                // ========================================
                Livewire::component('jiny-admin::admin-table', \Jiny\Admin\Http\Livewire\AdminTable::class);
                Livewire::component('jiny-admin::admin-create', \Jiny\Admin\Http\Livewire\AdminCreate::class);
                Livewire::component('jiny-admin::admin-edit', \Jiny\Admin\Http\Livewire\AdminEdit::class);
                Livewire::component('jiny-admin::admin-show', \Jiny\Admin\Http\Livewire\AdminShow::class);
                Livewire::component('jiny-admin::admin-search', \Jiny\Admin\Http\Livewire\AdminSearch::class);
                Livewire::component('jiny-admin::admin-delete', \Jiny\Admin\Http\Livewire\AdminDelete::class);

                // ========================================
                // UI 컴포넌트
                // ========================================
                Livewire::component('jiny-admin::admin-notification', \Jiny\Admin\Http\Livewire\AdminNotification::class);
                Livewire::component('jiny-admin::admin-table-setting', \Jiny\Admin\Http\Livewire\AdminTableSetting::class);
                Livewire::component('jiny-admin::admin-header-with-settings', \Jiny\Admin\Http\Livewire\AdminHeaderWithSettings::class);
                Livewire::component('jiny-admin::admin-dash-title', \Jiny\Admin\Http\Livewire\AdminDashTitle::class);

                // ========================================
                // 설정 드로어 컴포넌트
                // ========================================
                Livewire::component('jiny-admin::settings.table-settings-drawer', \Jiny\Admin\Http\Livewire\Settings\TableSettingsDrawer::class);
                Livewire::component('jiny-admin::settings.show-settings-drawer', \Jiny\Admin\Http\Livewire\Settings\ShowSettingsDrawer::class);
                Livewire::component('jiny-admin::settings.create-settings-drawer', \Jiny\Admin\Http\Livewire\Settings\CreateSettingsDrawer::class);
                Livewire::component('jiny-admin::settings.edit-settings-drawer', \Jiny\Admin\Http\Livewire\Settings\EditSettingsDrawer::class);
                Livewire::component('jiny-admin::settings.detail-settings-drawer', \Jiny\Admin\Http\Livewire\Settings\DetailSettingsDrawer::class);
                Livewire::component('jiny-admin::settings.settings-button', \Jiny\Admin\Http\Livewire\Settings\SettingsButton::class);

                // ========================================
                // 특수 기능 컴포넌트
                // ========================================
                Livewire::component('jiny-admin::admin-captcha-logs', \Jiny\Admin\Http\Livewire\AdminCaptchaLogs::class);
            }
        });
    }

    /**
     * Tailwind CSS 자동 설정
     * 
     * 패키지 설치/업데이트 시 Tailwind CSS 설정을 자동으로 업데이트합니다.
     * 
     * @return void
     */
    protected function autoConfigureTailwind()
    {
        // composer install/update 시에만 실행
        if (!app()->environment('production') && file_exists(resource_path('css/app.css'))) {
            $appCssPath = resource_path('css/app.css');
            $content = file_get_contents($appCssPath);
            
            // Tailwind v4 체크 (@source 디렉티브 사용)
            if (strpos($content, '@source') !== false) {
                $sourcesToAdd = [
                    "@source '../../vendor/jinyerp/**/*.blade.php';",
                    "@source '../../vendor/jinyerp/**/*.php';",
                    "@source '../../vendor/jiny/**/*.blade.php';",
                    "@source '../../vendor/jiny/**/*.php';",
                ];
                
                $updated = false;
                foreach ($sourcesToAdd as $source) {
                    if (strpos($content, $source) === false) {
                        // @theme 앞에 추가 또는 파일 끝에 추가
                        if (strpos($content, '@theme') !== false) {
                            $content = str_replace('@theme', $source . "\n@theme", $content);
                        } else {
                            $content .= "\n" . $source;
                        }
                        $updated = true;
                    }
                }
                
                if ($updated) {
                    // 백업 파일 생성
                    $backupPath = $appCssPath . '.backup.' . date('YmdHis');
                    file_put_contents($backupPath, file_get_contents($appCssPath));
                    
                    // 업데이트된 내용 저장
                    file_put_contents($appCssPath, $content);
                    
                    // 안내 메시지 출력
                    echo "\n";
                    echo "✅ Jiny Admin: Tailwind CSS 설정이 자동으로 업데이트되었습니다.\n";
                    echo "   백업 파일: " . $backupPath . "\n";
                    echo "   npm run build 명령어를 실행하여 CSS를 다시 빌드하세요.\n";
                    echo "\n";
                }
            }
            // Tailwind v3 체크 (tailwind.config.js 사용)
            elseif (file_exists(base_path('tailwind.config.js'))) {
                $configPath = base_path('tailwind.config.js');
                $config = file_get_contents($configPath);
                
                $pathsToAdd = [
                    "'./vendor/jinyerp/**/*.blade.php'",
                    "'./vendor/jiny/**/*.blade.php'",
                ];
                
                $updated = false;
                foreach ($pathsToAdd as $path) {
                    if (strpos($config, $path) === false) {
                        // content 배열에 추가
                        $config = preg_replace(
                            '/content:\s*\[([^\]]*)\]/s',
                            "content: [$1,\n        " . $path . "\n    ]",
                            $config
                        );
                        $updated = true;
                    }
                }
                
                if ($updated) {
                    // 백업 파일 생성
                    $backupPath = $configPath . '.backup.' . date('YmdHis');
                    file_put_contents($backupPath, file_get_contents($configPath));
                    
                    // 업데이트된 내용 저장
                    file_put_contents($configPath, $config);
                    
                    echo "\n";
                    echo "✅ Jiny Admin: Tailwind CSS v3 설정이 자동으로 업데이트되었습니다.\n";
                    echo "   백업 파일: " . $backupPath . "\n";
                    echo "   npm run build 명령어를 실행하여 CSS를 다시 빌드하세요.\n";
                    echo "\n";
                }
            }
        }
    }

    /**
     * 패키지에서 제공하는 서비스 목록
     * 
     * @return array
     */
    public function provides()
    {
        return [
            CaptchaManager::class,
        ];
    }
}