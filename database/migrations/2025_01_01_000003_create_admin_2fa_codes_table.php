<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * [우선순위 3] 2FA(Two-Factor Authentication) 임시 코드 저장 테이블
     * - SMS/Email로 발송되는 6자리 인증 코드 관리
     * - 코드 만료 시간 및 시도 횟수 제한
     * - 사용 완료된 코드 추적
     * 
     * 참고: TOTP 외에 SMS/Email 2FA 지원을 위한 테이블
     */
    public function up(): void
    {
        // 2FA 임시 코드 저장 테이블
        Schema::create('admin_2fa_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('method', 20); // sms, email
            $table->string('code', 6); // 6자리 숫자 코드
            $table->string('destination'); // 전화번호 또는 이메일
            $table->integer('attempts')->default(0); // 시도 횟수
            $table->boolean('used')->default(false); // 사용 여부
            $table->timestamp('expires_at'); // 만료 시간 (5분)
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'code', 'expires_at']);
            $table->index(['method', 'destination']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_2fa_codes');
    }
};