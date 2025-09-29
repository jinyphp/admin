<?php

namespace Jiny\Admin\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * 관리자 CRUD 모듈 제거 명령어
 * 
 * admin:make 명령으로 생성된 모든 파일과 데이터베이스 테이블을
 * 안전하게 제거합니다. 이 명령은 다음을 수행합니다:
 * 
 * - 컨트롤러, 모델, 뷰 파일 삭제
 * - 마이그레이션 파일 및 이력 제거
 * - 데이터베이스 테이블 삭제
 * - 라우트 등록 제거
 * - 빈 디렉토리 정리
 * 
 * @package Jiny\Admin
 * @author JinyPHP
 * @since 1.0.0
 */
class AdminRemoveCommand extends Command
{
    /**
     * 콘솔 명령어 시그니처
     * 
     * 사용법: php artisan admin:remove {module} {feature} [--force]
     * 
     * Arguments:
     *   module : 제거할 모듈 이름 (예: shop, blog)
     *   feature : 제거할 기능 이름 (예: product, category)
     * 
     * Options:
     *   --force : 확인 없이 바로 제거
     *
     * @var string
     */
    protected $signature = 'admin:remove {module : The module name} {feature : The feature name} {--force : Force removal without confirmation}';

    /**
     * 콘솔 명령어 설명
     * 
     * admin:make로 생성된 모듈을 완전히 제거합니다.
     * 제거 전 파일 목록을 표시하고 확인을 받습니다.
     *
     * @var string
     */
    protected $description = 'Remove an Admin CRUD controller with all related files';

    /**
     * 제거할 파일 목록
     * 
     * 카테고리별로 정리된 파일 목록을 저장합니다:
     * - Controllers: 컨트롤러 파일들
     * - Model: 모델 파일
     * - Views: 뷰 파일들
     * - Migrations: 마이그레이션 파일들
     *
     * @var array
     */
    protected $filesToRemove = [];

    /**
     * 명령어 실행 메인 메서드
     * 
     * 전체 제거 프로세스를 조율합니다:
     * 1. 제거할 파일 목록 수집
     * 2. 파일 목록 표시
     * 3. 사용자 확인 (--force 옵션이 없는 경우)
     * 4. 파일 제거
     * 5. 라우트 제거
     * 6. 데이터베이스 테이블 삭제
     * 7. 빈 디렉토리 정리
     * 
     * @return int 명령어 실행 결과 (0: 성공, 1: 실패)
     */
    public function handle()
    {
        $module = $this->argument('module');
        $feature = $this->argument('feature');
        $force = $this->option('force');

        // Convert to proper case
        $moduleStudly = Str::studly($module);
        $featureStudly = Str::studly($feature);
        $featureSnake = Str::snake($feature);
        $featurePlural = Str::plural($featureSnake);

        $this->warn("Preparing to remove Admin CRUD for {$moduleStudly}::{$featureStudly}");

        // Collect all files to be removed
        $this->collectFilesToRemove($moduleStudly, $featureStudly, $featureSnake, $featurePlural);

        // Display files to be removed
        $this->displayFilesToRemove();

        // Confirm removal
        if (! $force && ! $this->confirm('Do you want to proceed with removing these files?')) {
            $this->info('Operation cancelled.');

            return;
        }

        // Remove files
        $this->removeFiles();

        // Remove routes
        $this->removeRoutes($moduleStudly, $featureSnake);

        // Drop database table if exists
        $this->dropTable($featurePlural);

        // Clean up empty directories
        $this->cleanupEmptyDirectories($moduleStudly, $featureSnake);

        $this->info("Admin CRUD for {$moduleStudly}::{$featureStudly} removed successfully!");
    }

