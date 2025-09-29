<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminUser2fa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Admin\Services\JsonConfigService;

class AdminUser2fa extends Controller
{
    private $jsonData;

    public function __construct()
    {
        // 서비스를 사용하여 JSON 파일 로드
        $jsonConfigService = new JsonConfigService;
        $this->jsonData = $jsonConfigService->loadFromControllerPath(__DIR__);
    }

    /**
     * Single Action __invoke method
     * 2FA 사용자 목록 조회 처리
     */
    public function __invoke(Request $request)
    {
        // JSON 데이터 확인
        if (! $this->jsonData) {
            return response('Error: JSON configuration file not found or invalid.', 500);
        }

        // template.index view 경로 확인
        if (! isset($this->jsonData['template']['index'])) {
            return response('Error: 화면을 출력하기 위한 template.index 설정이 필요합니다.', 500);
        }

        // route 정보를 jsonData에 추가
        if (isset($this->jsonData['route']['name'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route']['name'];
        } elseif (isset($this->jsonData['route']) && is_string($this->jsonData['route'])) {
            // 이전 버전 호환성
            $this->jsonData['currentRoute'] = $this->jsonData['route'];
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__.DIRECTORY_SEPARATOR.'AdminUser2fa.json';
        $settingsPath = $jsonPath; // settings drawer를 위한 경로

        return view($this->jsonData['template']['index'], [
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
        ]);
    }

    /**
     * Hook: Livewire 컴포넌트의 데이터 조회 전 실행
     * 2FA가 활성화된 사용자만 조회하도록 설정
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @return false|mixed false 반환시 정상 진행, 다른 값 반환시 해당 값이 출력됨
     */
    public function hookIndexing($wire)
    {
        // JSON 설정에서 이미 table.name이 'users'로 설정되어 있음
        // 추가 처리가 필요한 경우 여기에 구현

        return false;
    }

    /**
     * Hook: 데이터 조회 후 실행
     * 2FA 상태 정보를 추가합니다.
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @param  mixed  $rows  조회된 데이터
     * @return mixed 가공된 데이터
     */
    public function hookIndexed($wire, $rows)
    {
        foreach ($rows as $row) {
            $row->two_fa_status = $row->two_factor_enabled ? '활성화' : '비활성화';
            $row->last_used = $row->last_2fa_used_at ? date('Y-m-d H:i', strtotime($row->last_2fa_used_at)) : '-';
        }

        return $rows;
    }

    /**
     * Hook: 테이블 헤더 커스터마이징
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @return array 커스터마이징된 헤더 설정
     */
    public function hookTableHeader($wire)
    {
        return $this->jsonData['index']['table']['columns'] ?? [];
    }

    /**
     * Hook: 페이지네이션 설정
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @return array 페이지네이션 설정
     */
    public function hookPagination($wire)
    {
        return $this->jsonData['index']['pagination'] ?? [
            'perPage' => 10,
            'perPageOptions' => [10, 25, 50, 100],
        ];
    }

    /**
     * Hook: 정렬 설정
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @return array 정렬 설정
     */
    public function hookSorting($wire)
    {
        return $this->jsonData['index']['sorting'] ?? [
            'default' => 'created_at',
            'direction' => 'desc',
        ];
    }

    /**
     * Hook: 검색 설정
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @return array 검색 설정
     */
    public function hookSearch($wire)
    {
        return $this->jsonData['index']['search'] ?? [
            'placeholder' => 'Search user2fas...',
            'debounce' => 300,
        ];
    }

    /**
     * Hook: 필터 설정
     *
     * @param  mixed  $wire  Livewire 컴포넌트 인스턴스
     * @return array 필터 설정
     */
    public function hookFilters($wire)
    {
        return $this->jsonData['index']['filters'] ?? [];
    }
}
