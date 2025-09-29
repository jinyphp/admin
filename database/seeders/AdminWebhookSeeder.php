<?php

namespace Jiny\Admin\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminWebhookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 샘플 웹훅 로그 생성
        $channels = DB::table('admin_webhook_channels')->pluck('name');
        
        if ($channels->isEmpty()) {
            $this->command->info('No webhook channels found. Skipping log generation.');
            return;
        }
        
        $logs = [];
        $now = Carbon::now();
        
        // 최근 30일간의 샘플 로그 생성
        for ($i = 0; $i < 100; $i++) {
            $createdAt = $now->copy()->subMinutes(rand(1, 43200)); // 최근 30일
            $status = rand(1, 10) > 2 ? 'sent' : 'failed'; // 80% 성공률
            
            $logs[] = [
                'channel_name' => $channels->random(),
                'message' => $this->getRandomMessage(),
                'status' => $status,
                'error_message' => $status === 'failed' ? $this->getRandomError() : null,
                'sent_at' => $status === 'sent' ? $createdAt : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];
        }
        
        // 배치 삽입
        foreach (array_chunk($logs, 50) as $chunk) {
            DB::table('admin_webhook_logs')->insert($chunk);
        }
        
        $this->command->info('Sample webhook logs created successfully!');
        $this->command->info('Total logs: ' . count($logs));
    }
    
    /**
     * 랜덤 메시지 생성
     */
    private function getRandomMessage(): string
    {
        $messages = [
            '새로운 사용자가 가입했습니다.',
            '주문이 완료되었습니다.',
            '결제가 성공적으로 처리되었습니다.',
            '시스템 백업이 완료되었습니다.',
            '보안 경고: 비정상적인 로그인 시도가 감지되었습니다.',
            '서버 모니터링: CPU 사용률이 높습니다.',
            '데이터베이스 최적화가 완료되었습니다.',
            '새로운 댓글이 작성되었습니다.',
            '파일 업로드가 완료되었습니다.',
            '이메일이 성공적으로 발송되었습니다.',
            '캐시가 초기화되었습니다.',
            '배치 작업이 시작되었습니다.',
            'API 호출 제한에 도달했습니다.',
            '새로운 버전이 배포되었습니다.',
            '일일 리포트가 생성되었습니다.',
        ];
        
        return $messages[array_rand($messages)];
    }
    
    /**
     * 랜덤 에러 메시지 생성
     */
    private function getRandomError(): string
    {
        $errors = [
            'Connection timeout',
            'Invalid webhook URL',
            'Authentication failed',
            'Rate limit exceeded',
            'Server returned 500 error',
            'Invalid JSON payload',
            'Network unreachable',
            'SSL certificate verification failed',
            'Request entity too large',
            'Service temporarily unavailable',
        ];
        
        return $errors[array_rand($errors)];
    }
}