    /**
     * 제거할 파일 목록 수집
     * 
     * 모듈과 기능 이름을 기반으로 생성된 모든 파일의
     * 경로를 찾아 목록화합니다.
     * 
     * 수집되는 파일:
     * - 6개 컨트롤러 (Admin{Feature}.php, Create, Edit, Delete, Show, .json)
     * - 1개 모델 (Admin{Feature}.php)
     * - 5개 뷰 (create, edit, show, search, table .blade.php)
     * - 마이그레이션 파일들
     * 
     * @param string $module 모듈 이름 (StudlyCase)
     * @param string $feature 기능 이름 (StudlyCase)
     * @param string $featureSnake 기능 이름 (snake_case)
     * @param string $featurePlural 기능 이름 복수형 (snake_case)
     * @return void
     */
    protected function collectFilesToRemove($module, $feature, $featureSnake, $featurePlural)
    {
        // Controllers
        $controllerPath = base_path("jiny/{$module}/App/Http/Controllers/Admin/Admin{$feature}");
        if (File::exists($controllerPath)) {
            $this->filesToRemove['Controllers'] = [
                "{$controllerPath}/Admin{$feature}.php",
                "{$controllerPath}/Admin{$feature}Create.php",
                "{$controllerPath}/Admin{$feature}Edit.php",
                "{$controllerPath}/Admin{$feature}Delete.php",
                "{$controllerPath}/Admin{$feature}Show.php",
                "{$controllerPath}/Admin{$feature}.json",
            ];
        }

        // Model
        $modelPath = base_path("jiny/{$module}/App/Models/Admin{$feature}.php");
        if (File::exists($modelPath)) {
            $this->filesToRemove['Model'] = [$modelPath];
        }

        // Views
        $viewPath = base_path("jiny/{$module}/resources/views/admin/admin_{$featureSnake}");
        if (File::exists($viewPath)) {
            $this->filesToRemove['Views'] = [
                "{$viewPath}/create.blade.php",
                "{$viewPath}/edit.blade.php",
                "{$viewPath}/show.blade.php",
                "{$viewPath}/search.blade.php",
                "{$viewPath}/table.blade.php",
            ];
        }

        // Migration files
        $migrationPath = base_path("jiny/{$module}/database/migrations");
        if (File::exists($migrationPath)) {
            $migrationFiles = File::glob("{$migrationPath}/*_create_admin_{$featurePlural}_table.php");
            if (! empty($migrationFiles)) {
                $this->filesToRemove['Migrations'] = $migrationFiles;
            }
        }
    }

    /**
     * 제거할 파일 목록 표시
     * 
     * 사용자에게 제거될 파일들을 카테고리별로 정리하여 표시합니다.
     * 각 파일의 존재 여부를 확인하고 체크마크로 표시합니다.
     * 
     * 표시 형식:
     * - ✓ : 파일이 존재하며 제거 예정
     * - ✗ : 파일이 이미 없음
     * 
     * @return void
     */
    protected function displayFilesToRemove()
    {
        $this->info('The following files will be removed:');
        $this->newLine();

        foreach ($this->filesToRemove as $category => $files) {
            $this->comment("  {$category}:");
            foreach ($files as $file) {
                if (File::exists($file)) {
                    $this->line('    ✓ '.str_replace(base_path(), '', $file));
                } else {
                    $this->line('    ✗ '.str_replace(base_path(), '', $file).' (not found)');
                }
            }
        }

        $this->newLine();
        $this->warn('Additional changes:');
        $this->line('  - Routes will be removed from admin.php');
        $this->line('  - Database table will be dropped if exists');
        $this->line('  - Empty directories will be cleaned up');
        $this->newLine();
    }

    /**
     * 파일 제거 실행
     * 
     * 수집된 파일 목록을 반복하며 실제로 파일을 삭제합니다.
     * 각 파일 삭제 후 결과를 표시합니다.
     * 
     * @return void
     */
    protected function removeFiles()
    {
        $this->info('Removing files...');

        foreach ($this->filesToRemove as $category => $files) {
            foreach ($files as $file) {
                if (File::exists($file)) {
                    File::delete($file);
                    $this->line('  - Removed: '.basename($file));
                }
            }
        }
    }

    /**
     * admin.php에서 라우트 제거
     * 
     * 정규표현식을 사용하여 해당 기능의 라우트 블록을 찾아 제거합니다.
     * 주석과 라우트 그룹 전체를 깨끗하게 제거하며,
     * 불필요한 빈 줄을 정리합니다.
     * 
     * @param string $module 모듈 이름 (StudlyCase)
     * @param string $featureSnake 기능 이름 (snake_case)
     * @return void
     */
    protected function removeRoutes($module, $featureSnake)
    {
        $this->info('Removing routes...');

        $routePath = base_path("jiny/{$module}/routes/admin.php");

        if (! File::exists($routePath)) {
            $this->line('  - Route file not found, skipping...');

            return;
        }

        $content = File::get($routePath);
        $featureStudly = Str::studly($featureSnake);

        // More specific pattern that only matches the exact feature routes
        // Match the comment line and the entire route block for this specific feature
        $pattern = "/\n*\/\/ Admin ".preg_quote($featureStudly, '/')." Routes\s*\n".
                   "Route::middleware\(\['web'\]\)->prefix\('admin'\)->group\(function \(\) \{\s*\n".
                   "\s*Route::group\(\['prefix' => '".preg_quote($featureSnake, '/')."'\], function \(\) \{[^}]*?\}\);\s*\n".
                   "\}\);/s";

        $newContent = preg_replace($pattern, '', $content);

        // Clean up multiple empty lines
        $newContent = preg_replace("/\n{3,}/", "\n\n", $newContent);

        // Remove trailing newlines at the end of file but keep one
        $newContent = rtrim($newContent)."\n";

        if ($newContent !== $content) {
            File::put($routePath, $newContent);
            $this->line("  - Routes for '{$featureSnake}' removed from admin.php");
        } else {
            $this->line("  - No routes found for '{$featureSnake}'");
        }
    }

