{{-- 
    Livewire 편집 컴포넌트 뷰
    
    이 파일은 데이터 편집을 위한 Livewire 컴포넌트의 메인 뷰입니다.
    실제 폼 필드는 JSON 설정의 formPath에 지정된 별도 파일을 include하여 표시합니다.
    
    구조:
    1. 알림 메시지 표시 (성공/오류)
    2. 폼 필드 include (JSON 설정의 edit.formPath 경로)
    3. 액션 버튼 (삭제, 취소, 저장)
    
    Livewire 메서드:
    - save(): 변경사항 저장
    - requestDelete(): 삭제 확인 모달 표시
    - cancel(): 목록 페이지로 이동
--}}
<div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
    {{-- 성공 메시지 표시 영역 --}}
    @if (session()->has('success'))
        <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 text-sm rounded">
            {{ session('success') }}
        </div>
    @endif

    {{-- 오류 메시지 표시 영역 --}}
    @if (session()->has('error'))
        <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 text-sm rounded">
            {{ session('error') }}
        </div>
    @endif
    
    {{-- 폼 검증 오류 표시 --}}
    @error('form')
        <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 text-sm rounded">
            {{ $message }}
        </div>
    @enderror

    {{-- 편집 폼: wire:submit으로 Livewire의 save 메서드 호출 --}}
    <form wire:submit="save">
        {{-- 
            폼 필드 영역
            JSON 설정(AdminTemplates.json)의 edit.formPath에 지정된 뷰 파일을 include
            예: "jiny-admin::admin.admin_templates.edit"
            실제 경로: /resources/views/admin/admin_templates/edit.blade.php
        --}}
        @if(isset($jsonData['edit']['formPath']) && !empty($jsonData['edit']['formPath']))
            @includeIf($jsonData['edit']['formPath'])
        @else
            {{-- formPath가 설정되지 않은 경우 설정 오류 메시지 표시 --}}
            @include('jiny-admin::template.components.config-error', [
                'title' => '수정 폼 설정 오류',
                'config' => 'edit.formPath'
            ])
        @endif


        {{-- 액션 버튼 영역 --}}
        <div class="mt-6 flex justify-between">
            {{-- 삭제 버튼: 설정에서 활성화된 경우에만 표시 (왼쪽 정렬) --}}
            @if($settings['enableDelete'] ?? true)
                <button type="button"
                        wire:click="requestDelete"
                        class="inline-flex items-center h-8 px-3 text-xs font-medium text-white bg-red-600 border border-transparent rounded hover:bg-red-700 focus:outline-none focus:ring-1 focus:ring-red-500 transition-colors">
                    삭제
                </button>
            @else
                {{-- 삭제 버튼이 비활성화된 경우 빈 div로 레이아웃 유지 --}}
                <div></div>
            @endif
            
            {{-- 취소/저장 버튼 그룹 (오른쪽 정렬) --}}
            <div class="flex space-x-2">
                {{-- 취소 버튼: 목록 페이지로 이동 --}}
                <button type="button"
                        wire:click="cancel"
                        class="inline-flex items-center h-8 px-3 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    취소
                </button>
                
                {{-- 저장 버튼: 폼 제출 (기본 동작) --}}
                <button type="submit"
                        class="inline-flex items-center h-8 px-3 text-xs font-medium text-white bg-blue-600 border border-transparent rounded hover:bg-blue-700 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-colors">
                    저장
                </button>
            </div>
        </div>
    </form>
</div>
