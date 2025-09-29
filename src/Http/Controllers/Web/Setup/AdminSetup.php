<?php

namespace Jiny\Admin\Http\Controllers\Web\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Jiny\Admin\Models\User;

class AdminSetup extends Controller
{
    protected $steps = [
        'requirements',
        'database',
        'admin',
        'complete'
    ];

    public function index(Request $request)
    {
        if ($this->isSetupComplete()) {
            return redirect('/admin/login');
        }

        // Query parameter로 step이 전달된 경우 처리
        if ($request->has('step') && $request->get('step') === 'complete') {
            $request->session()->put('setup_step', 'complete');
            $step = 'complete';
        } else {
            // DB 상태를 체크하여 현재 단계를 결정
            $step = $this->determineCurrentStep($request);
        }
        
        // 세션에 현재 단계 저장
        $request->session()->put('setup_step', $step);

        return view('jiny-admin::web.setup.index', [
            'currentStep' => $step,
            'steps' => $this->steps,
            'stepNumber' => array_search($step, $this->steps) + 1,
            'totalSteps' => count($this->steps)
        ]);
    }
    
    /**
     * DB 상태를 기반으로 현재 설정 단계 결정
     */
    protected function determineCurrentStep(Request $request)
    {
        // 세션에서 현재 단계 확인
        $sessionStep = $request->session()->get('setup_step');
        
        try {
            // DB 연결 확인
            DB::connection()->getPdo();
            
            // 테이블 존재 확인
            $hasUsersTable = Schema::hasTable('users');
            
            // 관리자 존재 확인
            $hasAdmin = false;
            if ($hasUsersTable) {
                $hasAdmin = User::exists();
            }
            
            // 현재 진행 상황에 따라 최대 가능한 단계 결정
            if (!$hasUsersTable) {
                // 테이블이 없으면 최대 database 단계까지
                $maxStep = 'database';
            } elseif (!$hasAdmin) {
                // 관리자가 없으면 최대 admin 단계까지
                $maxStep = 'admin';
            } else {
                // 모든 설정이 완료된 경우
                $maxStep = 'complete';
            }
            
            // 세션에 저장된 단계가 있고, 최대 가능 단계보다 앞선 경우 세션 값 사용
            if ($sessionStep) {
                $sessionIndex = array_search($sessionStep, $this->steps);
                $maxIndex = array_search($maxStep, $this->steps);
                
                if ($sessionIndex !== false && $sessionIndex <= $maxIndex) {
                    return $sessionStep;
                }
            }
            
            // 그렇지 않으면 최대 가능 단계 반환
            return $maxStep;
            
        } catch (\Exception $e) {
            // DB 연결 실패시 requirements부터 시작
            if ($sessionStep && $sessionStep === 'requirements') {
                return 'requirements';
            }
            return 'requirements';
        }
    }

