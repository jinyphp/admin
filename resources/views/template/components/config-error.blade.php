{{--
    재사용 가능한 설정 오류 메시지 컴포넌트
    Reusable configuration error message component
    
    Parameters:
    - $title: 오류 제목 (Error title)
    - $config: 누락된 설정 경로 (Missing configuration path)
    - $description: 추가 설명 (Optional additional description)
--}}

@props([
    'title' => '설정 오류',
    'config' => '',
    'description' => ''
])

<div class="p-6 bg-red-50 border border-red-200 rounded-lg">
    <div class="flex items-center">
        <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <div>
            <h3 class="text-lg font-semibold text-red-800">{{ $title }}</h3>
            <p class="text-sm text-red-600 mt-1">
                JSON 설정 파일에서 '{{ $config }}' 값이 누락되었거나 비어있습니다.
            </p>
            <p class="text-xs text-red-500 mt-2">
                Error: Missing or empty '{{ $config }}' configuration in JSON settings file.
            </p>
            @if($description)
                <p class="text-xs text-gray-600 mt-2">{{ $description }}</p>
            @endif
        </div>
    </div>
</div>