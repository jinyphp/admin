<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add browser and device information columns to admin_user_sessions table
     * 
     * 추가되는 컬럼:
     * - browser: 브라우저 종류 (Chrome, Firefox, Safari 등)
     * - browser_version: 브라우저 버전
     * - platform: 운영체제 (Windows, macOS, Linux 등)
     * - device: 디바이스 타입 (desktop, mobile, tablet 등)
     * - login_at: 로그인 시간
     * - last_activity: 마지막 활동 시간 (별칭)
     */
    public function up(): void
    {
        Schema::table('admin_user_sessions', function (Blueprint $table) {
            // 브라우저 및 디바이스 정보
            if (!Schema::hasColumn('admin_user_sessions', 'browser')) {
                $table->string('browser', 50)->nullable()->after('user_agent')->comment('브라우저 종류');
            }
            if (!Schema::hasColumn('admin_user_sessions', 'browser_version')) {
                $table->string('browser_version', 20)->nullable()->after('browser')->comment('브라우저 버전');
            }
            if (!Schema::hasColumn('admin_user_sessions', 'platform')) {
                $table->string('platform', 50)->nullable()->after('browser_version')->comment('운영체제');
            }
            if (!Schema::hasColumn('admin_user_sessions', 'device')) {
                $table->string('device', 30)->nullable()->after('platform')->comment('디바이스 타입');
            }
            
            // 시간 관련 컬럼
            if (!Schema::hasColumn('admin_user_sessions', 'login_at')) {
                $table->timestamp('login_at')->nullable()->after('device')->comment('로그인 시간');
            }
            if (!Schema::hasColumn('admin_user_sessions', 'last_activity')) {
                $table->timestamp('last_activity')->nullable()->after('login_at')->comment('마지막 활동 시간');
            }
            
            // 인덱스 추가
            if (!Schema::hasIndex('admin_user_sessions', 'admin_user_sessions_browser_index')) {
                $table->index('browser');
            }
            if (!Schema::hasIndex('admin_user_sessions', 'admin_user_sessions_platform_index')) {
                $table->index('platform');
            }
            if (!Schema::hasIndex('admin_user_sessions', 'admin_user_sessions_device_index')) {
                $table->index('device');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_user_sessions', function (Blueprint $table) {
            // 인덱스 제거
            $table->dropIndex(['browser']);
            $table->dropIndex(['platform']);
            $table->dropIndex(['device']);
            
            // 컬럼 제거
            $table->dropColumn([
                'browser',
                'browser_version', 
                'platform',
                'device',
                'login_at',
                'last_activity'
            ]);
        });
    }
};