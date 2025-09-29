<?php

namespace Jiny\Admin\Http\Livewire;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;

class AdminDelete extends Component
{
    public $jsonData;

    protected $controller = null;

    public $controllerClass = null;

    // 삭제 모달 상태
    public $showDeleteModal = false;

    public $deleteType = ''; // 'single' or 'multiple'

    public $deleteIds = [];

    public $deleteCount = 0;

    // 확인 키
    public $deleteConfirmKey = '';

    public $deleteConfirmInput = '';

    public $deleteButtonEnabled = false;

    public function mount($jsonData = null, $controllerClass = null)
    {
        $this->jsonData = $jsonData;

        // 컨트롤러 클래스 설정
        if ($controllerClass) {
            $this->controllerClass = $controllerClass;
            $this->setupController();
        } elseif (isset($this->jsonData['controllerClass'])) {
            $this->controllerClass = $this->jsonData['controllerClass'];
            $this->setupController();
        }
    }

    /**
     * 컨트롤러 설정
     */
    protected function setupController()
    {
        // 컨트롤러 인스턴스 생성
        if ($this->controllerClass && class_exists($this->controllerClass)) {
            $this->controller = new $this->controllerClass;
            \Log::info('AdminDelete: Controller loaded successfully', [
                'class' => $this->controllerClass,
            ]);
        } else {
            \Log::warning('AdminDelete: Controller class not found', [
                'class' => $this->controllerClass,
            ]);
        }
    }

    /**
     * 커스텀 액션 호출
     * 컨트롤러의 hookCustom{Name} 메소드를 호출합니다.
     *
     * @param  string  $actionName  액션명
     * @param  array  $params  파라미터
     */
    public function callCustomAction($actionName, $params = [])
    {
        // 컨트롤러 확인
        if (! $this->controller) {
            $this->setupController();
        }

        if (! $this->controller) {
            session()->flash('error', '컨트롤러가 설정되지 않았습니다.');

            return;
        }

        // Hook 메소드명 생성
        $methodName = 'hookCustom'.ucfirst($actionName);

        // Hook 메소드 존재 확인
        if (! method_exists($this->controller, $methodName)) {
            session()->flash('error', "Hook 메소드 '{$methodName}'를 찾을 수 없습니다.");

            return;
        }

        // Hook 호출
        try {
            $result = $this->controller->$methodName($this, $params);

            // 결과 처리
            if (isset($result['redirect'])) {
                return redirect($result['redirect']);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Hook 실행 중 오류가 발생했습니다: '.$e->getMessage());
        }
    }

    // 단일 항목 삭제 이벤트 처리
    #[On('delete-single')]
    public function handleDeleteSingle($id)
    {
        $this->deleteType = 'single';
        $this->deleteIds = [$id];
        $this->deleteCount = 1;
        $this->openDeleteModal();
    }

    // 다중 항목 삭제 이벤트 처리
    #[On('delete-multiple')]
    public function handleDeleteMultiple($ids)
    {
        if (empty($ids)) {
            return;
        }

        $this->deleteType = 'multiple';
        $this->deleteIds = $ids;
        $this->deleteCount = count($ids);
        $this->openDeleteModal();
    }

    // 삭제 모달 열기
    private function openDeleteModal()
    {
        // 4자리 랜덤 키 생성
        $this->deleteConfirmKey = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $this->deleteConfirmInput = '';
        $this->deleteButtonEnabled = false;
        $this->showDeleteModal = true;
    }

    // 삭제 모달 닫기
    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteType = '';
        $this->deleteIds = [];
        $this->deleteCount = 0;
        $this->deleteConfirmKey = '';
        $this->deleteConfirmInput = '';
        $this->deleteButtonEnabled = false;
    }

    // 확인 키 복사
    public function copyConfirmKey()
    {
        $this->deleteConfirmInput = $this->deleteConfirmKey;
        $this->validateConfirmKey();
    }

    // 확인 키 입력 처리
    public function updatedDeleteConfirmInput($value)
    {
        $this->validateConfirmKey();
    }

    private function validateConfirmKey()
    {
        $this->deleteButtonEnabled = ($this->deleteConfirmInput === $this->deleteConfirmKey);
    }

