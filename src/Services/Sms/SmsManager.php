<?php

namespace Jiny\Admin\Services\Sms;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * SMS Manager - 드라이버 관리 및 SMS 발송 처리
 */
class SmsManager
{
    private $drivers = [];
    private $defaultDriver = null;
    private $currentDriver = null;

    /**
     * 지원하는 드라이버 목록
     */
    private $availableDrivers = [
        'vonage' => VonageDriver::class,
        'twilio' => TwilioDriver::class,
    ];

    /**
     * 제공업체 ID로 드라이버 생성
     */
    public function withProvider($providerId)
    {
        $provider = DB::table('admin_sms_providers')
            ->where('id', $providerId)
            ->where('is_active', 1)
            ->first();

        if (!$provider) {
            throw new InvalidArgumentException("Provider ID {$providerId} not found or inactive");
        }

        return $this->driver($provider->driver_type, [
            'api_key' => $provider->api_key,
            'api_secret' => $provider->api_secret,
            'account_sid' => $provider->account_sid,
            'auth_token' => $provider->auth_token,
            'from_number' => $provider->from_number,
            'provider_id' => $provider->id,
            'provider_name' => $provider->provider_name
        ]);
    }

    /**
     * 드라이버 인스턴스 가져오기
     */
    public function driver($name = null, array $config = [])
    {
        $name = $name ?: $this->getDefaultDriver();

        // 이미 생성된 드라이버가 있으면 반환
        if (isset($this->drivers[$name]) && empty($config)) {
            return $this->drivers[$name];
        }

        // 드라이버 생성
        $this->drivers[$name] = $this->createDriver($name, $config);
        $this->currentDriver = $this->drivers[$name];

        return $this;
    }

    /**
     * 드라이버 생성
     */
    protected function createDriver($name, array $config = [])
    {
        if (!isset($this->availableDrivers[$name])) {
            throw new InvalidArgumentException("Driver [{$name}] not supported.");
        }

        $driverClass = $this->availableDrivers[$name];

        // 설정이 없으면 기본 설정 로드
        if (empty($config)) {
            $config = $this->getDriverConfig($name);
        }

        return new $driverClass($config);
    }

    /**
     * 드라이버 설정 가져오기
     */
    protected function getDriverConfig($name)
    {
        // 데이터베이스에서 기본 제공업체 찾기
        $provider = DB::table('admin_sms_providers')
            ->where('driver_type', $name)
            ->where('is_active', 1)
            ->orderBy('is_default', 'desc')
            ->orderBy('priority', 'desc')
            ->first();

        if ($provider) {
            return [
                'api_key' => $provider->api_key,
                'api_secret' => $provider->api_secret,
                'account_sid' => $provider->account_sid,
                'auth_token' => $provider->auth_token,
                'from_number' => $provider->from_number,
                'provider_id' => $provider->id,
                'provider_name' => $provider->provider_name
            ];
        }

        // 환경 변수에서 가져오기 (fallback)
        if ($name === 'vonage') {
            return [
                'api_key' => env('VONAGE_API_KEY', ''),
                'api_secret' => env('VONAGE_API_SECRET', ''),
                'from_number' => env('VONAGE_FROM_NUMBER', 'JINYPHP')
            ];
        } elseif ($name === 'twilio') {
            return [
                'account_sid' => env('TWILIO_ACCOUNT_SID', ''),
                'auth_token' => env('TWILIO_AUTH_TOKEN', ''),
                'from_number' => env('TWILIO_FROM_NUMBER', '')
            ];
        }

        return [];
    }

    /**
     * 기본 드라이버 가져오기
     */
    protected function getDefaultDriver()
    {
        if ($this->defaultDriver) {
            return $this->defaultDriver;
        }

        // 데이터베이스에서 기본 제공업체 찾기
        $defaultProvider = DB::table('admin_sms_providers')
            ->where('is_active', 1)
            ->where('is_default', 1)
            ->first();

        if ($defaultProvider) {
            return $defaultProvider->driver_type;
        }

        // 활성화된 첫 번째 제공업체
        $firstProvider = DB::table('admin_sms_providers')
            ->where('is_active', 1)
            ->orderBy('priority', 'desc')
            ->first();

        if ($firstProvider) {
            return $firstProvider->driver_type;
        }

        return 'vonage'; // 기본값
    }

    /**
     * SMS 발송
     */
    public function send($toNumber, $message, $fromNumber = null)
    {
        if (!$this->currentDriver) {
            $this->driver();
        }

        return $this->currentDriver->send($toNumber, $message, $fromNumber);
    }

    /**
     * 잔액 조회
     */
    public function getBalance()
    {
        if (!$this->currentDriver) {
            $this->driver();
        }

        return $this->currentDriver->getBalance();
    }

    /**
     * 발송 상태 조회
     */
    public function getStatus($messageId)
    {
        if (!$this->currentDriver) {
            $this->driver();
        }

        return $this->currentDriver->getStatus($messageId);
    }

    /**
     * 전화번호 포맷팅
     */
    public function formatPhoneNumber($phoneNumber)
    {
        if (!$this->currentDriver) {
            $this->driver();
        }

        return $this->currentDriver->formatPhoneNumber($phoneNumber);
    }

    /**
     * 현재 드라이버 이름
     */
    public function getDriverName()
    {
        if (!$this->currentDriver) {
            return null;
        }

        return $this->currentDriver->getName();
    }

    /**
     * 지원하는 드라이버 목록
     */
    public function getAvailableDrivers()
    {
        return array_keys($this->availableDrivers);
    }
}