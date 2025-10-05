<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * [우선순위 1] Users 테이블에 Admin 관련 모든 컬럼을 추가
     * - 전화번호 및 검증 상태 (SMS 2FA용)
     * - 2FA 방법 선택 (totp, sms, email)
     * - 백업 코드 사용 기록
     * - 로그인 시도 및 계정 잠금 관련 필드
     * - 계정 상태 관리 (active, suspended, banned, pending)
     * 
     * 참고: Laravel 기본 users 테이블에 관리자 기능 확장
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // UUID for sharding (moved from jiny/auth)
            if (!Schema::hasColumn('users', 'uuid')) {
                $table->string('uuid', 36)
                    ->after('id')
                    ->nullable()
                    ->unique()
                    ->comment('UUID for sharding');
            }

            if (!Schema::hasColumn('users', 'shard_id')) {
                $table->integer('shard_id')
                    ->after('uuid')
                    ->nullable()
                    ->comment('Shard number (0-15)');
            }

            // 2FA 컬럼 (from jiny/auth)
            if (!Schema::hasColumn('users', 'two_factor_secret')) {
                $table->text('two_factor_secret')
                    ->after('password')
                    ->nullable();
            }

            if (!Schema::hasColumn('users', 'two_factor_recovery_codes')) {
                $table->text('two_factor_recovery_codes')
                    ->after('two_factor_secret')
                    ->nullable();
            }

            if (!Schema::hasColumn('users', 'two_factor_confirmed_at')) {
                $table->timestamp('two_factor_confirmed_at')
                    ->after('two_factor_recovery_codes')
                    ->nullable();
            }

            // 추가 필드 (from jiny/auth)
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username')->after('email')->unique()->nullable();
            }

            if (!Schema::hasColumn('users', 'isAdmin')) {
                $table->string('isAdmin')->default('0');
            }

            if (!Schema::hasColumn('users', 'utype')) {
                $table->string('utype', 10)->after('password')->default('USR')->index();
            }

            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status', 20)->after('utype')->default('active')->index();
            }

            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable();
            }

            if (!Schema::hasColumn('users', 'last_activity_at')) {
                $table->timestamp('last_activity_at')->nullable();
            }

            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }

            // 전화번호 및 검증 상태 (SMS 2FA용)
            if (!Schema::hasColumn('users', 'phone_number')) {
                $table->string('phone_number')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'phone_verified')) {
                $table->boolean('phone_verified')->default(false)->after('phone_number');
            }
            
            // 2FA 방법 선택 (totp, sms, email)
            if (!Schema::hasColumn('users', 'two_factor_method')) {
                $table->string('two_factor_method', 20)->default('totp')->after('two_factor_confirmed_at');
            }
            
            // 백업 코드 사용 기록
            if (!Schema::hasColumn('users', 'used_backup_codes')) {
                $table->json('used_backup_codes')->nullable()->after('two_factor_recovery_codes');
            }
            
            // 마지막 코드 발송 시간 (재발송 제한용)
            if (!Schema::hasColumn('users', 'last_code_sent_at')) {
                $table->timestamp('last_code_sent_at')->nullable();
            }
            
            // 추가 보안 필드
            if (!Schema::hasColumn('users', 'login_attempts')) {
                $table->integer('login_attempts')->default(0);
            }
            if (!Schema::hasColumn('users', 'locked_until')) {
                $table->timestamp('locked_until')->nullable();
            }
            if (!Schema::hasColumn('users', 'unlock_token')) {
                $table->string('unlock_token')->nullable();
            }
            if (!Schema::hasColumn('users', 'unlock_token_expires_at')) {
                $table->timestamp('unlock_token_expires_at')->nullable();
            }
            
            // 계정 상태
            if (!Schema::hasColumn('users', 'account_status')) {
                $table->string('account_status', 20)->default('active'); // active, suspended, banned, pending
            }
            if (!Schema::hasColumn('users', 'suspended_until')) {
                $table->timestamp('suspended_until')->nullable();
            }
            if (!Schema::hasColumn('users', 'suspension_reason')) {
                $table->text('suspension_reason')->nullable();
            }
            
            // 인덱스 추가
            if (!Schema::hasIndex('users', 'users_uuid_index')) {
                $table->index('uuid');
            }
            if (!Schema::hasIndex('users', 'users_shard_id_index')) {
                $table->index('shard_id');
            }
            if (!Schema::hasIndex('users', 'users_phone_number_index')) {
                $table->index('phone_number');
            }
            if (!Schema::hasIndex('users', 'users_account_status_index')) {
                $table->index('account_status');
            }
            if (!Schema::hasIndex('users', 'users_unlock_token_index')) {
                $table->index('unlock_token');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 인덱스 제거
            $table->dropIndex(['uuid']);
            $table->dropIndex(['shard_id']);
            $table->dropIndex(['phone_number']);
            $table->dropIndex(['account_status']);
            $table->dropIndex(['unlock_token']);

            // 컬럼 제거
            $table->dropColumn([
                'uuid',
                'shard_id',
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at',
                'username',
                'isAdmin',
                'utype',
                'status',
                'last_login_at',
                'last_activity_at',
                'phone_number',
                'phone_verified',
                'two_factor_method',
                'used_backup_codes',
                'last_code_sent_at',
                'login_attempts',
                'locked_until',
                'unlock_token',
                'unlock_token_expires_at',
                'account_status',
                'suspended_until',
                'suspension_reason'
            ]);

            if (Schema::hasColumn('users', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};