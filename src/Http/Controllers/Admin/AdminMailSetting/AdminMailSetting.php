<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminMailSetting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Jiny\Admin\Mail\EmailMailable;

/**
 * AdminMailSetting Controller
 * 
 * 메일 설정 관리 및 테스트 메일 발송 기능을 제공합니다.
 */
class AdminMailSetting extends Controller
{
    private $route;

    public function __construct()
    {
        $this->route = 'admin.mail.setting';
    }

    /**
     * 메일 설정 페이지 표시
     */
    public function __invoke(Request $request)
    {
        // jiny/admin/config/mail.php 파일에서 직접 읽기
        $configPath = base_path('jiny/admin/config/mail.php');
        if (file_exists($configPath)) {
            $mailSettings = include $configPath;
        } else {
            // 파일이 없으면 기본 config 사용
            $mailSettings = config('admin.mail', [
                'mailer' => env('MAIL_MAILER', 'smtp'),
                'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
                'port' => env('MAIL_PORT', 587),
                'username' => env('MAIL_USERNAME', ''),
                'password' => env('MAIL_PASSWORD', ''),
                'encryption' => env('MAIL_ENCRYPTION', 'tls'),
                'from_address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
                'from_name' => env('MAIL_FROM_NAME', 'Example'),
            ]);
        }

        return view('jiny-admin::admin.mail.setting.index', [
            'mailSettings' => $mailSettings,
            'route' => $this->route,
            'title' => '메일 설정',
            'subtitle' => 'SMTP 메일 서버 설정을 관리합니다',
        ]);
    }

    /**
     * 메일 설정 저장
     */
    public function update(Request $request)
    {
        $request->validate([
            'mailer' => 'required|string',
            'host' => 'required_if:mailer,smtp|nullable|string',
            'port' => 'required_if:mailer,smtp|nullable|integer',
            'username' => 'nullable|string',
            'password' => 'nullable|string',
            'encryption' => 'nullable|string|in:tls,ssl,null',
            'from_address' => 'required|email',
            'from_name' => 'required|string',
        ]);

        $data = [
            'mailer' => $request->input('mailer'),
            'host' => $request->input('host'),
            'port' => (int)$request->input('port'),
            'username' => $request->input('username'),
            'password' => $request->input('password'),
            'encryption' => $request->input('encryption'),
            'from_address' => $request->input('from_address'),
            'from_name' => $request->input('from_name'),
        ];

        // jiny/admin/config/mail.php 파일에 저장
        $configPath = base_path('jiny/admin/config/mail.php');
        
        // 디렉토리가 없으면 생성
        if (!file_exists(dirname($configPath))) {
            mkdir(dirname($configPath), 0755, true);
        }
        
        // PHP 설정 파일 내용 생성
        $content = "<?php\n\n";
        $content .= "/**\n";
        $content .= " * Admin Mail Configuration\n";
        $content .= " * \n";
        $content .= " * 이 파일은 관리자 패널에서 자동으로 생성됩니다.\n";
        $content .= " * 수동으로 편집하지 마세요.\n";
        $content .= " */\n\n";
        $content .= "return " . var_export($data, true) . ";\n";
        
        File::put($configPath, $content);

        // 설정 캐시 클리어 (옵션)
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response()->json([
            'success' => true,
            'message' => '메일 설정이 저장되었습니다.'
        ]);
    }

