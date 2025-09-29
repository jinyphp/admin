{{-- 
    Livewire 생성 컴포넌트 뷰
    
    이 파일은 데이터 생성을 위한 Livewire 컴포넌트의 메인 뷰입니다.
    실제 폼 필드는 JSON 설정의 formPath에 지정된 별도 파일을 include하여 표시합니다.
    
    구조:
    1. 알림 메시지 표시 (성공/오류)
    2. 폼 필드 include (JSON 설정의 create.formPath 경로)
    3. 액션 버튼 (취소, 저장 후 계속, 생성)
    
    Livewire 메서드:
    - save(): 데이터 저장 및 목록으로 이동
    - saveAndContinue(): 데이터 저장 후 새 생성 폼 유지
    - cancel(): 목록 페이지로 이동
--}}
<div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
    {{-- 성공 메시지 표시 영역 --}}
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    {{-- 오류 메시지 표시 영역 --}}
    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    {{-- Livewire 검증 오류 메시지 --}}
    @error('form')
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ $message }}
        </div>
    @enderror

    {{-- 생성 폼: wire:submit.prevent로 Livewire의 save 메서드 호출 (기본 제출 동작 방지) --}}
    <form wire:submit.prevent="save">
        {{-- 
            폼 필드 영역
            JSON 설정(AdminTemplates.json)의 create.formPath에 지정된 뷰 파일을 include
            예: "jiny-admin::admin.admin_templates.create" 
            실제 경로: /resources/views/admin/admin_templates/create.blade.php
        --}}
        @if(isset($jsonData['create']['formPath']) && !empty($jsonData['create']['formPath']))
            @includeIf($jsonData['create']['formPath'])
        @else
            {{-- formPath가 설정되지 않은 경우 설정 오류 메시지 표시 --}}
            @include('jiny-admin::template.components.config-error', [
                'title' => '생성 폼 설정 오류',
                'config' => 'create.formPath'
            ])
        @endif


        {{-- 액션 버튼 영역 --}}
        <div class="mt-6 flex justify-end space-x-2">
            {{-- 취소 버튼: 목록 페이지로 이동 --}}
            <button type="button"
                    wire:click="cancel"
                    class="inline-flex items-center h-8 px-3 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                취소
            </button>
            
            {{-- 저장 후 계속 버튼: JSON 설정에서 활성화된 경우에만 표시 --}}
            @if($jsonData['create']['enableContinueCreate'] ?? false)
                <button type="button"
                        wire:click="saveAndContinue"
                        class="inline-flex items-center h-8 px-3 text-xs font-medium text-white bg-green-600 border border-transparent rounded hover:bg-green-700 focus:outline-none focus:ring-1 focus:ring-green-500 transition-colors">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    저장 후 계속 생성
                </button>
            @endif
            
            {{-- 생성 버튼: 폼 제출 (기본 동작) --}}
            <button type="submit"
                    class="inline-flex items-center h-8 px-3 text-xs font-medium text-white bg-blue-600 border border-transparent rounded hover:bg-blue-700 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-colors">
                생성
            </button>
        </div>
    </form>
</div>
