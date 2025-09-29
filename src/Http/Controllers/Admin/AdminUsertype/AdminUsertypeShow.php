<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminUsertype;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Admin\Services\JsonConfigService;

/**
 * AdminUsertypeShow Controller
 */
class AdminUsertypeShow extends Controller
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
     * 상세 정보 표시
     */
    public function __invoke(Request $request, $id)
    {
        // 데이터베이스에서 데이터 조회
        $tableName = $this->jsonData['table']['name'] ?? 'admin_usertypes';
        $query = DB::table($tableName);

        // 기본 where 조건 적용
        if (isset($this->jsonData['table']['where']['default'])) {
            foreach ($this->jsonData['table']['where']['default'] as $condition) {
                if (count($condition) === 3) {
                    $query->where($condition[0], $condition[1], $condition[2]);
                } elseif (count($condition) === 2) {
                    $query->where($condition[0], $condition[1]);
                }
            }
        }

        $item = $query->where('id', $id)->first();

        if (! $item) {
            $redirectUrl = isset($this->jsonData['route']['name'])
                ? route($this->jsonData['route']['name'].'.index')
                : '/admin/usertype';

            return redirect($redirectUrl)
                ->with('error', 'Usertype을(를) 찾을 수 없습니다.');
        }

        // 객체를 배열로 변환
        $data = (array) $item;

        // Apply hookShowing if exists
        if (method_exists($this, 'hookShowing')) {
            $data = $this->hookShowing(null, $data);
        }

        // route 정보를 jsonData에 추가
        if (isset($this->jsonData['route']['name'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route']['name'];
        } elseif (isset($this->jsonData['route']) && is_string($this->jsonData['route'])) {
            $this->jsonData['currentRoute'] = $this->jsonData['route'];
        }

        // template.show view 경로 확인
        if (! isset($this->jsonData['template']['show'])) {
            return response('Error: 화면을 출력하기 위한 template.show 설정이 필요합니다.', 500);
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__.DIRECTORY_SEPARATOR.'AdminUsertype.json';
        $settingsPath = $jsonPath; // settings drawer를 위한 경로

        // Set title from data or use default
        $title = $data['title'] ?? $data['name'] ?? 'Usertype Details';

        return view($this->jsonData['template']['show'], [
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'data' => $data,
            'id' => $id,
            'title' => $title,
            'subtitle' => 'Usertype 상세 정보',
        ]);
    }

    /**
     * 상세보기 표시 전에 호출됩니다.
     */
    public function hookShowing($wire, $data)
    {
        // 날짜 형식 지정
        $dateFormat = $this->jsonData['show']['display']['datetimeFormat'] ?? 'Y-m-d H:i:s';

        if (isset($data['created_at'])) {
            $data['created_at_formatted'] = date($dateFormat, strtotime($data['created_at']));
        }

        if (isset($data['updated_at'])) {
            $data['updated_at_formatted'] = date($dateFormat, strtotime($data['updated_at']));
        }

        // Boolean 라벨 처리
        $booleanLabels = $this->jsonData['show']['display']['booleanLabels'] ?? [
            'true' => 'Enabled',
            'false' => 'Disabled',
        ];

        if (isset($data['enable'])) {
            $data['enable_label'] = $data['enable'] ? $booleanLabels['true'] : $booleanLabels['false'];
        }

        return $data;
    }

    /**
     * Hook: 조회 후 데이터 가공
     */
    public function hookShowed($wire, $data)
    {
        return $data;
    }
}