    public function checkRequirements()
    {
        $requirements = [
            'php_version' => [
                'name' => 'PHP Version',
                'required' => '8.2.0',
                'current' => PHP_VERSION,
                'satisfied' => version_compare(PHP_VERSION, '8.2.0', '>='),
                'message' => 'PHP 8.2 이상이 필요합니다.'
            ],
            'pdo' => [
                'name' => 'PDO Extension',
                'required' => '설치됨',
                'current' => extension_loaded('pdo') ? '설치됨' : '미설치',
                'satisfied' => extension_loaded('pdo'),
                'message' => 'PDO 확장이 필요합니다.'
            ],
            'pdo_sqlite' => [
                'name' => 'PDO SQLite',
                'required' => '설치됨',
                'current' => extension_loaded('pdo_sqlite') ? '설치됨' : '미설치',
                'satisfied' => extension_loaded('pdo_sqlite'),
                'message' => 'PDO SQLite 드라이버가 필요합니다.'
            ],
            'mbstring' => [
                'name' => 'Mbstring Extension',
                'required' => '설치됨',
                'current' => extension_loaded('mbstring') ? '설치됨' : '미설치',
                'satisfied' => extension_loaded('mbstring'),
                'message' => 'Mbstring 확장이 필요합니다.'
            ],
            'openssl' => [
                'name' => 'OpenSSL Extension',
                'required' => '설치됨',
                'current' => extension_loaded('openssl') ? '설치됨' : '미설치',
                'satisfied' => extension_loaded('openssl'),
                'message' => 'OpenSSL 확장이 필요합니다.'
            ],
            'tokenizer' => [
                'name' => 'Tokenizer Extension',
                'required' => '설치됨',
                'current' => extension_loaded('tokenizer') ? '설치됨' : '미설치',
                'satisfied' => extension_loaded('tokenizer'),
                'message' => 'Tokenizer 확장이 필요합니다.'
            ],
            'xml' => [
                'name' => 'XML Extension',
                'required' => '설치됨',
                'current' => extension_loaded('xml') ? '설치됨' : '미설치',
                'satisfied' => extension_loaded('xml'),
                'message' => 'XML 확장이 필요합니다.'
            ],
            'ctype' => [
                'name' => 'Ctype Extension',
                'required' => '설치됨',
                'current' => extension_loaded('ctype') ? '설치됨' : '미설치',
                'satisfied' => extension_loaded('ctype'),
                'message' => 'Ctype 확장이 필요합니다.'
            ],
            'json' => [
                'name' => 'JSON Extension',
                'required' => '설치됨',
                'current' => extension_loaded('json') ? '설치됨' : '미설치',
                'satisfied' => extension_loaded('json'),
                'message' => 'JSON 확장이 필요합니다.'
            ],
            'bcmath' => [
                'name' => 'BCMath Extension',
                'required' => '설치됨',
                'current' => extension_loaded('bcmath') ? '설치됨' : '미설치',
                'satisfied' => extension_loaded('bcmath'),
                'message' => 'BCMath 확장이 필요합니다.'
            ],
            'fileinfo' => [
                'name' => 'Fileinfo Extension',
                'required' => '설치됨',
                'current' => extension_loaded('fileinfo') ? '설치됨' : '미설치',
                'satisfied' => extension_loaded('fileinfo'),
                'message' => 'Fileinfo 확장이 필요합니다.'
            ],
            'storage_writable' => [
                'name' => 'Storage Directory',
                'required' => '쓰기 가능',
                'current' => is_writable(storage_path()) ? '쓰기 가능' : '쓰기 불가',
                'satisfied' => is_writable(storage_path()),
                'message' => 'storage 디렉토리에 쓰기 권한이 필요합니다.'
            ],
            'cache_writable' => [
                'name' => 'Cache Directory',
                'required' => '쓰기 가능',
                'current' => is_writable(storage_path('framework/cache')) ? '쓰기 가능' : '쓰기 불가',
                'satisfied' => is_writable(storage_path('framework/cache')),
                'message' => 'cache 디렉토리에 쓰기 권한이 필요합니다.'
            ]
        ];

        $allSatisfied = true;
        foreach ($requirements as $req) {
            if (!$req['satisfied']) {
                $allSatisfied = false;
                break;
            }
        }

        return response()->json([
            'requirements' => $requirements,
            'allSatisfied' => $allSatisfied
        ]);
    }

