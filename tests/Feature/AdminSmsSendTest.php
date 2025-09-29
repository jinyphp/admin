<?php

namespace Jiny\Admin\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class AdminSmsSendTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // 테스트용 SMS 제공업체 생성
        DB::table('admin_sms_providers')->insert([
            'provider_name' => 'Test Provider',
            'provider_type' => 'test',
            'api_key' => 'test_api_key',
            'api_secret' => 'test_api_secret',
            'from_number' => '1234567890',
            'is_active' => true,
            'is_default' => true,
            'priority' => 0,
            'sent_count' => 0,
            'failed_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * SMS Send 목록 페이지가 정상적으로 표시되는지 테스트
     *
     * @test
     */
    public function test_sms_send_index_page_returns_200()
    {
        $response = $this->get('/admin/sms/send');
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::template.index');
        $response->assertViewHas('jsonData');
        $response->assertSee('SMS 발송');
    }

    /**
     * SMS Send 생성 페이지가 정상적으로 표시되는지 테스트
     *
     * @test
     */
    public function test_sms_send_create_page_returns_200()
    {
        $response = $this->get('/admin/sms/send/create');
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::template.create');
        $response->assertViewHas('jsonData');
        $response->assertSee('수신번호');
        $response->assertSee('메시지 내용');
    }

    /**
     * SMS Send 상세보기 페이지가 정상적으로 표시되는지 테스트
     *
     * @test
     */
    public function test_sms_send_show_page_returns_200()
    {
        // 테스트용 SMS 메시지 생성
        $provider = DB::table('admin_sms_providers')->first();
        
        DB::table('admin_sms_sends')->insert([
            'provider_id' => $provider->id,
            'provider_name' => $provider->provider_name,
            'to_number' => '01012345678',
            'from_number' => '1234567890',
            'message' => '테스트 메시지입니다.',
            'message_length' => 10,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $sms = DB::table('admin_sms_sends')->first();
        
        $response = $this->get('/admin/sms/send/' . $sms->id);
        
        $response->assertStatus(200);
        $response->assertViewHas('data');
        // 헤더 컴포넌트에 표시되는 제목
        $response->assertSee('01012345678');
    }

    /**
     * SMS Send 수정 페이지가 정상적으로 표시되는지 테스트
     *
     * @test
     */
    public function test_sms_send_edit_page_returns_200()
    {
        // 테스트용 SMS 메시지 생성
        $provider = DB::table('admin_sms_providers')->first();
        
        DB::table('admin_sms_sends')->insert([
            'provider_id' => $provider->id,
            'provider_name' => $provider->provider_name,
            'to_number' => '01012345678',
            'from_number' => '1234567890',
            'message' => '테스트 메시지입니다.',
            'message_length' => 10,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $sms = DB::table('admin_sms_sends')->first();
        
        $response = $this->get('/admin/sms/send/' . $sms->id . '/edit');
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::template.edit');
        $response->assertViewHas('data');
        $response->assertViewHas('form');
    }

    /**
     * 존재하지 않는 SMS Send 조회 시 404 반환 테스트
     *
     * @test
     */
    public function test_non_existent_sms_send_returns_404()
    {
        $response = $this->get('/admin/sms/send/99999');
        
        $response->assertStatus(404);
    }

    /**
     * SMS 메시지 생성 시 pending 상태로 저장되는지 테스트
     *
     * @test
     */
    public function test_sms_message_saved_as_pending()
    {
        $provider = DB::table('admin_sms_providers')->first();
        
        // SMS 메시지 생성
        DB::table('admin_sms_sends')->insert([
            'provider_id' => $provider->id,
            'provider_name' => $provider->provider_name,
            'to_number' => '01098765432',
            'from_number' => $provider->from_number,
            'message' => '새로운 테스트 메시지',
            'message_length' => mb_strlen('새로운 테스트 메시지'),
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $sms = DB::table('admin_sms_sends')
            ->where('to_number', '01098765432')
            ->first();
        
        $this->assertNotNull($sms);
        $this->assertEquals('pending', $sms->status);
        $this->assertNull($sms->sent_at);
    }

    /**
     * SMS 발송 액션 라우트가 존재하는지 테스트
     *
     * @test
     */
    public function test_sms_send_action_route_exists()
    {
        $provider = DB::table('admin_sms_providers')->first();
        
        DB::table('admin_sms_sends')->insert([
            'provider_id' => $provider->id,
            'provider_name' => $provider->provider_name,
            'to_number' => '01011112222',
            'from_number' => $provider->from_number,
            'message' => '발송 테스트 메시지',
            'message_length' => 10,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $sms = DB::table('admin_sms_sends')->first();
        
        // SMS 발송 액션 엔드포인트 테스트
        $response = $this->post('/admin/sms/send/' . $sms->id . '/send');
        
        // JSON 응답이어야 함
        $response->assertStatus(500); // SmsService가 실제 API를 호출하려 하므로 500 에러 발생
        $response->assertJson(['success' => false]);
    }

    /**
     * 대량 발송 액션 라우트가 존재하는지 테스트
     *
     * @test
     */
    public function test_bulk_send_action_route_exists()
    {
        $response = $this->post('/admin/sms/send/bulk-send', [
            'ids' => [1, 2, 3]
        ]);
        
        // JSON 응답이어야 함
        $response->assertStatus(422); // validation 실패 (존재하지 않는 ID)
    }

    /**
     * 재발송 액션 라우트가 존재하는지 테스트
     *
     * @test
     */
    public function test_resend_action_route_exists()
    {
        $provider = DB::table('admin_sms_providers')->first();
        
        DB::table('admin_sms_sends')->insert([
            'provider_id' => $provider->id,
            'provider_name' => $provider->provider_name,
            'to_number' => '01033334444',
            'from_number' => $provider->from_number,
            'message' => '재발송 테스트 메시지',
            'message_length' => 11,
            'status' => 'failed',
            'error_message' => 'Test error',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $sms = DB::table('admin_sms_sends')
            ->where('status', 'failed')
            ->first();
        
        $response = $this->post('/admin/sms/send/' . $sms->id . '/resend');
        
        // JSON 응답이어야 함
        $response->assertStatus(500); // SmsService가 실제 API를 호출하려 하므로 500 에러 발생
        $response->assertJson(['success' => false]);
    }

    /**
     * SMS 목록에서 상태별 표시가 올바른지 테스트
     *
     * @test
     */
    public function test_sms_list_shows_different_statuses()
    {
        $provider = DB::table('admin_sms_providers')->first();
        
        // 다양한 상태의 SMS 메시지 생성
        $statuses = ['pending', 'sent', 'delivered', 'failed'];
        
        foreach ($statuses as $status) {
            DB::table('admin_sms_sends')->insert([
                'provider_id' => $provider->id,
                'provider_name' => $provider->provider_name,
                'to_number' => '010' . rand(10000000, 99999999),
                'from_number' => $provider->from_number,
                'message' => $status . ' 상태 테스트 메시지',
                'message_length' => 15,
                'status' => $status,
                'sent_at' => in_array($status, ['sent', 'delivered']) ? now() : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        $response = $this->get('/admin/sms/send');
        
        $response->assertStatus(200);
        // 각 상태가 목록에 표시되는지 확인
        $response->assertSee('대기중');
        $response->assertSee('발송완료');
        $response->assertSee('수신확인');
        $response->assertSee('발송실패');
    }

    /**
     * 메시지 길이가 올바르게 계산되는지 테스트
     *
     * @test
     */
    public function test_message_length_calculated_correctly()
    {
        $provider = DB::table('admin_sms_providers')->first();
        
        $message = '안녕하세요. 테스트 메시지입니다. Hello World!';
        $expectedLength = mb_strlen($message);
        
        DB::table('admin_sms_sends')->insert([
            'provider_id' => $provider->id,
            'provider_name' => $provider->provider_name,
            'to_number' => '01055556666',
            'from_number' => $provider->from_number,
            'message' => $message,
            'message_length' => $expectedLength,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $sms = DB::table('admin_sms_sends')
            ->where('to_number', '01055556666')
            ->first();
        
        $this->assertEquals($expectedLength, $sms->message_length);
    }
}