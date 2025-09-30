<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminIpWhitelist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Jiny\Admin\Services\JsonConfigService;
use Jiny\Admin\Models\AdminIpWhitelist as IpWhitelistModel;

/**
 * IP 화이트리스트 메인 컨트롤러
 *
 * IP 화이트리스트 목록을 관리하는 메인 컨트롤러입니다.
 * Single Action Controller 패턴을 따르며, __invoke 메소드를 통해 실행됩니다.
 */
class AdminIpWhitelist extends Controller
{
    /**
     * JSON 설정 데이터
     * @var array|null
     */
    private $jsonData;

    /**
     * 생성자 - JSON 설정 로드
     */
    public function __construct()
    {
        $jsonConfigService = new JsonConfigService;
        $this->jsonData = $jsonConfigService->loadFromControllerPath(__DIR__);
    }

    /**
     * IP 화이트리스트 목록 페이지 표시
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function __invoke(Request $request)
    {
        // JSON 데이터 확인
        if (!$this->jsonData) {
            return response('Error: JSON 설정을 로드할 수 없습니다.', 500);
        }

        // 템플릿 경로 확인
        if (!isset($this->jsonData['template']['index'])) {
            return response('Error: template.index 설정이 필요합니다.', 500);
        }

        // 뷰 데이터 준비
        $jsonPath = __DIR__ . DIRECTORY_SEPARATOR . 'AdminIpWhitelist.json';
        $this->jsonData['controllerClass'] = get_class($this);


        // dd($this->jsonData);

        return view($this->jsonData['template']['index'], [
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $jsonPath,
            'controllerClass' => static::class
        ]);
    }

    /**
     * Hook: 데이터 조회 전 실행
     *
     * @param mixed $wire Livewire 컴포넌트
     * @return false|mixed
     */
    public function hookIndexing($wire)
    {
        // 현재 사용자의 IP 주소 표시
        $wire->currentUserIp = request()->ip();

        // IP 화이트리스트 기능 활성화 상태 확인
        $wire->isWhitelistEnabled = config('setting.ip_whitelist.enabled', false);

        return false; // false면 정상 진행
    }

    /**
     * Hook: 데이터 조회 후 실행
     *
     * @param mixed $wire Livewire 컴포넌트
     * @param mixed $rows 조회된 데이터
     * @return mixed
     */
    public function hookIndexed($wire, $rows)
    {
        // 각 IP의 상태를 시각적으로 표시하기 위한 처리
        foreach ($rows as $row) {
            // 만료 여부 체크
            if ($row->expires_at && now()->gt($row->expires_at)) {
                $row->status_class = 'expired';
                $row->status_text = '만료됨';
            } elseif (!$row->is_active) {
                $row->status_class = 'inactive';
                $row->status_text = '비활성';
            } else {
                $row->status_class = 'active';
                $row->status_text = '활성';
            }

            // IP 타입별 표시 형식
            switch ($row->type) {
                case 'range':
                    $row->ip_display = "{$row->ip_range_start} ~ {$row->ip_range_end}";
                    break;
                case 'cidr':
                    $row->ip_display = "{$row->ip_address}/{$row->cidr_prefix}";
                    break;
                default:
                    $row->ip_display = $row->ip_address;
            }
        }

        return $rows;
    }

    /**
     * Hook: 테이블 헤더 커스터마이징
     *
     * @param mixed $wire Livewire 컴포넌트
     * @return array|null
     */
    public function hookTableHeader($wire)
    {
        // 추가 액션 버튼
        return [
            'actions' => [
                [
                    'label' => '접근 로그 보기',
                    'route' => 'admin.ip-access-logs',
                    'class' => 'btn-secondary',
                    'icon' => 'fas fa-history'
                ],
                [
                    'label' => '만료된 IP 정리',
                    'action' => 'cleanupExpired',
                    'class' => 'btn-warning',
                    'icon' => 'fas fa-broom',
                    'confirm' => '만료된 IP를 모두 삭제하시겠습니까?'
                ]
            ]
        ];
    }

    /**
     * Hook: 커스텀 액션 처리
     *
     * @param mixed $wire Livewire 컴포넌트
     * @param string $action 액션명
     * @param mixed $data 데이터
     * @return void
     */
    public function hookCustomAction($wire, $action, $data = null)
    {
        switch ($action) {
            case 'cleanupExpired':
                $this->cleanupExpiredIps($wire);
                break;

            case 'toggleStatus':
                $this->toggleIpStatus($wire, $data);
                break;
        }
    }

    /**
     * 만료된 IP 정리
     *
     * @param mixed $wire Livewire 컴포넌트
     * @return void
     */
    private function cleanupExpiredIps($wire)
    {
        $count = IpWhitelistModel::expired()->delete();

        session()->flash('notification', [
            'type' => 'success',
            'title' => '정리 완료',
            'message' => "{$count}개의 만료된 IP가 삭제되었습니다."
        ]);

        // 캐시 초기화
        IpWhitelistModel::clearCache();
    }

    /**
     * IP 활성화 상태 토글
     *
     * @param mixed $wire Livewire 컴포넌트
     * @param int $id IP ID
     * @return void
     */
    private function toggleIpStatus($wire, $id)
    {
        $ip = IpWhitelistModel::find($id);

        if ($ip) {
            $ip->is_active = !$ip->is_active;
            $ip->save();

            $status = $ip->is_active ? '활성화' : '비활성화';

            session()->flash('notification', [
                'type' => 'success',
                'title' => '상태 변경',
                'message' => "IP가 {$status}되었습니다."
            ]);
        }
    }
}
