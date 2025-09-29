<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add missing columns to admin_2fa_codes table
     * 
     * 추가되는 컬럼:
     * - enabled: 2FA 활성화 상태
     * - last_used_at: 마지막 사용 시간
     * - backup_codes_used: 사용된 백업 코드 수
     * - metadata: 추가 메타데이터 (JSON)
     */
    public function up(): void
    {
        Schema::table('admin_2fa_codes', function (Blueprint $table) {
            // 2FA 활성화 상태
            if (!Schema::hasColumn('admin_2fa_codes', 'enabled')) {
                $table->boolean('enabled')->default(true)->after('method')->comment('2FA 활성화 상태');
            }
            
            // 마지막 사용 시간
            if (!Schema::hasColumn('admin_2fa_codes', 'last_used_at')) {
                $table->timestamp('last_used_at')->nullable()->after('expires_at')->comment('마지막 사용 시간');
            }
            
            // 사용된 백업 코드 수
            if (!Schema::hasColumn('admin_2fa_codes', 'backup_codes_used')) {
                $table->integer('backup_codes_used')->default(0)->after('last_used_at')->comment('사용된 백업 코드 수');
            }
            
            // 메타데이터 (JSON)
            if (!Schema::hasColumn('admin_2fa_codes', 'metadata')) {
                $table->json('metadata')->nullable()->after('backup_codes_used')->comment('추가 메타데이터');
            }
            
            // 인덱스 추가
            if (!Schema::hasIndex('admin_2fa_codes', 'admin_2fa_codes_enabled_index')) {
                $table->index('enabled');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_2fa_codes', function (Blueprint $table) {
            // 인덱스 제거
            if (Schema::hasIndex('admin_2fa_codes', 'admin_2fa_codes_enabled_index')) {
                $table->dropIndex(['enabled']);
            }
            
            // 컬럼 제거
            $table->dropColumn([
                'enabled',
                'last_used_at',
                'backup_codes_used',
                'metadata'
            ]);
        });
    }
};