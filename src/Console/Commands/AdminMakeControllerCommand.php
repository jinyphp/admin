<?php

namespace Jiny\Admin\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class AdminMakeControllerCommand extends Command
{
    protected $signature = 'admin:make-controller {module} {controller} {--force : Overwrite existing controllers}';
    protected $description = 'Generate admin controllers (CRUD) for a module';

    public function handle()
    {
        $module = $this->argument('module');
        $controller = $this->argument('controller');
        $force = $this->option('force');

        $moduleUC = ucfirst($module);
        $moduleLC = strtolower($module);
        $controllerName = 'Admin' . ucfirst($controller);
        $featureName = ucfirst($controller);
        $featuresLC = Str::snake(Str::plural($controller));

        // 대상 디렉토리
        $targetDir = base_path("jiny/{$module}/App/Http/Controllers/Admin/{$controllerName}");

        // 디렉토리가 이미 존재하는지 확인
        if (File::exists($targetDir) && !$force) {
            $this->error("Controllers already exist: {$targetDir}");
            $this->info('Use --force option to overwrite');
            return 1;
        }

        // 디렉토리 생성
        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        // 생성할 컨트롤러 목록
        $controllers = [
            '' => 'Admin.stub',                    // 메인 컨트롤러 (목록)
            'Create' => 'AdminCreate.stub',        // 생성
            'Edit' => 'AdminEdit.stub',            // 수정
            'Delete' => 'AdminDelete.stub',        // 삭제
            'Show' => 'AdminShow.stub',            // 상세 보기
        ];

        $stubPath = base_path('jiny/admin/stubs/controller');
        
        foreach ($controllers as $suffix => $stubFile) {
            $stubFilePath = $stubPath . '/' . $stubFile;
            
            if (!File::exists($stubFilePath)) {
                $this->error("Stub file not found: {$stubFilePath}");
                continue;
            }

            $stubContent = File::get($stubFilePath);

            // 플레이스홀더 치환
            $replacements = [
                '{{Module}}' => $moduleUC,
                '{{module}}' => $moduleLC,
                '{{Feature}}' => $featureName,
                '{{feature}}' => strtolower($controller),
                '{{features}}' => $featuresLC,
                '{{Features}}' => ucfirst($featuresLC),
            ];

            $content = str_replace(array_keys($replacements), array_values($replacements), $stubContent);

            // 파일 저장
            $fileName = $controllerName . $suffix . '.php';
            $filePath = $targetDir . '/' . $fileName;

            File::put($filePath, $content);

            $this->info("Created: {$filePath}");
        }

        // JSON 파일도 함께 생성하도록 안내
        $this->newLine();
        $this->info("Controllers created successfully!");
        $this->warn("Don't forget to create the JSON configuration file:");
        $this->line("  php artisan admin:make-json {$module} {$controller}");
        $this->newLine();
        $this->warn("And register the routes:");
        $this->line("  php artisan admin:route-add {$module} {$controller}");

        return 0;
    }
}