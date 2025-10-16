<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminSupport;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Site\Models\SiteSupport;
use Jiny\Admin\Services\JsonConfigService;

/**
 * 지원 요청 상세 보기 컨트롤러
 */
class AdminSupportShow extends Controller
{
    private $jsonData;

    public function __construct()
    {
        $this->middleware('admin');

        $jsonConfigService = new JsonConfigService;
        $this->jsonData = $jsonConfigService->loadFromControllerPath(__DIR__);
    }

    public function __invoke(Request $request, $id)
    {
        $support = SiteSupport::with(['user', 'assignedTo'])->findOrFail($id);

        // JSON 데이터 확인
        if (! $this->jsonData) {
            return response('Error: JSON configuration file not found or invalid.', 500);
        }

        // template.show view 경로 확인
        if (! isset($this->jsonData['template']['show'])) {
            return response('Error: 화면을 출력하기 위한 template.show 설정이 필요합니다.', 500);
        }

        return view($this->jsonData['template']['show'], [
            'support' => $support,
            'jsonData' => $this->jsonData,
            'controllerClass' => static::class,
        ]);
    }
}