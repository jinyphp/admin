<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * [우선순위 4] 관리자 계정 잠금 해제 토큰 테이블
 * 
 * 보안 정책에 의해 잠긴 계정을 안전하게 해제하기 위한 일회용 토큰을 관리합니다.
 * 이메일이나 SMS로 발송된 링크를 통해 계정 잠금을 해제할 수 있습니다.
 * 
 * 주요 기능:
 * - 일회용 보안 토큰 생성 및 관리 (SHA256 해시 저장)
 * - 토큰 만료 시간 관리 (기본 60분)
 * - 시도 횟수 제한 (최대 5회)
 * - IP 주소 및 User Agent 추적
 * - 사용 이력 및 수동 만료 관리
 * 
 * 참고: 계정 잠금은 로그인 실패, 비정상 접근 등으로 발생
 * @since 1.0.0
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_unlock_tokens', function (Blueprint $table) {
            $table->id();
            
            // 사용자 정보
            $table->unsignedBigInteger('user_id')->index()
                ->comment('잠금 해제 대상 사용자 ID');
            
            // 토큰 정보
            $table->string('token', 255)->unique()
                ->comment('SHA256 해시로 저장된 보안 토큰');
            $table->timestamp('expires_at')->index()
                ->comment('토큰 만료 시간 (생성 후 60분)');
            $table->timestamp('used_at')->nullable()
                ->comment('토큰 사용 시간 (사용 시 기록)');
            $table->timestamp('expired_at')->nullable()
                ->comment('수동 만료 처리 시간 (관리자가 무효화한 경우)');
            
            // 보안 정보
            $table->integer('attempts')->default(0)
                ->comment('잠금 해제 시도 횟수 (최대 5회)');
            $table->string('ip_address', 45)->nullable()
                ->comment('토큰 생성 요청 IP 주소');
            $table->text('user_agent')->nullable()
                ->comment('토큰 생성 요청 User Agent');
            
            // 타임스탬프
            $table->timestamps();
            
            // 외래 키 제약
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->comment('사용자 삭제 시 관련 토큰도 삭제');
            
            // 복합 인덱스
            $table->index(['user_id', 'expires_at'], 'idx_user_expires')
                ->comment('사용자별 만료 토큰 조회 최적화');
            $table->index(['token', 'used_at'], 'idx_token_used')
                ->comment('토큰 검증 및 사용 여부 확인 최적화');
            $table->index('created_at', 'idx_created')
                ->comment('최근 생성 토큰 조회 최적화');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_unlock_tokens');
    }
};