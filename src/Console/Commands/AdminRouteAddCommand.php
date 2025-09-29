<?php

namespace Jiny\Admin\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * 관리자 라우트 추가/복구 명령어
 * 
 * 기존 CRUD 모듈에 라우트가 빠진 경우 라우트만 추가하거나,
 * 실수로 삭제된 라우트를 복구하는 데 사용됩니다.
 * 
 * 주요 기능:
 * - 컨트롤러 파일 존재 확인
 * - 라우트 중복 확인
 * - admin.php 파일에 라우트 추가
 * - 라우트 정보 표시
 * 
 * @package Jiny\Admin
 * @author JinyPHP
 * @since 1.0.0
 */
class AdminRouteAddCommand extends Command
{
    /**
     * 콘솔 명령어 시그니처
     * 
     * 사용법: php artisan admin:route-add {module} {feature}
     * 
     * Arguments:
     *   module : 모듈 이름 (예: shop, blog)
     *   feature : 기능 이름 (예: product, category)
     * 
     * 이 명령은 컨트롤러가 이미 존재할 때만 작동합니다.
     *
     * @var string
     */
    protected $signature = 'admin:route-add {module : The module name} {feature : The feature name}';

    /**
     * 콘솔 명령어 설명
     * 
     * 기존 CRUD 컨트롤러에 라우트를 추가하거나 복구합니다.
     * 주로 다음과 같은 상황에서 사용됩니다:
     * - 라우트 파일이 실수로 삭제된 경우
     * - 라우트가 누락된 기존 모듈에 라우트 추가
     *
     * @var string
     */
    protected $description = 'Add or restore routes for an Admin CRUD controller';

    /**
     * 명령어 실행 메인 메서드
     * 
     * 실행 순서:
     * 1. 컨트롤러 파일 존재 확인
     * 2. 라우트 중복 검사
     * 3. 라우트 추가
     * 4. 결과 표시
     * 
     * @return int 명령어 실행 결과 (0: 성공, 1: 실패)
     */
    public function handle()
    {
        $module = $this->argument('module');
        $feature = $this->argument('feature');

        // Convert to proper case
        $moduleStudly = Str::studly($module);
        $featureStudly = Str::studly($feature);
        $featureSnake = Str::snake($feature);

        $this->info("Checking routes for {$moduleStudly}::{$featureStudly}...");

        // Check if controller files exist
        if (! $this->checkControllerExists($moduleStudly, $featureStudly)) {
            $this->error("Controller files not found for {$moduleStudly}::{$featureStudly}");
            $this->line("Please run 'php artisan admin:make {$module} {$feature}' first to create the controllers.");

            return 1;
        }

        // Check if routes already exist
        if ($this->checkRoutesExist($moduleStudly, $featureSnake)) {
            $this->info("Routes already exist for {$moduleStudly}::{$featureStudly}");
            $this->displayExistingRoutes($featureSnake);

            return 0;
        }

        // Add routes
        $this->addRoutes($moduleStudly, $featureStudly, $featureSnake);

        $this->info("Routes added successfully for {$moduleStudly}::{$featureStudly}!");
        $this->displayRouteInfo($featureSnake);

        return 0;
    }

    /**
     * 컨트롤러 파일 존재 확인
     * 
     * 라우트를 추가하기 전에 해당 컨트롤러가 실제로
     * 존재하는지 확인합니다. 메인 컨트롤러 파일이
     * 없으면 false를 반환합니다.
     * 
     * @param string $module 모듈 이름 (StudlyCase)
     * @param string $feature 기능 이름 (StudlyCase)
     * @return bool 컨트롤러 존재 여부
     */
    protected function checkControllerExists($module, $feature)
    {
        $controllerPath = base_path("jiny/{$module}/App/Http/Controllers/Admin/Admin{$feature}");

        if (! File::exists($controllerPath)) {
            return false;
        }

        // Check for main controller file
        $mainController = "{$controllerPath}/Admin{$feature}.php";
        if (! File::exists($mainController)) {
            return false;
        }

        $this->line('✓ Controllers found at: '.str_replace(base_path(), '', $controllerPath));

        return true;
    }

    /**
     * 라우트 중복 확인
     * 
     * admin.php 파일에 해당 기능의 라우트가 이미 등록되어
     * 있는지 확인합니다. 정규표현식으로 라우트 프리픽스를
     * 검색하여 중복을 방지합니다.
     * 
     * @param string $module 모듈 이름 (StudlyCase)
     * @param string $featureSnake 기능 이름 (snake_case)
     * @return bool 라우트 존재 여부
     */
    protected function checkRoutesExist($module, $featureSnake)
    {
        $routePath = base_path("jiny/{$module}/routes/admin.php");

        if (! File::exists($routePath)) {
            return false;
        }

        $content = File::get($routePath);

        // Check if route with this prefix already exists
        if (preg_match("/Route::group\(\['prefix' => '{$featureSnake}'\]/", $content)) {
            return true;
        }

        return false;
    }

