<?php

namespace Jiny\Admin\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\DB;


class userAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:admin {email} {--disable} {--enable}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'change admin';

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
        $email = $this->argument('email');

        $isAdmin = $this->option('enable') ? 1:0;
        if($this->option('disable')) {
            $this->disableAdmin($email);
        } else {
            $this->enableAdmin($email);
        }



        if($isAdmin) {
            $this->info('Success : '. $email." is Admin user");
        } else {
            $this->info('Success : '. $email." is normal user");
        }

        return 0;
    }

    private function enableAdmin($email)
    {
        $user = DB::table('users')->where('email', $email)->first();

        // Admin 컬럼을 변경합니다.
        DB::table('users')->where('email',$email)->update([
            'isAdmin'=>1,
            'utype'=>"admin",
            'auth'=>1
        ]);

        // Admin 테이블에 row를 추가합니다.
        DB::table('users_admin')->insert([
            'user_id'=>$user->id,
            'enable' => 1,
            'name' => $user->name,
            'email'=>$user->email,

            'created_at'=> date("Y-m-d H:i:s"),
            'updated_at'=> date("Y-m-d H:i:s")
        ]);
    }

    private function disableAdmin($email)
    {
        DB::table('users')->where('email',$email)->update([
            'isAdmin'=>0
        ]);
    }


}
