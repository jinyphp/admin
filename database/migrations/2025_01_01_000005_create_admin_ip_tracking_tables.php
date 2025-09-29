<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * [우선순위 5] IP 기반 보안 추적 및 접근 제어 테이블
     * 
     * 포함된 테이블:
     * 1. admin_ip_attempts: IP별 로그인 시도 기록 및 자동 차단
     * 2. admin_ip_blacklist: 영구/임시 차단 IP 관리
     * 3. admin_ip_whitelist: 신뢰할 수 있는 IP 관리 (차단 예외)
     * 4. admin_ip_logs: 모든 IP 접근 상세 로그 (모니터링용)
     * 
     * 주요 기능:
     * - IPv4/IPv6 지원
     * - CIDR 표기법으로 IP 대역 관리
     * - 자동 차단 및 만료 시간 관리
     * - 지역 정보 추적 (country_code, city)
     * 
     * 참고: 브루트포스 공격 방어 및 접근 제어용
     */
    public function up(): void
    {
        // IP별 로그인 시도 기록
        Schema::create('admin_ip_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->unique(); // IPv4/IPv6 지원
            $table->integer('attempts')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('last_success_at')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->boolean('is_blocked')->default(false);
            $table->timestamp('blocked_until')->nullable();
            $table->json('metadata')->nullable(); // 추가 정보 (user agent, referer 등)
            $table->timestamps();
            
            $table->index('ip_address');
            $table->index('is_blocked');
            $table->index('blocked_until');
            $table->index('last_attempt_at');
        });
        
        // IP 블랙리스트
        Schema::create('admin_ip_blacklist', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->unique();
            $table->string('ip_range')->nullable(); // CIDR 표기법 지원
            $table->string('reason')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->unsignedBigInteger('added_by')->nullable(); // 추가한 관리자
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index('ip_address');
            $table->index('is_active');
            $table->index('expires_at');
        });
        
        // IP 화이트리스트
        Schema::create('admin_ip_whitelist', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->unique();
            $table->string('ip_range')->nullable(); // CIDR 표기법 지원
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->unsignedBigInteger('added_by')->nullable(); // 추가한 관리자
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index('ip_address');
            $table->index('is_active');
            $table->index('expires_at');
        });
        
        // IP 접근 로그 (선택적 - 상세 모니터링용)
        Schema::create('admin_ip_logs', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45);
            $table->string('action'); // login_attempt, login_success, blocked, etc.
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('referer')->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('city')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('ip_address');
            $table->index('action');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_ip_logs');
        Schema::dropIfExists('admin_ip_whitelist');
        Schema::dropIfExists('admin_ip_blacklist');
        Schema::dropIfExists('admin_ip_attempts');
    }
};