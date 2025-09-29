<?php

namespace Jiny\Admin\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class AdminMakeViewCommand extends Command
{
    protected $signature = 'admin:make-view {module} {controller} {--force : Overwrite existing views}';
    protected $description = 'Generate admin view files (table, create, edit, show, search) for a module';

    public function handle()
    {
        $module = $this->argument('module');
        $controller = $this->argument('controller');
        $force = $this->option('force');

        $moduleLC = strtolower($module);
        $featureName = ucfirst($controller);
        $featureLC = strtolower($controller);
        $featuresLC = Str::snake(Str::plural($controller));
        $viewPath = 'admin_' . Str::snake($controller);

        // 대상 디렉토리
        $targetDir = base_path("jiny/{$module}/resources/views/admin/{$viewPath}");

        // 디렉토리가 이미 존재하는지 확인
        if (File::exists($targetDir) && !$force) {
            $this->error("Views already exist: {$targetDir}");
            $this->info('Use --force option to overwrite');
            return 1;
        }

        // 디렉토리 생성
        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        // 생성할 뷰 파일 목록
        $views = [
            'table.blade.php' => 'table.blade.stub',
            'create.blade.php' => 'create.blade.stub',
            'edit.blade.php' => 'edit.blade.stub',
            'show.blade.php' => 'show.blade.stub',
            'search.blade.php' => 'search.blade.stub',
        ];

        $stubPath = base_path('jiny/admin/stubs/views');
        
        foreach ($views as $viewFile => $stubFile) {
            $stubFilePath = $stubPath . '/' . $stubFile;
            
            if (!File::exists($stubFilePath)) {
                $this->error("Stub file not found: {$stubFilePath}");
                continue;
            }

            $stubContent = File::get($stubFilePath);

            // 플레이스홀더 치환
            $replacements = [
                '{{Module}}' => ucfirst($module),
                '{{module}}' => $moduleLC,
                '{{Feature}}' => $featureName,
                '{{feature}}' => $featureLC,
                '{{features}}' => $featuresLC,
                '{{Features}}' => ucfirst($featuresLC),
            ];

            $content = str_replace(array_keys($replacements), array_values($replacements), $stubContent);

            // 파일 저장
            $filePath = $targetDir . '/' . $viewFile;
            File::put($filePath, $content);

            $this->info("Created: {$filePath}");
        }

        $this->newLine();
        $this->info("Views created successfully!");
        $this->info("View path: jiny-{$moduleLC}::admin.{$viewPath}");
        $this->newLine();
        $this->warn("Don't forget to:");
        $this->line("1. Customize the view files according to your needs");
        $this->line("2. Update table columns in table.blade.php");
        $this->line("3. Update form fields in create.blade.php and edit.blade.php");
        $this->line("4. Update display fields in show.blade.php");
        $this->line("5. Update search filters in search.blade.php");

        return 0;
    }
}