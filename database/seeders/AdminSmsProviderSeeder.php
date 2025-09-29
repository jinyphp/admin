<?php

namespace Jiny\Admin\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminSmsProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $providers = [
            [
                'name' => 'Vonage (Nexmo)',
                'driver' => 'vonage',
                'driver_type' => 'sms',
                'config' => json_encode([
                    'api_key' => env('VONAGE_API_KEY', ''),
                    'api_secret' => env('VONAGE_API_SECRET', ''),
                    'from' => env('VONAGE_SMS_FROM', ''),
                    'webhook_secret' => env('VONAGE_WEBHOOK_SECRET', ''),
                    'api_endpoint' => 'https://rest.nexmo.com',
                    'timeout' => 30,
                    'retry_times' => 3
                ]),
                'is_active' => true,
                'is_default' => true,
                'description' => 'Vonage (formerly Nexmo) is a global cloud communications platform providing SMS, voice, and messaging APIs.',
                'priority' => 1,
                'supported_countries' => json_encode(['KR', 'US', 'JP', 'CN', 'GB', 'DE', 'FR', 'CA', 'AU', 'SG']),
                'rate_limit' => 10.00,
                'metadata' => json_encode([
                    'features' => ['sms', 'voice', 'verify', 'number_insights'],
                    'pricing_url' => 'https://www.vonage.com/communications-apis/sms/pricing/',
                    'documentation_url' => 'https://developer.vonage.com/messaging/sms/overview',
                    'support_unicode' => true,
                    'max_sms_length' => 1600,
                    'concatenated_sms' => true
                ]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Twilio',
                'driver' => 'twilio',
                'driver_type' => 'sms',
                'config' => json_encode([
                    'account_sid' => env('TWILIO_ACCOUNT_SID', ''),
                    'auth_token' => env('TWILIO_AUTH_TOKEN', ''),
                    'from' => env('TWILIO_FROM', ''),
                    'messaging_service_sid' => env('TWILIO_MESSAGING_SERVICE_SID', ''),
                    'api_endpoint' => 'https://api.twilio.com',
                    'timeout' => 30,
                    'retry_times' => 3
                ]),
                'is_active' => false,
                'is_default' => false,
                'description' => 'Twilio is a cloud communications platform offering programmable SMS, voice, video, and WhatsApp messaging.',
                'priority' => 2,
                'supported_countries' => json_encode(['KR', 'US', 'JP', 'CN', 'GB', 'DE', 'FR', 'CA', 'AU', 'IN']),
                'rate_limit' => 15.00,
                'metadata' => json_encode([
                    'features' => ['sms', 'mms', 'voice', 'video', 'whatsapp', 'verify'],
                    'pricing_url' => 'https://www.twilio.com/sms/pricing',
                    'documentation_url' => 'https://www.twilio.com/docs/sms',
                    'support_unicode' => true,
                    'max_sms_length' => 1600,
                    'concatenated_sms' => true,
                    'mms_support' => true
                ]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Aligo',
                'driver' => 'aligo',
                'driver_type' => 'sms',
                'config' => json_encode([
                    'user_id' => env('ALIGO_USER_ID', ''),
                    'api_key' => env('ALIGO_API_KEY', ''),
                    'sender' => env('ALIGO_SENDER', ''),
                    'api_endpoint' => 'https://apis.aligo.in',
                    'timeout' => 30,
                    'retry_times' => 3,
                    'test_mode' => env('ALIGO_TEST_MODE', false)
                ]),
                'is_active' => false,
                'is_default' => false,
                'description' => '알리고는 국내 SMS 발송 전문 서비스로, 대량 발송과 합리적인 가격을 제공합니다. 카카오 알림톡 및 친구톡 발송도 지원합니다.',
                'priority' => 3,
                'supported_countries' => json_encode(['KR']),
                'rate_limit' => 20.00,
                'metadata' => json_encode([
                    'features' => ['sms', 'lms', 'mms', 'kakao_alimtalk', 'kakao_friendtalk'],
                    'pricing_url' => 'https://www.aligo.in/price.html',
                    'documentation_url' => 'https://apis.aligo.in/api_doc.html',
                    'support_unicode' => true,
                    'max_sms_length' => 90,
                    'max_lms_length' => 2000,
                    'max_mms_length' => 2000,
                    'domestic_only' => true,
                    'currency' => 'KRW'
                ]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'AWS SNS',
                'driver' => 'aws_sns',
                'driver_type' => 'sms',
                'config' => json_encode([
                    'key' => env('AWS_ACCESS_KEY_ID', ''),
                    'secret' => env('AWS_SECRET_ACCESS_KEY', ''),
                    'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
                    'sender_id' => env('AWS_SNS_SENDER_ID', ''),
                    'max_price' => env('AWS_SNS_MAX_PRICE', '0.50'),
                    'sms_type' => env('AWS_SNS_SMS_TYPE', 'Transactional'),
                    'timeout' => 30,
                    'retry_times' => 3
                ]),
                'is_active' => false,
                'is_default' => false,
                'description' => 'Amazon Simple Notification Service (SNS) is a fully managed messaging service.',
                'priority' => 4,
                'supported_countries' => json_encode(['KR', 'US', 'JP', 'CN', 'GB', 'DE', 'FR', 'CA', 'AU', 'BR']),
                'rate_limit' => 10.00,
                'metadata' => json_encode([
                    'features' => ['sms', 'push', 'email', 'lambda_triggers'],
                    'pricing_url' => 'https://aws.amazon.com/sns/pricing/',
                    'documentation_url' => 'https://docs.aws.amazon.com/sns/',
                    'support_unicode' => true,
                    'max_sms_length' => 1600,
                    'concatenated_sms' => true,
                    'delivery_reports' => true
                ]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'MessageBird',
                'driver' => 'messagebird',
                'driver_type' => 'sms',
                'config' => json_encode([
                    'access_key' => env('MESSAGEBIRD_ACCESS_KEY', ''),
                    'originator' => env('MESSAGEBIRD_ORIGINATOR', ''),
                    'api_endpoint' => 'https://rest.messagebird.com',
                    'timeout' => 30,
                    'retry_times' => 3
                ]),
                'is_active' => false,
                'is_default' => false,
                'description' => 'MessageBird is a cloud communications platform that connects enterprises to customers globally.',
                'priority' => 5,
                'supported_countries' => json_encode(['NL', 'GB', 'DE', 'FR', 'US', 'SG', 'AU', 'JP', 'KR']),
                'rate_limit' => 12.00,
                'metadata' => json_encode([
                    'features' => ['sms', 'voice', 'verify', 'lookup', 'conversations'],
                    'pricing_url' => 'https://messagebird.com/pricing/sms',
                    'documentation_url' => 'https://developers.messagebird.com/docs/sms-messaging',
                    'support_unicode' => true,
                    'max_sms_length' => 1530,
                    'concatenated_sms' => true,
                    'hlr_lookup' => true
                ]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ];

        foreach ($providers as $provider) {
            DB::table('admin_sms_providers')->updateOrInsert(
                ['driver' => $provider['driver']],
                $provider
            );
        }

        $this->command->info('SMS providers seeded successfully!');
        $this->command->info('Default provider: Vonage (Nexmo)');
        $this->command->info('Total providers added: ' . count($providers));
    }
}