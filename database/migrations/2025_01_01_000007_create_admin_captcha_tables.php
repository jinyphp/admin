<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * [우선순위 7] CAPTCHA 보안 검증 관리 테이블
     * 
     * 포함된 테이블:
     * 1. admin_captcha_logs: CAPTCHA 검증 로그
     *    - 다양한 CAPTCHA 제공자 지원 (reCAPTCHA, hCaptcha, Cloudflare)
     *    - 검증 점수 및 성공/실패 추적
     *    - 액션별 분류 (login, register, contact 등)
     * 
     * 2. admin_captcha_whitelist_blacklist: CAPTCHA 예외 관리
     *    - IP, 이메일, User Agent 기반 화이트/블랙리스트
     *    - 임시 예외 처리 (만료일 설정)
     *    - 관리자별 추가 사유 기록
     * 
     * 참고: 봇 방어 및 자동화 공격 차단용
     */
    public function up(): void
    {
        // Captcha Logs 테이블
        if (!Schema::hasTable('admin_captcha_logs')) {
            Schema::create('admin_captcha_logs', function (Blueprint $table) {
                $table->id();
                $table->string('provider', 50); // recaptcha, hcaptcha, cloudflare
                $table->string('action', 100); // login, register, contact, etc.
                $table->decimal('score', 3, 2)->nullable(); // 0.0 ~ 1.0
                $table->boolean('success');
                $table->string('ip_address', 45);
                $table->text('user_agent')->nullable();
                $table->json('response_data')->nullable();
                $table->string('error_codes')->nullable();
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
                $table->string('session_id')->nullable();
                $table->timestamps();
                
                $table->index('provider');
                $table->index('action');
                $table->index('success');
                $table->index('ip_address');
                $table->index('created_at');
            });
        }

        // Captcha Whitelist/Blacklist 테이블
        if (!Schema::hasTable('admin_captcha_whitelist_blacklist')) {
            Schema::create('admin_captcha_whitelist_blacklist', function (Blueprint $table) {
                $table->id();
                $table->enum('type', ['whitelist', 'blacklist']);
                $table->string('value'); // IP, email, user agent 등
                $table->string('value_type', 20); // ip, email, user_agent
                $table->text('reason')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamp('expires_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->timestamps();
                
                $table->index(['type', 'value_type', 'is_active']);
                $table->index('value');
                $table->index('expires_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_captcha_whitelist_blacklist');
        Schema::dropIfExists('admin_captcha_logs');
    }
};