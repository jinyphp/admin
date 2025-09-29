<?php

namespace Jiny\Admin\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * 관리자 CRUD 모듈 생성 명령어
 * 
 * @jiny/admin 패키지의 핵심 명령어로, Laravel Artisan을 통해
 * 완전한 CRUD(Create, Read, Update, Delete) 기능을 가진
 * 관리자 모듈을 자동으로 생성합니다.
 * 
 * @package Jiny\Admin
 * @author JinyPHP
 * @since 1.0.0
 */
class AdminMakeCommand extends Command
{
    /**
     * 콘솔 명령어 시그니처
     * 
     * 사용법: php artisan admin:make {module} {feature}
     * 
     * Arguments:
     *   module : 모듈 이름 (예: shop, blog, crm)
     *   feature : 기능 이름 (예: product, category, customer)
     * 
     * Options:
     *   --controller : 컨트롤러만 생성
     *   --json : JSON 설정 파일만 생성
     *   --view : 뷰 파일만 생성
     *   --model : 모델만 생성
     *   --migrate : 마이그레이션만 생성
     *   --route : 라우트만 등록
     *   --seed : 시더만 생성
     *   --factory : 팩토리만 생성
     *   --all : 모든 구성요소 생성 (기본값)
     *   --fields : 추가 필드 정의 (예: --fields="name:string,price:decimal")
     *   --force : 기존 파일 덮어쓰기
     *
     * @var string
     */
    protected $signature = 'admin:make {module : The module name} {feature : The feature name} 
                            {--controller : Generate controllers only}
                            {--json : Generate JSON configuration only}
                            {--view : Generate views only}
                            {--model : Generate model only}
                            {--migrate : Generate migration only}
                            {--route : Register routes only}
                            {--seed : Generate seeder only}
                            {--factory : Generate factory only}
                            {--all : Generate all components (default)}
                            {--fields= : Comma-separated list of additional fields}
                            {--force : Overwrite existing files}';

    /**
     * 콘솔 명령어 설명
     * 
     * 이 명령어는 다음을 자동으로 생성합니다:
     * - 5개의 컨트롤러 (Index, Create, Edit, Delete, Show)
     * - JSON 설정 파일
     * - Eloquent 모델
     * - 데이터베이스 마이그레이션
     * - 5개의 Blade 뷰 템플릿
     * - 라우트 등록
     * - 모델 팩토리 (옵션)
     * - 시더 (옵션)
     *
     * @var string
     */
    protected $description = 'Create a new Admin CRUD module with granular component selection';

    /**
     * 생성 가능한 컴포넌트 목록
     */
    protected $componentMethods = [
        'controllers' => 'createControllers',
        'json' => 'createJsonConfig',
        'views' => 'createViews',
        'model' => 'createModel',
        'migration' => 'createMigration',
        'routes' => 'registerRoutes',
        'factory' => 'createFactory',
        'seeder' => 'createSeeder',
    ];

    /**
     * 명령어 실행 메인 메서드
     * 
     * @return int 명령어 실행 결과 (0: 성공, 1: 실패)
     */
    public function handle()
    {
        $module = $this->argument('module');
        $feature = $this->argument('feature');

        // Convert to proper case
        $this->moduleStudly = Str::studly($module);
        $this->featureStudly = Str::studly($feature);
        $this->featureSnake = Str::snake($feature);
        $this->featurePlural = Str::plural($this->featureSnake);

        $this->info("Creating Admin CRUD for {$this->moduleStudly}::{$this->featureStudly}");

        // 생성할 컴포넌트 결정
        $componentsToCreate = $this->determineComponents();

        // 각 컴포넌트 생성
        foreach ($componentsToCreate as $component) {
            if (isset($this->componentMethods[$component])) {
                $method = $this->componentMethods[$component];
                $this->$method();
            }
        }

        // 마이그레이션 실행 (--migrate 옵션이 있을 때만)
        if (in_array('migration', $componentsToCreate) && $this->option('migrate')) {
            if ($this->confirm('Do you want to run the migration now?', true)) {
                $this->runMigration();
            }
        }

        $this->newLine();
        $this->info("✅ Admin CRUD for {$this->moduleStudly}::{$this->featureStudly} created successfully!");
        
        // 생성된 컴포넌트 요약
        $this->displaySummary($componentsToCreate);
        
        return 0;
    }

