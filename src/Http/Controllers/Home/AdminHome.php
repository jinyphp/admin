<?php

namespace Jiny\Admin\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminHome extends Controller
{
    /**
     * 관리자 홈 페이지를 표시합니다.
     *
     * @return \Illuminate\View\View
     */
    public function __invoke(Request $request)
    {
        // JSON 파일 경로
        $jsonPath = resource_path('views/home/home.json');

        // vendor 패키지 경로로 대체
        if (!file_exists($jsonPath)) {
            $jsonPath = base_path('vendor/jiny/admin/src/Http/Controllers/Home/home.json');
        }

        // JSON 파일 읽기 (UTF-8 인코딩)
        $config = [];
        if (file_exists($jsonPath)) {
            $jsonContent = file_get_contents($jsonPath);
            // BOM 제거 (있는 경우)
            $jsonContent = preg_replace('/^\xEF\xBB\xBF/', '', $jsonContent);
            // JSON 디코딩 (UTF-8)
            $config = json_decode($jsonContent, true, 512, JSON_UNESCAPED_UNICODE);

            // JSON 파싱 에러 확인
            if (json_last_error() !== JSON_ERROR_NONE) {
                // 에러 발생 시 빈 배열 사용
                $config = [];
            }
        }

        // 활성화된 카드만 필터링
        $cards = [];
        if (isset($config['cards'])) {
            $cards = array_filter($config['cards'], function($card) {
                return isset($card['enabled']) && $card['enabled'] === true;
            });
        }

        return view('jiny-admin::home.home', [
            'cards' => $cards
        ]);
    }
}