    public function checkDatabase()
    {
        try {
            $connection = DB::connection();
            $connection->getPdo();
            
            // 데이터베이스 정보 가져오기
            $config = config('database.connections.' . config('database.default'));
            $driver = $config['driver'] ?? 'unknown';
            $database = $config['database'] ?? 'N/A';
            $host = $config['host'] ?? 'N/A';
            $port = $config['port'] ?? 'N/A';
            
            // SQLite의 경우 파일 경로만 표시
            if ($driver === 'sqlite') {
                $connectionInfo = [
                    'driver' => 'SQLite',
                    'database' => basename($database),
                    'path' => $database
                ];
            } else {
                $connectionInfo = [
                    'driver' => strtoupper($driver),
                    'host' => $host,
                    'port' => $port,
                    'database' => $database
                ];
            }
            
            // 테이블 존재 여부 확인
            $tablesExist = Schema::hasTable('users');
            
            // 전체 테이블 수 확인
            $tables = [];
            if ($driver === 'sqlite') {
                $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            } elseif ($driver === 'mysql') {
                $tables = DB::select('SHOW TABLES');
            } elseif ($driver === 'pgsql') {
                $tables = DB::select("SELECT tablename FROM pg_tables WHERE schemaname='public'");
            }
            
            $tableCount = count($tables);
            
            return response()->json([
                'connected' => true,
                'connectionInfo' => $connectionInfo,
                'tablesExist' => $tablesExist,
                'tableCount' => $tableCount,
                'message' => $tablesExist ? '데이터베이스가 준비되었습니다.' : '마이그레이션이 필요합니다.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'connected' => false,
                'connectionInfo' => null,
                'tablesExist' => false,
                'tableCount' => 0,
                'message' => '데이터베이스 연결 실패: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkPendingMigrations()
    {
        try {
            // 미적용 마이그레이션 확인
            Artisan::call('migrate:status');
            $output = Artisan::output();
            
            // 출력 파싱하여 미적용 마이그레이션 찾기
            $lines = explode("\n", $output);
            $pendingMigrations = [];
            $totalMigrations = 0;
            $ranMigrations = 0;
            
            foreach ($lines as $line) {
                // 마이그레이션 상태 라인 파싱
                if (preg_match('/\s+(Yes|No|Pending)\s+(.+)/', $line, $matches)) {
                    $status = $matches[1];
                    $migration = trim($matches[2]);
                    $totalMigrations++;
                    
                    if ($status === 'Yes') {
                        $ranMigrations++;
                    } else if ($status === 'No' || $status === 'Pending') {
                        $pendingMigrations[] = $migration;
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'totalMigrations' => $totalMigrations,
                'ranMigrations' => $ranMigrations,
                'pendingCount' => count($pendingMigrations),
                'pendingMigrations' => $pendingMigrations,
                'hasPending' => count($pendingMigrations) > 0,
                'output' => $output
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '마이그레이션 상태 확인 실패: ' . $e->getMessage()
            ], 500);
        }
    }

    public function runMigrations()
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            
            return response()->json([
                'success' => true,
                'message' => '마이그레이션이 완료되었습니다.',
                'output' => Artisan::output()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '마이그레이션 실패: ' . $e->getMessage()
            ], 500);
        }
    }

    public function createAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed'
        ]);

        try {
            // 기본 관리자 타입 생성 (존재하지 않는 경우)
            $this->createDefaultAdminTypes();
            
            // super 타입 가져오기 (최고 권한)
            $superType = DB::table('admin_user_types')
                ->where('code', 'super')
                ->first();
            
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'isAdmin' => true,  // is_admin 대신 isAdmin 사용 (컬럼명 확인)
                'utype' => $superType ? $superType->code : 'super',  // super 관리자 타입 할당
                'email_verified_at' => now(),
                'last_login_at' => null,
                'login_count' => 0,
                'password_changed_at' => now()
            ]);
            
            // super 타입의 사용자 수(cnt) 증가
            if ($superType) {
                DB::table('admin_user_types')
                    ->where('code', 'super')
                    ->increment('cnt');
            }
            
            // 자동 로그인 방지 - 명시적으로 로그아웃
            if (Auth::check()) {
                Auth::logout();
            }

            // complete 단계로 이동 - 세션 강제 저장
            $request->session()->put('setup_step', 'complete');
            $request->session()->save();
            