    /**
     * 생성할 컴포넌트 결정
     */
    protected function determineComponents()
    {
        $components = [];
        
        // 개별 컴포넌트 플래그 확인
        $hasIndividualFlags = false;
        
        if ($this->option('controller')) {
            $components[] = 'controllers';
            $components[] = 'json'; // 컨트롤러는 JSON과 함께 생성
            $hasIndividualFlags = true;
        }
        
        if ($this->option('json')) {
            $components[] = 'json';
            $hasIndividualFlags = true;
        }
        
        if ($this->option('view')) {
            $components[] = 'views';
            $hasIndividualFlags = true;
        }
        
        if ($this->option('model')) {
            $components[] = 'model';
            $hasIndividualFlags = true;
        }
        
        if ($this->option('migrate')) {
            $components[] = 'migration';
            $hasIndividualFlags = true;
        }
        
        if ($this->option('route')) {
            $components[] = 'routes';
            $hasIndividualFlags = true;
        }
        
        if ($this->option('seed')) {
            $components[] = 'seeder';
            $hasIndividualFlags = true;
        }
        
        if ($this->option('factory')) {
            $components[] = 'factory';
            $hasIndividualFlags = true;
        }
        
        // --all 옵션이 있거나 개별 플래그가 없으면 모든 컴포넌트 생성
        if ($this->option('all') || !$hasIndividualFlags) {
            $components = ['controllers', 'json', 'views', 'model', 'migration', 'routes'];
            
            // 추가 옵션으로 factory와 seeder도 포함 가능
            if ($this->option('factory')) {
                $components[] = 'factory';
            }
            if ($this->option('seed')) {
                $components[] = 'seeder';
            }
        }
        
        return array_unique($components);
    }

    /**
     * 컨트롤러 생성
     */
    protected function createControllers()
    {
        $this->info('📁 Creating controllers...');

        $controllerPath = base_path("jiny/{$this->moduleStudly}/App/Http/Controllers/Admin/Admin{$this->featureStudly}");

        // 디렉토리 생성
        if (!File::exists($controllerPath)) {
            File::makeDirectory($controllerPath, 0755, true);
        }

        // 컨트롤러 파일 매핑
        $controllers = [
            'Admin.stub' => "Admin{$this->featureStudly}.php",
            'AdminCreate.stub' => "Admin{$this->featureStudly}Create.php",
            'AdminEdit.stub' => "Admin{$this->featureStudly}Edit.php",
            'AdminDelete.stub' => "Admin{$this->featureStudly}Delete.php",
            'AdminShow.stub' => "Admin{$this->featureStudly}Show.php",
        ];

        foreach ($controllers as $stub => $filename) {
            $this->createFromStub(
                "controller/{$stub}",
                "{$controllerPath}/{$filename}",
                "Controller: {$filename}"
            );
        }
    }

    /**
     * JSON 설정 파일 생성
     */
    protected function createJsonConfig()
    {
        $this->info('📋 Creating JSON configuration...');

        $jsonPath = base_path("jiny/{$this->moduleStudly}/App/Http/Controllers/Admin/Admin{$this->featureStudly}");
        
        if (!File::exists($jsonPath)) {
            File::makeDirectory($jsonPath, 0755, true);
        }

        $this->createFromStub(
            "controller/Admin.json.stub",
            "{$jsonPath}/Admin{$this->featureStudly}.json",
            "JSON Config: Admin{$this->featureStudly}.json"
        );
    }

    /**
     * 뷰 파일 생성
     */
    protected function createViews()
    {
        $this->info('🎨 Creating view files...');

        $viewPath = base_path("jiny/{$this->moduleStudly}/resources/views/admin/admin_{$this->featureSnake}");

        // 디렉토리 생성
        if (!File::exists($viewPath)) {
            File::makeDirectory($viewPath, 0755, true);
        }

        // 뷰 파일 매핑
        $views = [
            'table.blade.stub' => 'table.blade.php',
            'create.blade.stub' => 'create.blade.php',
            'edit.blade.stub' => 'edit.blade.php',
            'show.blade.stub' => 'show.blade.php',
            'search.blade.stub' => 'search.blade.php',
        ];

        foreach ($views as $stub => $filename) {
            $this->createFromStub(
                "views/{$stub}",
                "{$viewPath}/{$filename}",
                "View: {$filename}"
            );
        }
    }

    /**
     * 모델 생성
     */
    protected function createModel()
    {
        $this->info('🗂️ Creating model...');

        $modelPath = base_path("jiny/{$this->moduleStudly}/App/Models");

        if (!File::exists($modelPath)) {
            File::makeDirectory($modelPath, 0755, true);
        }

        $this->createFromStub(
            "model.stub",
            "{$modelPath}/Admin{$this->featureStudly}.php",
            "Model: Admin{$this->featureStudly}.php"
        );
    }

