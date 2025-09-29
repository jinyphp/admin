<?php

namespace Jiny\Admin\Console\Commands;

use Jiny\Admin\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Jiny\Admin\Models\AdminUserLog;
use Jiny\Admin\Models\AdminUsertype;
use Illuminate\Support\Facades\DB;

class AdminUserCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:user-create 
                            {--email= : 관리자 이메일 주소}
                            {--name= : 관리자 이름}
                            {--password= : 비밀번호 (입력하지 않으면 프롬프트 표시)}
                            {--type= : 관리자 타입 (super, admin, manager 등)}
                            {--random-password : 랜덤 비밀번호 생성}
                            {--show-password : 생성된 비밀번호 표시}
                            {--force : 확인 없이 생성}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '새로운 관리자 계정을 생성합니다';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== 관리자 계정 생성 ===');
        $this->newLine();
        
        // 제공된 옵션 표시
        $this->displayProvidedOptions();
        
        // 입력값 수집
        $data = $this->collectData();
        
        if (!$data) {
            $this->error('관리자 생성이 취소되었습니다.');
            return 1;
        }
        
        // 중복 확인
        if ($this->checkDuplicateEmail($data['email'])) {
            return 1;
        }
        
        // 관리자 타입 확인
        if (!$this->validateAdminType($data['type'])) {
            return 1;
        }
        
        // 확인
        if (!$this->option('force')) {
            $this->displaySummary($data);
            if (!$this->confirm('위 정보로 관리자를 생성하시겠습니까?')) {
                $this->warn('관리자 생성이 취소되었습니다.');
                return 0;
            }
        }
        
        // 관리자 생성
        try {
            DB::beginTransaction();
            
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'isAdmin' => true,
                'utype' => $data['type'],
                'password_changed_at' => now(),
            ]);
            
            // 비밀번호 만료일 설정
            $expiryDays = config('admin.setting.password.expiry_days', 0);
            if ($expiryDays > 0) {
                $user->password_expires_at = now()->addDays($expiryDays);
                $user->password_expiry_days = $expiryDays;
                $user->save();
            }
            
            // 로그 기록
            AdminUserLog::log('admin_created_console', $user, [
                'created_by' => 'console',
                'command' => 'admin:create',
                'executor' => get_current_user(),
                'admin_type' => $data['type'],
                'timestamp' => now()->toDateTimeString(),
            ]);
            
            // 사용자 타입 카운트 업데이트
            AdminUsertype::where('code', $data['type'])->increment('cnt');
            
            DB::commit();
            
            $this->info('✓ 관리자가 성공적으로 생성되었습니다!');
            $this->displayCreatedAdmin($user, $data);
            
            return 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('관리자 생성 중 오류가 발생했습니다: ' . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * 입력 데이터 수집
     */
    private function collectData()
    {
        $data = [];
        
        // 이메일
        $data['email'] = $this->option('email');
        if (!$data['email']) {
            $data['email'] = $this->ask('이메일 주소를 입력하세요');
        }
        
        // 이메일 유효성 검증
        $validator = Validator::make(['email' => $data['email']], [
            'email' => 'required|email',
        ]);
        
        if ($validator->fails()) {
            $this->error('유효한 이메일 주소를 입력해주세요.');
            return null;
        }
        
        // 이름
        $data['name'] = $this->option('name');
        if (!$data['name']) {
            $data['name'] = $this->ask('관리자 이름을 입력하세요');
        }
        
        // 관리자 타입
        $data['type'] = $this->option('type');
        if (!$data['type']) {
            $this->displayAdminTypes();
            $data['type'] = $this->ask('관리자 타입을 선택하세요 (코드 입력)');
        }
        
        // 비밀번호
        if ($this->option('random-password')) {
            $data['password'] = $this->generateRandomPassword();
            $this->newLine();
            $this->info('✓ 안전한 랜덤 비밀번호가 생성되었습니다.');
            $this->comment('  생성된 비밀번호는 모든 보안 요구사항을 만족합니다.');
            if ($this->option('show-password')) {
                $this->warn("  생성된 비밀번호: {$data['password']}");
            }
        } else {
            $data['password'] = $this->option('password');
            if (!$data['password']) {
                $data['password'] = $this->getPasswordFromPrompt();
                if (!$data['password']) {
                    return null;
                }
            } else {
                // 제공된 비밀번호 검증
                if (!$this->validatePassword($data['password'])) {
                    return null;
                }
            }
        }
        
        return $data;
    }
    
    /**
     * 관리자 타입 목록 표시
     */
    private function displayAdminTypes()
    {
        $types = AdminUsertype::where('enable', true)
            ->orderBy('level', 'desc')
            ->get();
        
        if ($types->isEmpty()) {
            $this->warn('등록된 관리자 타입이 없습니다. 기본 타입을 생성합니다...');
            $this->createDefaultAdminTypes();
            $types = AdminUsertype::where('enable', true)->get();
        }
        
        $this->info('사용 가능한 관리자 타입:');
        $this->table(
            ['코드', '이름', '설명', '레벨', '사용자 수'],
            $types->map(function ($type) {
                return [
                    $type->code,
                    $type->name,
                    $type->description ?? '-',
                    $type->level,
                    $type->cnt,
                ];
            })
        );
    }
    
    /**
     * 기본 관리자 타입 생성
     */
    private function createDefaultAdminTypes()
    {
        $defaultTypes = [
            [
                'code' => 'super',
                'name' => 'Super Admin',
                'description' => '최고 관리자 - 모든 권한',
                'level' => 100,
                'enable' => true,
            ],
            [
                'code' => 'admin',
                'name' => 'Admin',
                'description' => '일반 관리자 - 대부분의 권한',
                'level' => 80,
                'enable' => true,
            ],
            [
                'code' => 'manager',
                'name' => 'Manager',
                'description' => '매니저 - 제한된 관리 권한',
                'level' => 60,
                'enable' => true,
            ],
        ];
        
        foreach ($defaultTypes as $type) {
            AdminUsertype::firstOrCreate(
                ['code' => $type['code']],
                $type
            );
        }
    }
    
    /**
     * 프롬프트로 비밀번호 입력받기
     */
    private function getPasswordFromPrompt()
    {
        // 처음에 패스워드 규칙 안내
        $this->newLine();
        $this->info('=== 비밀번호 생성 규칙 ===');
        $this->displayPasswordRequirements();
        $this->displayPasswordExamples();
        $this->newLine();
        
        $attempts = 0;
        while ($attempts < 3) {
            $password = $this->secret('비밀번호를 입력하세요');
            $passwordConfirm = $this->secret('비밀번호를 다시 입력하세요');
            
            if ($password !== $passwordConfirm) {
                $this->error('비밀번호가 일치하지 않습니다.');
                $attempts++;
                continue;
            }
            
            if ($this->validatePassword($password)) {
                return $password;
            }
            
            $attempts++;
            if ($attempts < 3) {
                $this->warn('비밀번호가 규칙을 만족하지 않습니다. 다시 입력해주세요.');
                $this->displayPasswordRequirements();
            }
        }
        
        $this->error('비밀번호 입력 시도 횟수를 초과했습니다.');
        return null;
    }
    
    /**
     * 비밀번호 유효성 검증
     */
    private function validatePassword($password)
    {
        $rules = [];
        $messages = [];
        
        // 최소 길이
        $minLength = config('admin.setting.password.min_length', 8);
        $rules[] = 'min:' . $minLength;
        
        // 최대 길이
        $maxLength = config('admin.setting.password.max_length', 128);
        $rules[] = 'max:' . $maxLength;
        
        // 정규식 규칙 생성
        $regex = '';
        if (config('admin.setting.password.require_uppercase', true)) {
            $regex .= '(?=.*[A-Z])';
        }
        if (config('admin.setting.password.require_lowercase', true)) {
            $regex .= '(?=.*[a-z])';
        }
        if (config('admin.setting.password.require_numbers', true)) {
            $regex .= '(?=.*[0-9])';
        }
        if (config('admin.setting.password.require_special_chars', true)) {
            $specialChars = preg_quote(config('admin.setting.password.allowed_special_chars', '!@#$%^&*()'), '/');
            $regex .= "(?=.*[{$specialChars}])";
        }
        
        if ($regex) {
            $rules[] = 'regex:/^' . $regex . '.*/';
        }
        
        $validator = Validator::make(
            ['password' => $password],
            ['password' => $rules],
            $messages
        );
        
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return false;
        }
        
        return true;
    }
    
    /**
     * 랜덤 비밀번호 생성
     */
    private function generateRandomPassword()
    {
        $length = config('admin.setting.password.generator.default_length', 16);
        $chars = '';
        
        if (config('admin.setting.password.generator.include_lowercase', true)) {
            $chars .= 'abcdefghijklmnopqrstuvwxyz';
        }
        if (config('admin.setting.password.generator.include_uppercase', true)) {
            $chars .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        if (config('admin.setting.password.generator.include_numbers', true)) {
            $chars .= '0123456789';
        }
        if (config('admin.setting.password.generator.include_special', true)) {
            $chars .= config('admin.setting.password.allowed_special_chars', '!@#$%^&*()');
        }
        
        // 혼동하기 쉬운 문자 제외
        if (config('admin.setting.password.generator.exclude_ambiguous', true)) {
            $ambiguous = config('admin.setting.password.generator.ambiguous_chars', '0O1lI');
            $chars = str_replace(str_split($ambiguous), '', $chars);
        }
        
        $password = '';
        
        // 각 유형별로 최소 1개씩 포함
        if (config('admin.setting.password.require_uppercase', true)) {
            $upperChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $password .= $upperChars[random_int(0, strlen($upperChars) - 1)];
        }
        if (config('admin.setting.password.require_lowercase', true)) {
            $lowerChars = 'abcdefghijklmnopqrstuvwxyz';
            $password .= $lowerChars[random_int(0, strlen($lowerChars) - 1)];
        }
        if (config('admin.setting.password.require_numbers', true)) {
            $numberChars = '0123456789';
            $password .= $numberChars[random_int(0, strlen($numberChars) - 1)];
        }
        if (config('admin.setting.password.require_special_chars', true)) {
            $specialChars = config('admin.setting.password.allowed_special_chars', '!@#$%^&*()');
            $password .= $specialChars[random_int(0, strlen($specialChars) - 1)];
        }
        
        // 나머지 문자 채우기
        $charsLength = strlen($chars);
        for ($i = strlen($password); $i < $length; $i++) {
            $password .= $chars[random_int(0, $charsLength - 1)];
        }
        
        // 문자 섞기
        return str_shuffle($password);
    }
    
    /**
     * 비밀번호 요구사항 표시
     */
    private function displayPasswordRequirements()
    {
        $requirements = [];
        
        $minLength = config('admin.setting.password.min_length', 8);
        $maxLength = config('admin.setting.password.max_length', 128);
        $requirements[] = ['길이', "{$minLength}자 ~ {$maxLength}자"];
        
        if (config('admin.setting.password.require_uppercase', true)) {
            $requirements[] = ['대문자', '최소 1개 포함 (A-Z)'];
        }
        if (config('admin.setting.password.require_lowercase', true)) {
            $requirements[] = ['소문자', '최소 1개 포함 (a-z)'];
        }
        if (config('admin.setting.password.require_numbers', true)) {
            $requirements[] = ['숫자', '최소 1개 포함 (0-9)'];
        }
        if (config('admin.setting.password.require_special_chars', true)) {
            $specialChars = config('admin.setting.password.allowed_special_chars', '!@#$%^&*()_+-=[]{}|;:,.<>?');
            $requirements[] = ['특수문자', "최소 1개 포함 ({$specialChars})"];
        }
        if (!config('admin.setting.password.allow_spaces', false)) {
            $requirements[] = ['공백', '사용 불가'];
        }
        
        $expiryDays = config('admin.setting.password.expiry_days', 0);
        if ($expiryDays > 0) {
            $requirements[] = ['유효기간', "{$expiryDays}일 후 변경 필요"];
        }
        
        $this->table(['요구사항', '설명'], $requirements);
    }
    
    /**
     * 비밀번호 예제 표시
     */
    private function displayPasswordExamples()
    {
        $this->newLine();
        $this->info('올바른 비밀번호 예제:');
        
        $examples = [
            'Admin@2024Pass',
            'SecureP@ss123!',
            'MyStr0ng#Password',
        ];
        
        foreach ($examples as $example) {
            $this->line("  ✓ {$example}");
        }
        
        $this->newLine();
        $this->warn('잘못된 비밀번호 예제:');
        
        $badExamples = [
            'password123' => '특수문자와 대문자 없음',
            'PASSWORD@123' => '소문자 없음',
            'Admin@Pass' => '숫자 없음',
            'admin123' => '대문자와 특수문자 없음',
        ];
        
        foreach ($badExamples as $example => $reason) {
            $this->line("  ✗ {$example} ({$reason})");
        }
    }
    
    /**
     * 이메일 중복 확인
     */
    private function checkDuplicateEmail($email)
    {
        $existing = User::where('email', $email)->first();
        
        if ($existing) {
            $this->error("이미 존재하는 이메일입니다: {$email}");
            
            if ($existing->isAdmin) {
                $this->info('기존 관리자 정보:');
                $this->table(
                    ['항목', '값'],
                    [
                        ['이름', $existing->name],
                        ['타입', $existing->utype ?? 'N/A'],
                        ['생성일', $existing->created_at->format('Y-m-d H:i:s')],
                    ]
                );
            } else {
                $this->warn('해당 이메일은 일반 사용자 계정입니다.');
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * 관리자 타입 유효성 확인
     */
    private function validateAdminType($type)
    {
        $adminType = AdminUsertype::where('code', $type)
            ->where('enable', true)
            ->first();
        
        if (!$adminType) {
            $this->error("유효하지 않은 관리자 타입입니다: {$type}");
            $this->displayAdminTypes();
            return false;
        }
        
        return true;
    }
    
    /**
     * 생성 요약 표시
     */
    private function displaySummary($data)
    {
        $adminType = AdminUsertype::where('code', $data['type'])->first();
        
        $this->info('생성할 관리자 정보:');
        $this->table(
            ['항목', '값'],
            [
                ['이메일', $data['email']],
                ['이름', $data['name']],
                ['타입', $adminType ? "{$adminType->name} ({$data['type']})" : $data['type']],
                ['레벨', $adminType ? $adminType->level : 'N/A'],
            ]
        );
    }
    
    /**
     * 생성된 관리자 정보 표시
     */
    private function displayCreatedAdmin($user, $data)
    {
        $this->newLine();
        $this->table(
            ['항목', '값'],
            [
                ['ID', $user->id],
                ['이메일', $user->email],
                ['이름', $user->name],
                ['관리자 타입', $user->utype],
                ['생성일시', $user->created_at->format('Y-m-d H:i:s')],
                ['비밀번호 만료일', $user->password_expires_at ? $user->password_expires_at->format('Y-m-d') : '없음'],
            ]
        );
        
        if ($this->option('show-password')) {
            $this->newLine();
            $this->warn("비밀번호: {$data['password']}");
            $this->warn("보안을 위해 이 비밀번호를 안전한 곳에 기록한 후 화면을 지우세요.");
        }
    }
    
    /**
     * 제공된 옵션 표시
     */
    private function displayProvidedOptions()
    {
        $providedOptions = [];
        
        if ($this->option('email')) {
            $providedOptions[] = ['이메일', $this->option('email'), '✓'];
        }
        if ($this->option('name')) {
            $providedOptions[] = ['이름', $this->option('name'), '✓'];
        }
        if ($this->option('type')) {
            $providedOptions[] = ['타입', $this->option('type'), '✓'];
        }
        if ($this->option('password')) {
            $providedOptions[] = ['비밀번호', '********', '✓'];
        } elseif ($this->option('random-password')) {
            $providedOptions[] = ['비밀번호', '랜덤 생성 예정', '✓'];
        }
        
        if (!empty($providedOptions)) {
            $this->info('제공된 옵션:');
            $this->table(['항목', '값', '상태'], $providedOptions);
            
            // 추가로 입력받을 항목 표시
            $missingItems = [];
            if (!$this->option('email')) {
                $missingItems[] = '이메일';
            }
            if (!$this->option('name')) {
                $missingItems[] = '이름';
            }
            if (!$this->option('type')) {
                $missingItems[] = '관리자 타입';
            }
            if (!$this->option('password') && !$this->option('random-password')) {
                $missingItems[] = '비밀번호';
            }
            
            if (!empty($missingItems)) {
                $this->comment('추가로 입력이 필요한 항목: ' . implode(', ', $missingItems));
            }
            
            $this->newLine();
        } else {
            $this->comment('제공된 옵션이 없습니다. 모든 정보를 대화형으로 입력받습니다.');
            $this->newLine();
        }
    }
}