<?php
namespace Jiny\Admin\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class WireAdminSetupUser extends Component
{
    public $forms=[];

    public function mount()
    {

    }

    public function render()
    {
        // Wrap the database connection attempt in a try-catch block
        try {
            // Attempt to establish a database connection
            $pdo = DB::connection()->getPdo();

            // If the connection is successful, PDO instance will be available
            if ($pdo !== null) {
                // echo "Database connection is currently established.";
            } else {
                // echo "Database connection is not established.";
            }
        } catch (\PDOException $e) {
            // If an exception is thrown, it means the connection failed
            // echo "Database connection is not established. Error: " . $e->getMessage();
            $pdo = null;
        }

        if($pdo) {
            $users = DB::table('users_super')->get();

            return view("jiny-admin::setup.user",[
                'user_count'=>count($users)
            ]);
        }

        return <<<'blade'
        <div>Super 사용자 미설정</div>
    blade;

    }

    public function submit()
    {
        // 회원 등록
        $user = User::create([
            'name' => $this->forms['name'],
            'email' => $this->forms['email'],
            'password' => Hash::make($this->forms['password']),
        ]);

        // Access the ID of the newly created user
        $userId = $user->id;
        DB::table('users_super')->insert([
            'user_id' => $userId,
            'name' => $this->forms['name'],
            'email' => $this->forms['email'],
            'created_at' => date("Y-m-d"),
            'updated_at' => date("Y-m-d")
        ]);

    }

}
