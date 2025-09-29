<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * [우선순위 10] SMS 발송 시스템 관련 테이블들을 생성
     * 
     * 포함된 테이블:
     * 1. admin_sms_providers: SMS 서비스 제공자 설정
     *    - 다중 제공자 지원 (Twilio, Vonage, Aligo, AWS SNS 등)
     *    - 국가별 라우팅 및 우선순위 관리
     *    - 발송 속도 제한 설정
     * 
     * 2. admin_sms_sends: SMS 발송 로그 및 상태 추적
     *    - 발송 상태 추적 (pending, sent, delivered, failed)
     *    - 비용 및 통화 정보 기록
     *    - SMS/Voice 타입 구분
     * 
     * 3. admin_sms_queues: SMS 발송 대기열 관리
     *    - 예약 발송 지원
     *    - 재시도 메커니즘
     *    - 대량 발송 관리
     * 
     * 4. admin_sms_webhooks: SMS 서비스 Webhook 이벤트 로그
     *    - 전송 상태 업데이트
     *    - 수신 메시지 처리
     *    - 이벤트 추적
     * 
     * 참고: 2FA 인증, 알림, 마케팅 SMS 발송용
     */
    public function up(): void
    {
        // SMS Provider 테이블 - 다양한 SMS 서비스 제공자 관리
        if (!Schema::hasTable('admin_sms_providers')) {
            Schema::create('admin_sms_providers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('driver'); // twilio, vonage, aligo, aws_sns 등
                $table->string('driver_type', 50)->nullable(); // sms, voice, push 등
                $table->json('config'); // API 키, 시크릿 등 설정
                $table->boolean('is_active')->default(false);
                $table->boolean('is_default')->default(false);
                $table->text('description')->nullable();
                $table->integer('priority')->default(0);
                $table->json('supported_countries')->nullable();
                $table->decimal('rate_limit', 10, 2)->nullable(); // 분당 발송 제한
                $table->json('metadata')->nullable();
                $table->timestamps();
                
                $table->index('is_active');
                $table->index('is_default');
                $table->index('driver');
            });
        }

        // SMS 발송 로그 테이블
        if (!Schema::hasTable('admin_sms_sends')) {
            Schema::create('admin_sms_sends', function (Blueprint $table) {
                $table->id();
                $table->foreignId('provider_id')->nullable()->constrained('admin_sms_providers')->onDelete('set null');
                $table->string('to_number');
                $table->string('from_number')->nullable();
                $table->text('message');
                $table->string('status', 50)->default('pending'); // pending, sent, delivered, failed
                $table->string('message_id')->nullable(); // 외부 서비스의 메시지 ID
                $table->decimal('cost', 10, 4)->nullable();
                $table->string('currency', 3)->nullable();
                $table->json('response_data')->nullable(); // 전체 응답 데이터
                $table->text('error_message')->nullable();
                $table->string('error_code')->nullable();
                $table->string('type', 20)->default('sms'); // sms, voice
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
                $table->string('ip_address', 45)->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamps();
                
                $table->index('status');
                $table->index('to_number');
                $table->index('provider_id');
                $table->index('user_id');
                $table->index('created_at');
            });
        }

        // SMS 큐 테이블
        if (!Schema::hasTable('admin_sms_queues')) {
            Schema::create('admin_sms_queues', function (Blueprint $table) {
                $table->id();
                $table->string('to_number');
                $table->text('message');
                $table->string('status', 20)->default('pending');
                $table->integer('attempts')->default(0);
                $table->timestamp('scheduled_at')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->foreignId('provider_id')->nullable()->constrained('admin_sms_providers');
                $table->json('metadata')->nullable();
                $table->timestamps();
                
                $table->index(['status', 'scheduled_at']);
                $table->index('to_number');
            });
        }

        // SMS Webhook 로그 테이블
        if (!Schema::hasTable('admin_sms_webhooks')) {
            Schema::create('admin_sms_webhooks', function (Blueprint $table) {
                $table->id();
                $table->string('provider');
                $table->string('event_type');
                $table->json('payload');
                $table->string('message_id')->nullable();
                $table->string('status')->nullable();
                $table->timestamp('received_at');
                $table->timestamps();
                
                $table->index('message_id');
                $table->index('provider');
                $table->index('event_type');
                $table->index('received_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_sms_webhooks');
        Schema::dropIfExists('admin_sms_queues');
        Schema::dropIfExists('admin_sms_sends');
        Schema::dropIfExists('admin_sms_providers');
    }
};