<?php

namespace Jiny\Admin\Http\Livewire;

use Livewire\Component;

/**
 * 관리자 대시보드 타이틀 컴포넌트
 *
 * JSON 설정 파일을 통해 대시보드 타이틀과 서브타이틀을 관리하고
 * 모달 팝업으로 편집 기능을 제공합니다.
 *
 * @package Jiny\Admin
 * @since 1.0.0
 */
class AdminDashTitle extends Component
{
    /**
     * 페이지 타이틀
     * @var string
     */
    public $title = '';

    /**
     * 페이지 서브타이틀/설명
     * @var string
     */
    public $subtitle = '';

    /**
     * JSON 설정 데이터
     * @var array
     */
    public $jsonData = [];

    /**
     * JSON 파일 경로
     * @var string|null
     */
    public $jsonPath = '';

    /**
     * 편집 가능 여부
     * @var bool
     */
    public $editable = true;

    /**
     * 타이틀 설정 모달 표시 여부
     * @var bool
     */
    public $showTitleSettingsModal = false;

    /**
     * 편집용 임시 타이틀
     * @var string
     */
    public $editTitle = '';

    /**
     * 편집용 임시 서브타이틀
     * @var string
     */
    public $editSubtitle = '';

    /**
     * 마지막 업데이트 시간
     * @var string
     */
    public $lastUpdated = '';

    /**
     * 컴포넌트 마운트
     *
     * @param array $jsonData JSON 설정 데이터
     * @param string|null $jsonPath JSON 파일 경로
     * @param bool $editable 편집 가능 여부
     */
    public function mount($jsonData = [], $jsonPath = null, $editable = true)
    {
        $this->jsonData = $jsonData;
        $this->jsonPath = $jsonPath;
        $this->editable = $editable && !empty($jsonPath) && file_exists($jsonPath);

        // JSON 데이터에서 타이틀 정보 추출
        $this->title = $jsonData['title'] ?? '페이지 제목';
        $this->subtitle = $jsonData['subtitle'] ?? $jsonData['description'] ?? '';

        // 마지막 업데이트 시간 설정
        $this->updateLastUpdatedTime();
    }

    /**
     * 마지막 업데이트 시간 갱신
     */
    public function updateLastUpdatedTime()
    {
        $this->lastUpdated = now()->format('H:i:s');
    }

    /**
     * 페이지 새로고침
     */
    public function refreshPage()
    {
        $this->updateLastUpdatedTime();

        // JSON 파일 다시 읽기
        if ($this->jsonPath && file_exists($this->jsonPath)) {
            try {
                $jsonContent = file_get_contents($this->jsonPath);
                $jsonData = json_decode($jsonContent, true);

                $this->title = $jsonData['title'] ?? $this->title;
                $this->subtitle = $jsonData['subtitle'] ?? $jsonData['description'] ?? $this->subtitle;

                session()->flash('message', '페이지가 새로고침되었습니다.');
            } catch (\Exception $e) {
                session()->flash('error', '데이터 새로고침 중 오류가 발생했습니다.');
            }
        }
    }

    /**
     * 타이틀 설정 모달 열기
     */
    public function openTitleSettings()
    {
        if (!$this->editable) {
            return;
        }

        $this->editTitle = $this->title;
        $this->editSubtitle = $this->subtitle;
        $this->showTitleSettingsModal = true;
    }

    /**
     * 타이틀 설정 모달 닫기
     */
    public function closeTitleSettings()
    {
        $this->showTitleSettingsModal = false;
        $this->editTitle = '';
        $this->editSubtitle = '';
    }

    /**
     * 타이틀 설정 저장
     */
    public function saveTitleSettings()
    {
        // 유효성 검사
        $this->validate([
            'editTitle' => 'required|string|max:255',
            'editSubtitle' => 'nullable|string|max:500'
        ], [
            'editTitle.required' => '타이틀은 필수 입력 항목입니다.',
            'editTitle.max' => '타이틀은 최대 255자까지 입력 가능합니다.',
            'editSubtitle.max' => '서브타이틀은 최대 500자까지 입력 가능합니다.'
        ]);

        // 현재 값 업데이트
        $this->title = $this->editTitle;
        $this->subtitle = $this->editSubtitle;

        // JSON 파일 업데이트
        if ($this->jsonPath && file_exists($this->jsonPath)) {
            try {
                // JSON 파일 읽기
                $jsonContent = file_get_contents($this->jsonPath);
                $jsonData = json_decode($jsonContent, true);

                // 타이틀 정보 업데이트
                $jsonData['title'] = $this->editTitle;
                $jsonData['subtitle'] = $this->editSubtitle;

                // JSON 파일 쓰기 (포맷팅 적용)
                $jsonString = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                file_put_contents($this->jsonPath, $jsonString);

                // 마지막 업데이트 시간 갱신
                $this->updateLastUpdatedTime();

                // 성공 메시지
                session()->flash('message', '타이틀 설정이 저장되었습니다.');
            } catch (\Exception $e) {
                // 에러 메시지
                session()->flash('error', 'JSON 파일 저장 중 오류가 발생했습니다: ' . $e->getMessage());
            }
        }

        // 모달 닫기
        $this->closeTitleSettings();
    }


    /**
     * 컴포넌트 렌더링
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('jiny-admin::livewire.admin-dash-title');
    }
}