            return response()->json([
                'success' => true,
                'message' => '최고 관리자 계정이 생성되었습니다.',
                'redirect' => '/admin/setup'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '관리자 생성 실패: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 기본 관리자 타입 생성
     */
    protected function createDefaultAdminTypes()
    {
        $types = [
            [
                'code' => 'super',
                'name' => '슈퍼 관리자',
                'description' => '모든 권한을 가진 최고 관리자',
                'badge_color' => 'bg-red-100 text-red-800',
                'level' => 100,
                'pos' => 1,
                'enable' => true,
                'permissions' => json_encode(['*']),
                'settings' => json_encode([
                    'can_delete_super' => false,
                    'can_modify_permissions' => true,
                    'can_access_all_modules' => true
                ])
            ],
            [
                'code' => 'admin',
                'name' => '일반 관리자',
                'description' => '일반적인 관리 권한을 가진 관리자',
                'badge_color' => 'bg-blue-100 text-blue-800',
                'level' => 80,
                'pos' => 2,
                'enable' => true,
                'permissions' => json_encode([
                    'dashboard.view',
                    'users.manage',
                    'content.manage',
                    'settings.view'
                ]),
                'settings' => json_encode([
                    'can_delete_super' => false,
                    'can_modify_permissions' => false,
                    'can_access_all_modules' => false
                ])
            ],
            [
                'code' => 'staff',
                'name' => '스태프',
                'description' => '제한된 권한을 가진 직원',
                'badge_color' => 'bg-gray-100 text-gray-800',
                'level' => 50,
                'pos' => 3,
                'enable' => true,
                'permissions' => json_encode([
                    'dashboard.view',
                    'content.view',
                    'content.create',
                    'content.edit'
                ]),
                'settings' => json_encode([
                    'can_delete_super' => false,
                    'can_modify_permissions' => false,
                    'can_access_all_modules' => false
                ])
            ]
        ];
        
        foreach ($types as $type) {
            // 이미 존재하는지 확인
            $exists = DB::table('admin_user_types')
                ->where('code', $type['code'])
                ->exists();
            
            if (!$exists) {
                DB::table('admin_user_types')->insert(array_merge($type, [
                    'created_at' => now(),
                    'updated_at' => now()
                ]));
            }
        }
    }

    public function saveSettings(Request $request)
    {
        $settings = $request->validate([
            'site_name' => 'nullable|string|max:255',
            'site_description' => 'nullable|string',
            'admin_prefix' => 'nullable|string|max:50',
            'enable_2fa' => 'nullable|boolean',
            'enable_captcha' => 'nullable|boolean',
            'enable_ip_whitelist' => 'nullable|boolean',
            'session_lifetime' => 'nullable|integer|min:5|max:1440'
        ]);

        try {
            $configPath = base_path('jiny/admin/config/setting.php');
            $config = include $configPath;
            
            foreach ($settings as $key => $value) {
                if ($value !== null) {
                    $config[$key] = $value;
                }
            }
            
            $configContent = "<?php\n\nreturn " . var_export($config, true) . ";\n";
            file_put_contents($configPath, $configContent);
            
            Artisan::call('config:clear');
            
            return response()->json([
                'success' => true,
                'message' => '설정이 저장되었습니다.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '설정 저장 실패: ' . $e->getMessage()
            ], 500);
        }
    }

    public function nextStep(Request $request)
    {
        $currentStep = $request->session()->get('setup_step', 'requirements');
        $currentIndex = array_search($currentStep, $this->steps);
        
        if ($currentIndex !== false && $currentIndex < count($this->steps) - 1) {
            $nextStep = $this->steps[$currentIndex + 1];
            $request->session()->put('setup_step', $nextStep);
            
            return response()->json([
                'success' => true,
                'nextStep' => $nextStep
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => '다음 단계가 없습니다.'
        ], 400);
    }

    public function goToStep(Request $request)
    {
        $targetStep = $request->input('step');
        
        // 유효한 스텝인지 확인
        if (!in_array($targetStep, $this->steps)) {
            return response()->json([
                'success' => false,
                'message' => '유효하지 않은 단계입니다.'
            ], 400);
        }
        
        // 현재 실제 진행 상태 확인
        $actualCurrentStep = $this->determineCurrentStep($request);
        $actualCurrentIndex = array_search($actualCurrentStep, $this->steps);
        $targetIndex = array_search($targetStep, $this->steps);
        
        // 이미 완료된 단계로만 이동 가능 (뒤로가기)
        // 또는 현재 단계 이전으로 이동 가능
        if ($targetIndex <= $actualCurrentIndex) {
            $request->session()->put('setup_step', $targetStep);
            
            return response()->json([
                'success' => true,
                'step' => $targetStep
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => '아직 진행하지 않은 단계로는 이동할 수 없습니다.'
        ], 400);
    }

    public function completeSetup(Request $request)
    {
        try {
            // setup 관련 모든 세션 초기화
            $request->session()->forget('setup_step');
            $request->session()->forget('setup_completed');
            
            // 세션 저장
            $request->session()->save();
            
            return response()->json([
                'success' => true,
                'message' => '설정이 완료되었습니다. 로그인 페이지로 이동합니다.',
                'redirect' => '/admin/login'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '설정 완료 실패: ' . $e->getMessage()
            ], 500);
        }
    }

    protected function isSetupComplete()
    {
        // complete 단계가 아직 표시되지 않았다면 false 반환
        $currentStep = request()->session()->get('setup_step');
        if ($currentStep === 'complete') {
            return false; // complete 페이지를 보여줘야 함
        }
        
        // users 테이블에 사용자가 한 명이라도 있고, complete 단계를 이미 본 경우
        return User::exists() && !request()->session()->has('setup_step');
    }
}