    /**
     * 마이그레이션 생성
     */
    protected function createMigration()
    {
        $this->info('🗄️ Creating migration...');

        $migrationPath = base_path("jiny/{$this->moduleStudly}/database/migrations");

        if (!File::exists($migrationPath)) {
            File::makeDirectory($migrationPath, 0755, true);
        }

        $timestamp = date('Y_m_d_His');
        $filename = "{$timestamp}_create_admin_{$this->featurePlural}_table.php";

        $this->createFromStub(
            "migration.stub",
            "{$migrationPath}/{$filename}",
            "Migration: {$filename}"
        );
    }

    /**
     * 라우트 등록
     */
    protected function registerRoutes()
    {
        $this->info('🛣️ Registering routes...');

        $routePath = base_path("jiny/{$this->moduleStudly}/routes/admin.php");

        // 라우트 디렉토리 및 파일 생성
        if (!File::exists(dirname($routePath))) {
            File::makeDirectory(dirname($routePath), 0755, true);
        }

        if (!File::exists($routePath)) {
            $initialContent = "<?php\n\nuse Illuminate\Support\Facades\Route;\n\n";
            File::put($routePath, $initialContent);
        }

        // 라우트 템플릿
        $routeTemplate = $this->getRouteTemplate();

        // 중복 체크
        $existingContent = File::get($routePath);
        if (strpos($existingContent, "Admin {$this->featureStudly} Routes") !== false) {
            if (!$this->option('force')) {
                $this->warn("  ⚠️ Routes already exist. Use --force to overwrite.");
                return;
            }
        }

        File::append($routePath, $routeTemplate);
        $this->line("  ✅ Routes registered in admin.php");
    }

    /**
     * 팩토리 생성
     */
    protected function createFactory()
    {
        $this->info('🏭 Creating factory...');

        $factoryPath = base_path('database/factories');

        if (!File::exists($factoryPath)) {
            File::makeDirectory($factoryPath, 0755, true);
        }

        $this->createFromStub(
            "factory.stub",
            "{$factoryPath}/Admin{$this->featureStudly}Factory.php",
            "Factory: Admin{$this->featureStudly}Factory.php"
        );
    }

    /**
     * 시더 생성
     */
    protected function createSeeder()
    {
        $this->info('🌱 Creating seeder...');

        $seederPath = base_path('database/seeders');

        if (!File::exists($seederPath)) {
            File::makeDirectory($seederPath, 0755, true);
        }

        $filename = "Admin{$this->featureStudly}Seeder.php";

        $stubPath = __DIR__.'/../../../stubs/seeder.stub';
        if (File::exists($stubPath)) {
            $this->createFromStub(
                "seeder.stub",
                "{$seederPath}/{$filename}",
                "Seeder: {$filename}"
            );
        } else {
            // Fallback
            $content = $this->generateSeederContent($this->featureStudly, $this->featurePlural);
            File::put("{$seederPath}/{$filename}", $content);
            $this->line("  ✅ Seeder: {$filename} (generated)");
        }

        // 시더 실행 옵션 (--seed 옵션이 있을 때만)
        if ($this->option('seed')) {
            if ($this->confirm('Do you want to run the seeder now?', true)) {
                $this->call('db:seed', ['--class' => "Admin{$this->featureStudly}Seeder"]);
            }
        }
    }

    /**
     * 스텁에서 파일 생성
     */
    protected function createFromStub($stubName, $targetPath, $message)
    {
        // 파일이 이미 존재하는 경우
        if (File::exists($targetPath) && !$this->option('force')) {
            $this->warn("  ⚠️ {$message} already exists. Use --force to overwrite.");
            return;
        }

        $stubPath = __DIR__."/../../../stubs/{$stubName}";
        
        if (!File::exists($stubPath)) {
            $this->error("  ❌ Stub not found: {$stubName}");
            return;
        }

        $content = File::get($stubPath);
        $content = $this->replacePlaceholders($content);

        File::put($targetPath, $content);
        $this->line("  ✅ {$message}");
    }

    /**
     * 플레이스홀더 치환
     */
    protected function replacePlaceholders($content)
    {
        $replacements = [
            '{{Module}}' => $this->moduleStudly,
            '{{module}}' => Str::snake($this->moduleStudly),
            '{{Feature}}' => $this->featureStudly,
            '{{feature}}' => $this->featureSnake,
            '{{features}}' => $this->featurePlural,
            '{{Features}}' => Str::studly($this->featurePlural),
            '{{table}}' => "admin_{$this->featurePlural}",
        ];

        foreach ($replacements as $placeholder => $value) {
            $content = str_replace($placeholder, $value, $content);
        }

        return $content;
    }

