<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * [우선순위 9] 이메일 시스템 관련 테이블들을 생성
     * 
     * 포함된 테이블:
     * 1. admin_email_templates: 이메일 템플릿 관리
     *    - HTML/Text/Markdown 형식 지원
     *    - 변수 치환 및 카테고리 관리
     *    - 첨부파일 및 CC/BCC 설정
     * 
     * 2. admin_emailtemplates: 레거시 템플릿 테이블 (하위 호환성)
     * 
     * 3. admin_email_logs: 이메일 발송 로그 및 추적
     *    - 발송 상태 추적 (pending, sent, bounced, opened, clicked)
     *    - 열람/클릭 카운트 및 시간 기록
     *    - 캠페인 및 이벤트별 추적
     * 
     * 4. admin_email_notification_rules: 자동 알림 규칙
     *    - 이벤트 기반 자동 발송
     *    - 조건부 발송 및 지연 발송
     * 
     * 5. admin_email_template_versions: 템플릿 버전 관리
     *    - 변경 이력 추적
     *    - 버전별 활성화 관리
     * 
     * 6. admin_email_ab_tests: A/B 테스트 관리
     *    - 템플릿 성과 비교
     *    - 오픈율/클릭율 분석
     * 
     * 7. admin_email_tracking: 이메일 열람/클릭 상세 추적
     *    - 개별 이벤트 추적
     *    - URL 클릭 추적
     * 
     * 8. admin_email_subscribers: 구독자 관리
     *    - 구독/탈퇴 관리
     *    - 반송/스팸신고 처리
     *    - 세그먼트 및 선호도 관리
     * 
     * 참고: 완전한 이메일 마케팅 및 트랜잭션 이메일 시스템
     */
    public function up(): void
    {
        // Email Templates 테이블 - 재사용 가능한 이메일 템플릿
        if (!Schema::hasTable('admin_email_templates')) {
            Schema::create('admin_email_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('subject');
                $table->text('body');
                $table->json('variables')->nullable(); // 사용 가능한 변수 목록
                $table->enum('type', ['html', 'text', 'markdown'])->default('html');
                $table->string('category')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('status')->default(true);
                $table->integer('priority')->default(0);
                $table->json('attachments')->nullable();
                $table->string('from_name')->nullable();
                $table->string('from_email')->nullable();
                $table->string('reply_to')->nullable();
                $table->text('cc')->nullable();
                $table->text('bcc')->nullable();
                $table->text('description')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                
                $table->index('slug');
                $table->index('is_active');
                $table->index('category');
                $table->index('created_at');
            });
        }

        // 레거시 테이블명 호환 (필요시)
        if (!Schema::hasTable('admin_emailtemplates') && !Schema::hasTable('admin_email_templates')) {
            Schema::create('admin_emailtemplates', function (Blueprint $table) {
                $table->id();
                $table->boolean('enable')->default(true);
                $table->string('title');
                $table->text('description')->nullable();
                $table->integer('pos')->default(0);
                $table->integer('depth')->default(0);
                $table->integer('ref')->default(0);
                $table->timestamps();
            });
        }

        // Email Logs 테이블
        if (!Schema::hasTable('admin_email_logs')) {
            Schema::create('admin_email_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('template_id')->nullable()->constrained('admin_email_templates')->onDelete('set null');
                $table->string('to_email');
                $table->string('to_name')->nullable();
                $table->string('from_email')->nullable();
                $table->string('from_name')->nullable();
                $table->string('subject');
                $table->text('body');
                $table->string('status', 20)->default('pending'); // pending, processing, sent, failed, bounced, opened, clicked
                $table->text('error_message')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('opened_at')->nullable();
                $table->timestamp('clicked_at')->nullable();
                $table->integer('open_count')->default(0);
                $table->integer('click_count')->default(0);
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->json('metadata')->nullable();
                $table->string('message_id')->nullable();
                $table->string('campaign_id')->nullable();
                $table->string('event_type')->nullable();
                $table->string('tracking_id')->nullable();
                $table->timestamps();
                
                $table->index('to_email');
                $table->index('status');
                $table->index('sent_at');
                $table->index('template_id');
                $table->index('user_id');
                $table->index('event_type');
                $table->index('created_at');
            });
        }

        // Email Notification Rules 테이블
        if (!Schema::hasTable('admin_email_notification_rules')) {
            Schema::create('admin_email_notification_rules', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('event'); // user.registered, order.completed, etc.
                $table->foreignId('template_id')->constrained('admin_email_templates');
                $table->json('conditions')->nullable(); // 조건 규칙
                $table->json('recipients')->nullable(); // 수신자 규칙
                $table->boolean('is_active')->default(true);
                $table->integer('priority')->default(0);
                $table->integer('delay_minutes')->default(0); // 지연 발송
                $table->json('metadata')->nullable();
                $table->timestamps();
                
                $table->index('event');
                $table->index('is_active');
                $table->index('priority');
            });
        }

        // Email Template Versions 테이블 (템플릿 버전 관리)
        if (!Schema::hasTable('admin_email_template_versions')) {
            Schema::create('admin_email_template_versions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('template_id')->constrained('admin_email_templates')->onDelete('cascade');
                $table->integer('version');
                $table->string('subject');
                $table->text('body');
                $table->json('variables')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->text('change_notes')->nullable();
                $table->boolean('is_active')->default(false);
                $table->timestamps();
                
                $table->unique(['template_id', 'version']);
                $table->index('template_id');
                $table->index('is_active');
            });
        }

        // Email A/B Tests 테이블
        if (!Schema::hasTable('admin_email_ab_tests')) {
            Schema::create('admin_email_ab_tests', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->foreignId('template_a_id')->constrained('admin_email_templates');
                $table->foreignId('template_b_id')->constrained('admin_email_templates');
                $table->integer('sample_size');
                $table->integer('emails_sent_a')->default(0);
                $table->integer('emails_sent_b')->default(0);
                $table->integer('opens_a')->default(0);
                $table->integer('opens_b')->default(0);
                $table->integer('clicks_a')->default(0);
                $table->integer('clicks_b')->default(0);
                $table->string('winner')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('ended_at')->nullable();
                $table->timestamps();
                
                $table->index('started_at');
                $table->index('ended_at');
            });
        }

        // Email Tracking 테이블
        if (!Schema::hasTable('admin_email_tracking')) {
            Schema::create('admin_email_tracking', function (Blueprint $table) {
                $table->id();
                $table->foreignId('email_log_id')->constrained('admin_email_logs')->onDelete('cascade');
                $table->string('event_type'); // open, click, bounce, unsubscribe
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->string('url')->nullable(); // for click events
                $table->json('metadata')->nullable();
                $table->timestamp('tracked_at');
                $table->timestamps();
                
                $table->index(['email_log_id', 'event_type']);
                $table->index('tracked_at');
            });
        }

        // Email Subscribers 테이블
        if (!Schema::hasTable('admin_email_subscribers')) {
            Schema::create('admin_email_subscribers', function (Blueprint $table) {
                $table->id();
                $table->string('email')->unique();
                $table->string('name')->nullable();
                $table->json('segments')->nullable(); // Array of segment tags
                $table->boolean('is_subscribed')->default(true);
                $table->timestamp('subscribed_at')->nullable();
                $table->timestamp('unsubscribed_at')->nullable();
                $table->string('unsubscribe_reason')->nullable();
                $table->boolean('is_bounced')->default(false);
                $table->timestamp('bounced_at')->nullable();
                $table->string('bounce_type')->nullable();
                $table->boolean('is_complained')->default(false);
                $table->timestamp('complained_at')->nullable();
                $table->json('preferences')->nullable(); // Email preferences
                $table->string('language', 5)->default('en');
                $table->string('timezone')->nullable();
                $table->json('custom_fields')->nullable();
                $table->string('source')->nullable(); // signup, import, api, etc.
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();
                
                $table->index('is_subscribed');
                $table->index('is_bounced');
                $table->index('is_complained');
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_email_subscribers');
        Schema::dropIfExists('admin_email_tracking');
        Schema::dropIfExists('admin_email_ab_tests');
        Schema::dropIfExists('admin_email_template_versions');
        Schema::dropIfExists('admin_email_notification_rules');
        Schema::dropIfExists('admin_email_logs');
        Schema::dropIfExists('admin_emailtemplates');
        Schema::dropIfExists('admin_email_templates');
    }
};