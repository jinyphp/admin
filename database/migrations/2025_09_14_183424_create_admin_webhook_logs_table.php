<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // admin_webhook_logs 테이블이 없으면 생성
        if (!Schema::hasTable('admin_webhook_logs')) {
            Schema::create('admin_webhook_logs', function (Blueprint $table) {
                $table->id();
                $table->string('channel_name')->comment('채널 이름');
                $table->text('message')->comment('발송 메시지');
                $table->string('status')->default('pending')->comment('발송 상태 (pending/sent/failed)');
                $table->text('error_message')->nullable()->comment('에러 메시지');
                $table->timestamp('sent_at')->nullable()->comment('발송 시간');
                $table->timestamps();
                
                $table->index('channel_name');
                $table->index('status');
                $table->index('created_at');
            });
        }
        
        // admin_webhook_subscriptions 테이블이 없으면 생성
        if (!Schema::hasTable('admin_webhook_subscriptions')) {
            Schema::create('admin_webhook_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->string('channel_name')->comment('채널 이름');
                $table->string('event_type')->comment('이벤트 타입');
                $table->boolean('is_active')->default(true)->comment('활성화 여부');
                $table->timestamps();
                
                $table->unique(['channel_name', 'event_type']);
                $table->index('channel_name');
                $table->index('event_type');
                $table->index('is_active');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_webhook_logs');
        Schema::dropIfExists('admin_webhook_subscriptions');
    }
};