    // 삭제 실행
    public function executeDelete()
    {
        if (! $this->deleteButtonEnabled || empty($this->deleteIds)) {
            return;
        }

        // 컨트롤러 재설정 (Livewire 요청마다 필요)
        if (! $this->controller && $this->controllerClass) {
            $this->setupController();
        }

        // 테이블 이름 가져오기
        $tableName = $this->jsonData['table']['name'] ?? 'admin_templates';

        try {
            // 삭제할 항목 개수 저장 (closeDeleteModal 전에 저장)
            $deletedCount = count($this->deleteIds);

            // 삭제하기 전에 파일 경로 수집 (이미지 등 파일이 있는 경우)
            $filesToDelete = $this->collectFilesToDelete($tableName, $this->deleteIds);

            // hookDeleting 호출 (삭제 전 처리)
            $proceedWithDelete = true;
            if ($this->controller && method_exists($this->controller, 'hookDeleting')) {
                $result = $this->controller->hookDeleting($this, $this->deleteIds, $this->deleteType);

                // false를 반환하면 삭제 중단
                if ($result === false) {
                    $proceedWithDelete = false;
                    session()->flash('error', '삭제가 취소되었습니다.');
                } elseif (is_string($result)) {
                    // 문자열을 반환하면 에러 메시지로 처리
                    $proceedWithDelete = false;
                    session()->flash('error', $result);
                }
            }

            if (! $proceedWithDelete) {
                return;
            }

            // 데이터베이스에서 삭제
            $actualDeleted = DB::table($tableName)
                ->whereIn('id', $this->deleteIds)
                ->delete();

            // 데이터베이스 삭제가 성공한 경우에만 파일 삭제
            if ($actualDeleted > 0 && !empty($filesToDelete)) {
                $this->deleteFiles($filesToDelete);
            }

            // hookDeleted 호출 (삭제 후 처리)
            if ($this->controller && method_exists($this->controller, 'hookDeleted')) {
                $this->controller->hookDeleted($this, $this->deleteIds, $actualDeleted);
            }

            // 성공 메시지 (실제 삭제된 개수 사용)
            $message = $this->deleteType === 'single'
                ? '항목이 삭제되었습니다.'
                : "{$actualDeleted}개 항목이 삭제되었습니다.";

            // 모달 닫기 (deleteCount가 0으로 리셋됨)
            $this->closeDeleteModal();

            // 세션에 성공 메시지 저장
            session()->flash('success', $message);

            // 상세 페이지에서 삭제한 경우 목록으로 리다이렉트
            if (request()->is('*/sessions/*') || str_contains(request()->url(), '/sessions/')) {
                // 세션 목록 페이지로 리다이렉트
                $this->redirect('/admin/user/sessions');
            } else {
                // 완료 이벤트 발송 (항상 메시지 포함)
                $this->dispatch('delete-completed', message: $message);
            }

        } catch (\Exception $e) {
            session()->flash('error', '삭제 중 오류가 발생했습니다: '.$e->getMessage());
        }
    }

    /**
     * 삭제할 파일 경로 수집
     * 
     * @param string $tableName
     * @param array $ids
     * @return array
     */
    protected function collectFilesToDelete($tableName, $ids)
    {
        $files = [];
        
        // JSON 설정에서 파일 필드 정보 확인
        $fileFields = $this->getFileFields();
        
        if (empty($fileFields)) {
            return $files;
        }
        
        // 삭제할 레코드들의 파일 필드 값 조회
        $records = DB::table($tableName)
            ->whereIn('id', $ids)
            ->select($fileFields)
            ->get();
        
        // 각 레코드의 파일 경로 수집
        foreach ($records as $record) {
            foreach ($fileFields as $field) {
                if (!empty($record->$field) && $record->$field !== '/images/default-avatar.png') {
                    // /storage/ 접두사 제거
                    $path = str_replace('/storage/', '', $record->$field);
                    if (!empty($path)) {
                        $files[] = $path;
                    }
                }
            }
        }
        
        return $files;
    }

    /**
     * 파일 필드 목록 가져오기
     * 
     * @return array
     */
    protected function getFileFields()
    {
        $fileFields = [];
        
        // JSON 설정에서 upload 설정이 있는지 확인
        if (isset($this->jsonData['upload']) && isset($this->jsonData['upload']['path'])) {
            // 일반적인 파일 필드명들
            $commonFileFields = ['avatar', 'image', 'photo', 'file', 'attachment', 'thumbnail'];
            
            // 테이블의 실제 컬럼 확인 (존재하는 필드만 사용)
            $tableName = $this->jsonData['table']['name'] ?? 'admin_templates';
            $columns = DB::getSchemaBuilder()->getColumnListing($tableName);
            
            foreach ($commonFileFields as $field) {
                if (in_array($field, $columns)) {
                    $fileFields[] = $field;
                }
            }
            
            // JSON 설정의 fields에서 type이 file인 필드 찾기
            if (isset($this->jsonData['create']['fields'])) {
                foreach ($this->jsonData['create']['fields'] as $fieldName => $fieldConfig) {
                    if (isset($fieldConfig['type']) && $fieldConfig['type'] === 'file') {
                        if (!in_array($fieldName, $fileFields) && in_array($fieldName, $columns)) {
                            $fileFields[] = $fieldName;
                        }
                    }
                }
            }
            
            // JSON 설정의 edit fields에서도 확인
            if (isset($this->jsonData['edit']['fields'])) {
                foreach ($this->jsonData['edit']['fields'] as $fieldName => $fieldConfig) {
                    if (isset($fieldConfig['type']) && $fieldConfig['type'] === 'file') {
                        if (!in_array($fieldName, $fileFields) && in_array($fieldName, $columns)) {
                            $fileFields[] = $fieldName;
                        }
                    }
                }
            }
        }
        
        return $fileFields;
    }

    /**
     * 파일 삭제
     * 
     * @param array $files
     * @return void
     */
    protected function deleteFiles($files)
    {
        foreach ($files as $file) {
            try {
                // public 디스크에서 파일 삭제
                if (Storage::disk('public')->exists($file)) {
                    Storage::disk('public')->delete($file);
                    \Log::info("AdminDelete: Successfully deleted file: {$file}");
                }
            } catch (\Exception $e) {
                \Log::error("AdminDelete: Failed to delete file: {$file}, Error: " . $e->getMessage());
                // 파일 삭제 실패는 무시하고 계속 진행
            }
        }
    }

    public function render()
    {
        return view('jiny-admin::template.livewire.admin-delete');
    }
}
