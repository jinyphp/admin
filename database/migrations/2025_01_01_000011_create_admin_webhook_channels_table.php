<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_webhook_channels', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('채널 이름');
            $table->string('type')->comment('웹훅 타입 (slack, discord, teams, custom)');
            $table->text('webhook_url')->comment('웹훅 URL');
            $table->json('headers')->nullable()->comment('커스텀 헤더');
            $table->json('config')->nullable()->comment('추가 설정');
            $table->text('description')->nullable()->comment('채널 설명');
            $table->boolean('is_active')->default(true)->comment('활성화 여부');
            $table->integer('priority')->default(0)->comment('우선순위');
            $table->timestamps();
            
            $table->index('name');
            $table->index('is_active');
            $table->index('type');
        });

        // 기본 웹훅 채널 생성 (비활성 상태)
        $this->createDefaultChannels();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_webhook_channels');
    }

    /**
     * 기본 웹훅 채널 생성
     */
    private function createDefaultChannels(): void
    {
        $channels = [
            [
                'name' => 'slack_general',
                'type' => 'slack',
                'webhook_url' => 'https://hooks.slack.com/services/YOUR/WEBHOOK/URL',
                'headers' => json_encode(['Content-Type' => 'application/json']),
                'config' => json_encode([
                    'channel' => '#general',
                    'username' => 'Admin Bot',
                    'icon_emoji' => ':robot_face:'
                ]),
                'description' => 'Slack 일반 채널 알림',
                'is_active' => false,
                'priority' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'discord_alerts',
                'type' => 'discord',
                'webhook_url' => 'https://discord.com/api/webhooks/YOUR/WEBHOOK/URL',
                'headers' => json_encode(['Content-Type' => 'application/json']),
                'config' => json_encode([
                    'username' => 'Admin Bot',
                    'avatar_url' => 'https://example.com/avatar.png'
                ]),
                'description' => 'Discord 알림 채널',
                'is_active' => false,
                'priority' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'teams_notifications',
                'type' => 'teams',
                'webhook_url' => 'https://outlook.office.com/webhook/YOUR/WEBHOOK/URL',
                'headers' => json_encode(['Content-Type' => 'application/json']),
                'config' => json_encode([
                    'theme_color' => '0076D7'
                ]),
                'description' => 'Microsoft Teams 알림',
                'is_active' => false,
                'priority' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'custom_webhook',
                'type' => 'custom',
                'webhook_url' => 'https://your-custom-webhook.com/endpoint',
                'headers' => json_encode([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer YOUR_TOKEN'
                ]),
                'config' => json_encode([
                    'format' => 'json',
                    'retry_count' => 3
                ]),
                'description' => '커스텀 웹훅 서비스',
                'is_active' => false,
                'priority' => 4,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('admin_webhook_channels')->insert($channels);
    }
};