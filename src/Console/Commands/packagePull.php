<?php

namespace Jiny\Admin\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use CzProject\GitPhp\Git;

class packagePull extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jiny:pull {name?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'jiny package pull';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $path = base_path('vendor/jiny');
        $this->info($path);

        $name = $this->argument('name');
        if($name) {
            $repoPath = $path.DIRECTORY_SEPARATOR.$name;
            if(is_dir($repoPath.DIRECTORY_SEPARATOR.".git")) {
                $this->info("package name: ".$name." ==> pull");
                $this->gitPull($repoPath);
            } else {
                $this->info("package name: ".$name);
            }
        } else {
            // 전체 pull
            $this->allPull($path);
        }

        return 0;
    }


    private function allPull($path)
    {
        $packages = scandir($path);
        foreach($packages as $item) {
            if($item == '.' || $item == '..') continue;
            if($item[0] == '_') continue;
            $repoPath = $path.DIRECTORY_SEPARATOR.$item;
            if(is_dir($repoPath.DIRECTORY_SEPARATOR.".git")) {
                $this->info("package name: ".$item." ==> pull");
                $this->gitPull($repoPath);

            } else {
                $this->info("package name: ".$item);
            }
        }
    }


    private function gitPull($path)
    {
        try {
            $git = new Git;
            $repo = $git->open($path);
            // $output = $repo->pull('origin');

            // Git pull 실행
            $output = $repo->execute('pull', ['origin']);

            // 결과를 콘솔에 출력
            $this->info(implode("\n", $output));
            $this->info("\n");

        } catch (Exception $e) {
            // 예외 발생 시 에러 메시지 출력
            echo '에러 발생: ',  $e->getMessage(), "\n";
        }

    }



}