    /**
     * 라우트 템플릿 가져오기
     */
    protected function getRouteTemplate()
    {
        return "
// Admin {$this->featureStudly} Routes
Route::middleware(['web'])->prefix('admin')->group(function () {
    Route::group(['prefix' => '{$this->featureSnake}'], function () {
        Route::get('/', \\Jiny\\{$this->moduleStudly}\\App\\Http\\Controllers\\Admin\\Admin{$this->featureStudly}\\Admin{$this->featureStudly}::class)
            ->name('admin.{$this->featureSnake}');
        
        Route::get('/create', \\Jiny\\{$this->moduleStudly}\\App\\Http\\Controllers\\Admin\\Admin{$this->featureStudly}\\Admin{$this->featureStudly}Create::class)
            ->name('admin.{$this->featureSnake}.create');
        
        Route::get('/{id}/edit', \\Jiny\\{$this->moduleStudly}\\App\\Http\\Controllers\\Admin\\Admin{$this->featureStudly}\\Admin{$this->featureStudly}Edit::class)
            ->name('admin.{$this->featureSnake}.edit');
        
        Route::get('/{id}', \\Jiny\\{$this->moduleStudly}\\App\\Http\\Controllers\\Admin\\Admin{$this->featureStudly}\\Admin{$this->featureStudly}Show::class)
            ->name('admin.{$this->featureSnake}.show');
        
        Route::delete('/{id}', \\Jiny\\{$this->moduleStudly}\\App\\Http\\Controllers\\Admin\\Admin{$this->featureStudly}\\Admin{$this->featureStudly}Delete::class)
            ->name('admin.{$this->featureSnake}.delete');
    });
});
";
    }

    /**
     * 시더 내용 생성 (fallback)
     */
    protected function generateSeederContent($feature, $tableName)
    {
        return <<<PHP
<?php

namespace Database\\Seeders;

use Illuminate\\Database\\Seeder;
use Illuminate\\Support\\Facades\\DB;
use Carbon\\Carbon;

class Admin{$feature}Seeder extends Seeder
{
    public function run(): void
    {
        \$now = Carbon::now();
        
        \$data = [
            [
                'title' => 'Sample {$feature} 1',
                'description' => 'This is a sample {$feature} entry for testing.',
                'enable' => true,
                'pos' => 1,
                'created_at' => \$now,
                'updated_at' => \$now,
            ],
            [
                'title' => 'Sample {$feature} 2', 
                'description' => 'Another sample {$feature} entry.',
                'enable' => true,
                'pos' => 2,
                'created_at' => \$now,
                'updated_at' => \$now,
            ],
            [
                'title' => 'Disabled {$feature}',
                'description' => 'This {$feature} is disabled for testing.',
                'enable' => false,
                'pos' => 3,
                'created_at' => \$now,
                'updated_at' => \$now,
            ],
        ];
        
        DB::table('admin_{$tableName}')->insert(\$data);
    }
}
PHP;
    }

    /**
     * 마이그레이션 실행
     */
    protected function runMigration()
    {
        $this->info('🚀 Running migration...');
        $this->call('migrate');
    }

    /**
     * 생성 요약 표시
     */
    protected function displaySummary($components)
    {
        $this->newLine();
        $this->info('📊 Summary:');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        
        $componentLabels = [
            'controllers' => '✅ Controllers (5 files)',
            'json' => '✅ JSON Configuration',
            'views' => '✅ Views (5 files)',
            'model' => '✅ Model',
            'migration' => '✅ Migration',
            'routes' => '✅ Routes',
            'factory' => '✅ Factory',
            'seeder' => '✅ Seeder',
        ];

        foreach ($components as $component) {
            if (isset($componentLabels[$component])) {
                $this->line($componentLabels[$component]);
            }
        }

        $this->newLine();
        $this->info('🎯 Next Steps:');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        
        if (in_array('json', $components)) {
            $this->line('1. Customize the JSON configuration file');
        }
        if (in_array('views', $components)) {
            $this->line('2. Update view files according to your needs');
        }
        if (in_array('migration', $components) && !$this->option('migrate')) {
            $this->line('3. Run migration: php artisan migrate');
        }
        if (in_array('seeder', $components) && !$this->option('seed')) {
            $this->line('4. Run seeder: php artisan db:seed --class=Admin' . $this->featureStudly . 'Seeder');
        }
        if (in_array('routes', $components)) {
            $this->line('5. Test your new admin module at:');
            $this->info("   http://your-domain/admin/{$this->featureSnake}");
        }
    }
}