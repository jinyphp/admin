<?php

namespace Jiny\Admin\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class AdminSmsProviderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * SMS Provider 목록 페이지가 정상적으로 표시되는지 테스트
     *
     * @test
     */
    public function test_sms_provider_index_page_returns_200()
    {
        $response = $this->get('/admin/sms/provider');
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::template.index');
        $response->assertViewHas('jsonData');
        $response->assertSee('SMS 제공업체');
    }

    /**
     * SMS Provider 생성 페이지가 정상적으로 표시되는지 테스트
     *
     * @test
     */
    public function test_sms_provider_create_page_returns_200()
    {
        $response = $this->get('/admin/sms/provider/create');
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::template.create');
        $response->assertViewHas('jsonData');
        $response->assertSee('SMS 제공업체');
    }

    /**
     * SMS Provider 상세보기 페이지가 정상적으로 표시되는지 테스트
     *
     * @test
     */
    public function test_sms_provider_show_page_returns_200()
    {
        // 테스트용 데이터 생성
        DB::table('admin_sms_providers')->insert([
            'provider_name' => 'Test Provider',
            'provider_type' => 'test',
            'api_key' => 'test_api_key',
            'is_active' => true,
            'is_default' => false,
            'priority' => 0,
            'sent_count' => 0,
            'failed_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $provider = DB::table('admin_sms_providers')->first();
        
        $response = $this->get('/admin/sms/provider/' . $provider->id);
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::template.show');
        $response->assertViewHas('data');
    }

    /**
     * SMS Provider 수정 페이지가 정상적으로 표시되는지 테스트
     *
     * @test
     */
    public function test_sms_provider_edit_page_returns_200()
    {
        // 테스트용 데이터 생성
        DB::table('admin_sms_providers')->insert([
            'provider_name' => 'Test Provider',
            'provider_type' => 'test',
            'api_key' => 'test_api_key',
            'is_active' => true,
            'is_default' => false,
            'priority' => 0,
            'sent_count' => 0,
            'failed_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $provider = DB::table('admin_sms_providers')->first();
        
        $response = $this->get('/admin/sms/provider/' . $provider->id . '/edit');
        
        $response->assertStatus(200);
        $response->assertViewIs('jiny-admin::template.edit');
        $response->assertViewHas('data');
        $response->assertViewHas('form');
    }

    /**
     * 존재하지 않는 SMS Provider 조회 시 404 반환 테스트
     *
     * @test
     */
    public function test_non_existent_sms_provider_returns_404()
    {
        $response = $this->get('/admin/sms/provider/99999');
        
        $response->assertStatus(404);
    }

    /**
     * Vonage 기본 제공업체가 마이그레이션으로 생성되는지 테스트
     *
     * @test
     */
    public function test_vonage_provider_exists_after_migration()
    {
        // RefreshDatabase trait가 마이그레이션을 실행하므로
        // Vonage 제공업체가 존재해야 함
        $vonage = DB::table('admin_sms_providers')
            ->where('provider_type', 'vonage')
            ->first();
        
        $this->assertNotNull($vonage);
        $this->assertEquals('Vonage (Nexmo)', $vonage->provider_name);
        $this->assertTrue((bool)$vonage->is_default);
    }

    /**
     * SMS Provider 생성 후 목록에 표시되는지 테스트
     *
     * @test
     */
    public function test_created_provider_appears_in_list()
    {
        // 새 제공업체 생성
        DB::table('admin_sms_providers')->insert([
            'provider_name' => 'New Test Provider',
            'provider_type' => 'new_test',
            'api_key' => 'new_test_api_key',
            'is_active' => true,
            'is_default' => false,
            'priority' => 1,
            'sent_count' => 0,
            'failed_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $response = $this->get('/admin/sms/provider');
        
        $response->assertStatus(200);
        $response->assertSee('New Test Provider');
    }

    /**
     * 기본 제공업체 설정 시 다른 제공업체의 기본 설정이 해제되는지 테스트
     *
     * @test
     */
    public function test_setting_default_provider_unsets_others()
    {
        // 두 번째 제공업체 생성
        DB::table('admin_sms_providers')->insert([
            'provider_name' => 'Second Provider',
            'provider_type' => 'second',
            'api_key' => 'second_api_key',
            'is_active' => true,
            'is_default' => true, // 기본으로 설정
            'priority' => 1,
            'sent_count' => 0,
            'failed_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Vonage가 더 이상 기본이 아닌지 확인
        $vonage = DB::table('admin_sms_providers')
            ->where('provider_type', 'vonage')
            ->first();
        
        $second = DB::table('admin_sms_providers')
            ->where('provider_type', 'second')
            ->first();
        
        // 새로 생성한 제공업체만 기본이어야 함
        $this->assertTrue((bool)$second->is_default);
        
        // 기본 제공업체는 하나만 있어야 함
        $defaultCount = DB::table('admin_sms_providers')
            ->where('is_default', true)
            ->count();
        
        $this->assertEquals(1, $defaultCount);
    }
}