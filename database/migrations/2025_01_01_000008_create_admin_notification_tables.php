<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * [우선순위 8] 알림 시스템 관리 테이블 (Webhook & Push Notification)
     * 
     * 포함된 테이블:
     * 1. admin_webhook_configs: Webhook 설정
     *    - 이벤트별 외부 시스템 연동
     *    - 재시도 및 타임아웃 설정
     *    - 서명 검증 지원
     * 
     * 2. admin_webhook_logs: Webhook 호출 로그
     *    - 전송 상태 및 응답 추적
     *    - 재시도 관리
     * 
     * 3. admin_push_providers: 푸시 알림 제공자 설정
     *    - FCM, APNs, Web Push 지원
     *    - 다중 제공자 관리
     * 
     * 4. admin_push_devices: 사용자 디바이스 토큰 관리
     *    - iOS, Android, Web 디바이스 지원
     *    - 디바이스별 활성화 상태 관리
     * 
     * 5. admin_push_logs: 푸시 알림 전송 로그
     *    - 전송/수신/읽음 상태 추적
     *    - 에러 메시지 기록
     * 
     * 참고: 실시간 알림 및 외부 시스템 연동용
     */
    public function up(): void
    {
        // Webhook Configuration 테이블
        if (!Schema::hasTable('admin_webhook_configs')) {
            Schema::create('admin_webhook_configs', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('url');
                $table->string('method', 10)->default('POST');
                $table->json('headers')->nullable();
                $table->json('events'); // 구독할 이벤트 목록
                $table->string('secret')->nullable(); // 서명 검증용
                $table->boolean('is_active')->default(true);
                $table->integer('retry_times')->default(3);
                $table->integer('timeout')->default(30);
                $table->json('metadata')->nullable();
                $table->timestamps();
                
                $table->index('is_active');
            });
        }

        // Webhook Logs 테이블
        if (!Schema::hasTable('admin_webhook_logs')) {
            Schema::create('admin_webhook_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('config_id')->constrained('admin_webhook_configs')->onDelete('cascade');
                $table->string('event');
                $table->json('payload');
                $table->integer('status_code')->nullable();
                $table->text('response')->nullable();
                $table->string('status', 20)->default('pending'); // pending, success, failed
                $table->integer('attempts')->default(0);
                $table->text('error_message')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();
                
                $table->index(['config_id', 'status']);
                $table->index('event');
                $table->index('created_at');
            });
        }

        // Push Notification Providers 테이블
        if (!Schema::hasTable('admin_push_providers')) {
            Schema::create('admin_push_providers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('type', 20); // fcm, apns, web_push
                $table->json('config'); // API keys, certificates, etc.
                $table->boolean('is_active')->default(true);
                $table->boolean('is_default')->default(false);
                $table->json('metadata')->nullable();
                $table->timestamps();
                
                $table->index('type');
                $table->index('is_active');
                $table->index('is_default');
            });
        }

        // Push Notification Devices 테이블
        if (!Schema::hasTable('admin_push_devices')) {
            Schema::create('admin_push_devices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('device_token', 500);
                $table->string('device_type', 20); // ios, android, web
                $table->string('device_name')->nullable();
                $table->string('device_model')->nullable();
                $table->string('app_version')->nullable();
                $table->boolean('is_active')->default(true);
                $table->json('metadata')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamps();
                
                $table->unique(['user_id', 'device_token']);
                $table->index('device_type');
                $table->index('is_active');
            });
        }

        // Push Notification Logs 테이블
        if (!Schema::hasTable('admin_push_logs')) {
            Schema::create('admin_push_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('provider_id')->nullable()->constrained('admin_push_providers');
                $table->foreignId('device_id')->nullable()->constrained('admin_push_devices');
                $table->foreignId('user_id')->nullable()->constrained();
                $table->string('title');
                $table->text('body');
                $table->json('data')->nullable(); // 추가 데이터
                $table->string('status', 20)->default('pending'); // pending, sent, delivered, failed
                $table->text('error_message')->nullable();
                $table->string('message_id')->nullable(); // 외부 서비스 메시지 ID
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
                
                $table->index(['user_id', 'status']);
                $table->index('status');
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_push_logs');
        Schema::dropIfExists('admin_push_devices');
        Schema::dropIfExists('admin_push_providers');
        Schema::dropIfExists('admin_webhook_logs');
        Schema::dropIfExists('admin_webhook_configs');
    }
};