    /**
     * admin.php에 라우트 추가
     * 
     * 필요한 경우 routes 디렉토리와 admin.php 파일을 생성하고,
     * CRUD 라우트를 추가합니다.
     * 
     * 추가되는 라우트:
     * - GET /admin/{feature} : 리스트 페이지
     * - GET /admin/{feature}/create : 생성 페이지
     * - GET /admin/{feature}/{id}/edit : 수정 페이지
     * - GET /admin/{feature}/{id} : 상세 페이지
     * - DELETE /admin/{feature}/{id} : 삭제 처리
     * 
     * @param string $module 모듈 이름 (StudlyCase)
     * @param string $feature 기능 이름 (StudlyCase)
     * @param string $featureSnake 기능 이름 (snake_case)
     * @return void
     */
    protected function addRoutes($module, $feature, $featureSnake)
    {
        $this->info('Adding routes...');

        $routePath = base_path("jiny/{$module}/routes/admin.php");

        // Create routes directory and file if not exists
        if (! File::exists(dirname($routePath))) {
            File::makeDirectory(dirname($routePath), 0755, true);
            $this->line('  - Created routes directory');
        }

        if (! File::exists($routePath)) {
            $initialContent = "<?php\n\nuse Illuminate\Support\Facades\Route;\n\n";
            File::put($routePath, $initialContent);
            $this->line('  - Created admin.php route file');
        }

        // Route template
        $routeTemplate = "
// Admin {$feature} Routes
Route::middleware(['web'])->prefix('admin')->group(function () {
    Route::group(['prefix' => '{$featureSnake}'], function () {
        Route::get('/', \\Jiny\\{$module}\\App\\Http\\Controllers\\Admin\\Admin{$feature}\\Admin{$feature}::class)
            ->name('admin.{$featureSnake}');
        
        Route::get('/create', \\Jiny\\{$module}\\App\\Http\\Controllers\\Admin\\Admin{$feature}\\Admin{$feature}Create::class)
            ->name('admin.{$featureSnake}.create');
        
        Route::get('/{id}/edit', \\Jiny\\{$module}\\App\\Http\\Controllers\\Admin\\Admin{$feature}\\Admin{$feature}Edit::class)
            ->name('admin.{$featureSnake}.edit');
        
        Route::get('/{id}', \\Jiny\\{$module}\\App\\Http\\Controllers\\Admin\\Admin{$feature}\\Admin{$feature}Show::class)
            ->name('admin.{$featureSnake}.show');
        
        Route::delete('/{id}', \\Jiny\\{$module}\\App\\Http\\Controllers\\Admin\\Admin{$feature}\\Admin{$feature}Delete::class)
            ->name('admin.{$featureSnake}.delete');
    });
});
";

        // Append routes to file
        File::append($routePath, $routeTemplate);
        $this->line('  - Routes added to admin.php');
    }

    /**
     * 기존 라우트 정보 표시
     * 
     * 이미 등록된 라우트가 있을 때 해당 라우트들을
     * 테이블 형태로 정리하여 표시합니다.
     * 
     * @param string $featureSnake 기능 이름 (snake_case)
     * @return void
     */
    protected function displayExistingRoutes($featureSnake)
    {
        $this->newLine();
        $this->table(
            ['Route Name', 'Method', 'URI'],
            [
                ["admin.{$featureSnake}", 'GET', "/admin/{$featureSnake}"],
                ["admin.{$featureSnake}.create", 'GET', "/admin/{$featureSnake}/create"],
                ["admin.{$featureSnake}.edit", 'GET', "/admin/{$featureSnake}/{id}/edit"],
                ["admin.{$featureSnake}.show", 'GET', "/admin/{$featureSnake}/{id}"],
                ["admin.{$featureSnake}.delete", 'DELETE', "/admin/{$featureSnake}/{id}"],
            ]
        );
    }

    /**
     * 라우트 정보 표시
     * 
     * 새로 추가된 라우트 정보를 테이블 형태로 표시하고,
     * 관리자 패널에 접근할 수 있는 URL을 안내합니다.
     * 
     * 표시 내용:
     * - 라우트 이름, HTTP 메서드, URI 경로
     * - 관리자 패널 접근 URL
     * 
     * @param string $featureSnake 기능 이름 (snake_case)
     * @return void
     */
    protected function displayRouteInfo($featureSnake)
    {
        $this->newLine();
        $this->comment('Available routes:');
        $this->table(
            ['Route Name', 'Method', 'URI'],
            [
                ["admin.{$featureSnake}", 'GET', "/admin/{$featureSnake}"],
                ["admin.{$featureSnake}.create", 'GET', "/admin/{$featureSnake}/create"],
                ["admin.{$featureSnake}.edit", 'GET', "/admin/{$featureSnake}/{id}/edit"],
                ["admin.{$featureSnake}.show", 'GET', "/admin/{$featureSnake}/{id}"],
                ["admin.{$featureSnake}.delete", 'DELETE', "/admin/{$featureSnake}/{id}"],
            ]
        );

        $this->newLine();
        $this->line('You can now access your admin panel at:');
        $this->info('  '.url("/admin/{$featureSnake}"));
    }
}