    /**
     * 메일 설정 테스트
     */
    public function test(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email'
        ]);

        $testEmail = $request->input('test_email');

        // jiny/admin/config/mail.php 파일에서 직접 읽기
        $configPath = base_path('jiny/admin/config/mail.php');
        if (file_exists($configPath)) {
            $adminMailConfig = include $configPath;
        } else {
            // 파일이 없으면 기본 config 사용
            $adminMailConfig = config('admin.mail', [
                'mailer' => 'smtp',
                'host' => 'smtp.mailgun.org',
                'port' => 587,
                'username' => '',
                'password' => '',
                'encryption' => 'tls',
                'from_address' => 'hello@example.com',
                'from_name' => 'Example',
            ]);
        }

        // 런타임 메일 설정 적용 - .env 값이 아닌 저장된 설정 사용
        config([
            'mail.default' => $adminMailConfig['mailer'],
            'mail.mailers.smtp.host' => $adminMailConfig['host'],
            'mail.mailers.smtp.port' => $adminMailConfig['port'],
            'mail.mailers.smtp.username' => $adminMailConfig['username'],
            'mail.mailers.smtp.password' => $adminMailConfig['password'],
            'mail.mailers.smtp.encryption' => $adminMailConfig['encryption'] === 'null' ? null : $adminMailConfig['encryption'],
            'mail.from.address' => $adminMailConfig['from_address'],
            'mail.from.name' => $adminMailConfig['from_name'],
        ]);

        // 메일러가 smtp가 아닌 경우 추가 설정
        if ($adminMailConfig['mailer'] !== 'smtp') {
            switch ($adminMailConfig['mailer']) {
                case 'sendmail':
                    config(['mail.mailers.sendmail.path' => '/usr/sbin/sendmail -bs']);
                    break;
                case 'log':
                    config(['mail.mailers.log.channel' => env('MAIL_LOG_CHANNEL', 'mail')]);
                    break;
            }
        }

        try {
            // 테스트 메일 정보 생성
            $subject = '[테스트] 메일 설정 테스트';
            $content = $this->getTestEmailContent($adminMailConfig);
            
            // EmailMailable 사용하여 메일 발송
            Mail::to($testEmail)->send(new EmailMailable(
                $subject, 
                $content, 
                $adminMailConfig['from_address'], 
                $adminMailConfig['from_name'], 
                $testEmail
            ));

            return response()->json([
                'success' => true,
                'message' => "테스트 이메일이 {$testEmail}로 발송되었습니다. 수신함을 확인해주세요."
            ]);
        } catch (\Exception $e) {
            \Log::error('메일 테스트 실패: ' . $e->getMessage(), [
                'exception' => $e,
                'mail_config' => $adminMailConfig,
                'test_email' => $testEmail
            ]);

            return response()->json([
                'success' => false,
                'message' => '테스트 이메일 발송 실패: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 테스트 이메일 내용 생성
     */
    private function getTestEmailContent($config)
    {
        $html = '<div style="font-family: Arial, sans-serif; padding: 20px; background-color: #f5f5f5;">';
        $html .= '<div style="max-width: 600px; margin: 0 auto; background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
        $html .= '<h2 style="color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px;">메일 설정 테스트</h2>';
        $html .= '<p style="color: #666; line-height: 1.6;">이것은 메일 설정 테스트 이메일입니다.</p>';
        $html .= '<div style="background-color: #f0f8ff; padding: 15px; border-radius: 5px; margin: 20px 0;">';
        $html .= '<h3 style="color: #333; margin-top: 0;">설정 정보</h3>';
        $html .= '<ul style="color: #666; line-height: 1.8;">';
        $html .= '<li><strong>발송 시간:</strong> ' . now()->format('Y-m-d H:i:s') . '</li>';
        $html .= '<li><strong>메일 드라이버:</strong> ' . ($config['mailer'] ?? 'unknown') . '</li>';
        $html .= '<li><strong>발신자:</strong> ' . ($config['from_address'] ?? 'unknown') . '</li>';
        $html .= '<li><strong>발신자명:</strong> ' . ($config['from_name'] ?? 'unknown') . '</li>';
        if ($config['mailer'] === 'smtp') {
            $html .= '<li><strong>SMTP 호스트:</strong> ' . ($config['host'] ?? 'unknown') . '</li>';
            $html .= '<li><strong>SMTP 포트:</strong> ' . ($config['port'] ?? 'unknown') . '</li>';
            $html .= '<li><strong>암호화:</strong> ' . ($config['encryption'] ?? 'none') . '</li>';
        }
        $html .= '</ul>';
        $html .= '</div>';
        $html .= '<div style="background-color: #e8f5e9; padding: 15px; border-radius: 5px; margin-top: 20px;">';
        $html .= '<p style="color: #2e7d32; margin: 0;"><strong>✓ 성공!</strong> 이 메일이 정상적으로 수신되면 메일 설정이 올바르게 작동하고 있습니다.</p>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
}