    /**
     * 데이터베이스 테이블 삭제
     * 
     * 해당 기능의 데이터베이스 테이블이 존재하면 삭제합니다.
     * --force 옵션이 없는 경우 사용자에게 확인을 받습니다.
     * 테이블 삭제 후 migrations 테이블에서도 관련 기록을 제거합니다.
     * 
     * @param string $featurePlural 기능 이름 복수형 (snake_case)
     * @return void
     */
    protected function dropTable($featurePlural)
    {
        $this->info('Checking database table...');

        $tableName = "admin_{$featurePlural}";

        // Check if table exists
        if (Schema::hasTable($tableName)) {
            $force = $this->option('force');

            if ($force || $this->confirm("Drop database table '{$tableName}'?", true)) {
                // Drop the table
                Schema::dropIfExists($tableName);
                $this->line("  - Dropped table: {$tableName}");

                // Remove migration history from migrations table
                $this->removeMigrationHistory($tableName);
            } else {
                $this->line("  - Kept table: {$tableName}");
            }
        } else {
            $this->line("  - Table '{$tableName}' not found");

            // Still try to remove migration history even if table doesn't exist
            $this->removeMigrationHistory($tableName);
        }
    }

    /**
     * 마이그레이션 히스토리 제거
     * 
     * Laravel의 migrations 테이블에서 해당 테이블의
     * 마이그레이션 실행 기록을 제거합니다.
     * 
     * 이를 통해 나중에 같은 이름의 모듈을 다시 생성할 때
     * 마이그레이션 충돌을 방지합니다.
     * 
     * @param string $tableName 테이블 이름
     * @return void
     */
    protected function removeMigrationHistory($tableName)
    {
        try {
            // Find migration records that match this table
            $migrationPattern = "%create_{$tableName}_table";

            $migrations = DB::table('migrations')
                ->where('migration', 'like', $migrationPattern)
                ->get();

            if ($migrations->count() > 0) {
                foreach ($migrations as $migration) {
                    DB::table('migrations')
                        ->where('id', $migration->id)
                        ->delete();

                    $this->line("  - Removed migration history: {$migration->migration}");
                }
            } else {
                $this->line("  - No migration history found for table: {$tableName}");
            }
        } catch (\Exception $e) {
            $this->warn('  - Could not remove migration history: '.$e->getMessage());
        }
    }

    /**
     * 빈 디렉토리 정리
     * 
     * 파일 제거 후 남은 빈 디렉토리를 자동으로 삭제합니다.
     * 이를 통해 프로젝트 구조를 깨끗하게 유지합니다.
     * 
     * 정리 대상 디렉토리:
     * - 뷰 디렉토리: resources/views/admin/admin_{feature}/
     * - 컨트롤러 디렉토리: App/Http/Controllers/Admin/Admin{Feature}/
     * 
     * @param string $module 모듈 이름 (StudlyCase)
     * @param string $featureSnake 기능 이름 (snake_case)
     * @return void
     */
    protected function cleanupEmptyDirectories($module, $featureSnake)
    {
        $this->info('Cleaning up empty directories...');

        $directories = [
            base_path("jiny/{$module}/resources/views/admin/admin_{$featureSnake}"),
            base_path("jiny/{$module}/App/Http/Controllers/Admin/Admin".Str::studly($featureSnake)),
        ];

        foreach ($directories as $dir) {
            if (File::exists($dir) && File::isDirectory($dir)) {
                $files = File::allFiles($dir);
                if (count($files) === 0) {
                    File::deleteDirectory($dir);
                    $this->line('  - Removed empty directory: '.str_replace(base_path(), '', $dir));
                }
            }
        }
    }
}
