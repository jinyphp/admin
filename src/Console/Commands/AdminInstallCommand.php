<?php

namespace Jiny\Admin\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AdminInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:install 
                            {--force : 강제로 덮어쓰기}
                            {--skip-tailwind : Tailwind CSS 설정 건너뛰기}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Jiny Admin 패키지 설치 및 설정';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🚀 Jiny Admin 패키지 설치를 시작합니다...');
        
        // 1. Tailwind CSS 설정
        if (!$this->option('skip-tailwind')) {
            $this->configureTailwindCSS();
        }
        
        // 2. 설정 파일 발행
        $this->publishConfig();
        
        // 3. NPM 종속성 설치 및 빌드
        $this->installNpmDependencies();
        
        // 4. 마이그레이션 실행 여부 확인
        if ($this->confirm('데이터베이스 마이그레이션을 실행하시겠습니까?')) {
            $this->call('migrate');
        }
        
        // 5. 관리자 계정 생성
        if ($this->confirm('관리자 계정을 생성하시겠습니까?')) {
            $this->call('admin:user-create');
        }
        
        $this->info('✅ Jiny Admin 패키지 설치가 완료되었습니다!');
        $this->info('');
        $this->info('다음 단계:');
        $this->info('1. php artisan serve 로 서버 시작');
        $this->info('2. http://localhost:8000/admin 접속');
        
        return Command::SUCCESS;
    }
    
    /**
     * Tailwind CSS 설정 업데이트
     */
    protected function configureTailwindCSS()
    {
        $this->info('📦 Tailwind CSS 설정을 업데이트합니다...');
        
        $appCssPath = resource_path('css/app.css');
        
        // Tailwind v4 사용 여부 확인
        if (File::exists($appCssPath)) {
            $content = File::get($appCssPath);
            
            // Tailwind v4 (@source 사용)
            if (str_contains($content, '@source')) {
                $this->configureTailwindV4($appCssPath, $content);
            }
            // Tailwind v3 (tailwind.config.js 사용)
            elseif (File::exists(base_path('tailwind.config.js'))) {
                $this->configureTailwindV3();
            }
            else {
                $this->warn('⚠️ Tailwind CSS 설정을 감지할 수 없습니다.');
                $this->info('수동으로 다음 경로를 Tailwind 설정에 추가해주세요:');
                $this->info('- vendor/jinyerp/**/*.blade.php');
                $this->info('- vendor/jiny/**/*.blade.php');
            }
        }
    }
    
    /**
     * Tailwind CSS v4 설정 (app.css에 @source 추가)
     */
    protected function configureTailwindV4($path, $content)
    {
        $sources = [
            "@source '../../vendor/jinyerp/**/*.blade.php';",
            "@source '../../vendor/jinyerp/**/*.php';",
            "@source '../../vendor/jiny/**/*.blade.php';",
            "@source '../../vendor/jiny/**/*.php';",
        ];
        
        $needsUpdate = false;
        $linesToAdd = [];
        
        foreach ($sources as $source) {
            if (!str_contains($content, $source)) {
                $needsUpdate = true;
                $linesToAdd[] = $source;
            }
        }
        
        if ($needsUpdate) {
            // 백업 생성
            File::copy($path, $path . '.backup');
            $this->info('✅ app.css 백업 생성: ' . $path . '.backup');
            
            // @theme 앞에 source 추가
            if (str_contains($content, '@theme')) {
                $content = str_replace('@theme', implode("\n", $linesToAdd) . "\n\n@theme", $content);
            }
            // 파일 끝에 추가
            else {
                $content .= "\n" . implode("\n", $linesToAdd) . "\n";
            }
            
            File::put($path, $content);
            $this->info('✅ Tailwind CSS v4 설정이 업데이트되었습니다.');
        } else {
            $this->info('✅ Tailwind CSS v4 설정이 이미 최신 상태입니다.');
        }
    }
    
    /**
     * Tailwind CSS v3 설정 (tailwind.config.js 수정)
     */
    protected function configureTailwindV3()
    {
        $configPath = base_path('tailwind.config.js');
        $config = File::get($configPath);
        
        $paths = [
            "'./vendor/jinyerp/**/*.blade.php'",
            "'./vendor/jiny/**/*.blade.php'",
        ];
        
        $needsUpdate = false;
        foreach ($paths as $path) {
            if (!str_contains($config, $path)) {
                $needsUpdate = true;
            }
        }
        
        if ($needsUpdate) {
            // 백업 생성
            File::copy($configPath, $configPath . '.backup');
            $this->info('✅ tailwind.config.js 백업 생성');
            
            // content 배열에 경로 추가
            $config = preg_replace(
                '/content:\s*\[([^\]]*)\]/s',
                "content: [$1,\n        './vendor/jinyerp/**/*.blade.php',\n        './vendor/jiny/**/*.blade.php'\n    ]",
                $config
            );
            
            File::put($configPath, $config);
            $this->info('✅ Tailwind CSS v3 설정이 업데이트되었습니다.');
        } else {
            $this->info('✅ Tailwind CSS v3 설정이 이미 최신 상태입니다.');
        }
    }
    
    /**
     * NPM 종속성 설치 및 빌드 실행
     */
    protected function installNpmDependencies()
    {
        $this->info('📦 NPM 종속성을 설치하고 빌드합니다...');
        
        // package.json 파일이 있는지 확인
        if (!File::exists(base_path('package.json'))) {
            $this->warn('⚠️ package.json 파일을 찾을 수 없습니다.');
            $this->info('수동으로 npm install && npm run build를 실행해주세요.');
            return;
        }
        
        // npm 또는 yarn이 설치되어 있는지 확인
        $npmInstalled = shell_exec('which npm') !== null;
        $yarnInstalled = shell_exec('which yarn') !== null;
        
        if (!$npmInstalled && !$yarnInstalled) {
            $this->warn('⚠️ npm 또는 yarn이 설치되어 있지 않습니다.');
            $this->info('Node.js를 설치한 후 npm install && npm run build를 실행해주세요.');
            return;
        }
        
        $useYarn = $yarnInstalled && File::exists(base_path('yarn.lock'));
        
        // 종속성 설치
        $this->info('📥 종속성 설치 중...');
        if ($useYarn) {
            $installCommand = 'yarn install';
        } else {
            $installCommand = 'npm install';
        }
        
        $process = proc_open(
            $installCommand,
            [
                0 => STDIN,
                1 => STDOUT,
                2 => STDERR,
            ],
            $pipes,
            base_path()
        );
        
        $exitCode = proc_close($process);
        
        if ($exitCode !== 0) {
            $this->error('❌ 종속성 설치에 실패했습니다.');
            $this->info('수동으로 ' . $installCommand . '을 실행해주세요.');
            return;
        }
        
        $this->info('✅ 종속성 설치 완료');
        
        // Vite 빌드 실행
        $this->info('🔨 Vite 빌드 실행 중...');
        if ($useYarn) {
            $buildCommand = 'yarn build';
        } else {
            $buildCommand = 'npm run build';
        }
        
        $process = proc_open(
            $buildCommand,
            [
                0 => STDIN,
                1 => STDOUT,
                2 => STDERR,
            ],
            $pipes,
            base_path()
        );
        
        $exitCode = proc_close($process);
        
        if ($exitCode !== 0) {
            $this->error('❌ 빌드에 실패했습니다.');
            $this->info('수동으로 ' . $buildCommand . '를 실행해주세요.');
            return;
        }
        
        $this->info('✅ Vite 빌드 완료');
    }
    
    /**
     * 설정 파일 발행
     */
    protected function publishConfig()
    {
        $this->info('📋 설정 파일을 발행합니다...');
        
        if (!$this->option('force') && File::exists(config_path('admin/setting.php'))) {
            if (!$this->confirm('설정 파일이 이미 존재합니다. 덮어쓰시겠습니까?')) {
                return;
            }
        }
        
        $this->call('vendor:publish', [
            '--tag' => 'jiny-admin-config',
            '--force' => $this->option('force'),
        ]);
    }
}