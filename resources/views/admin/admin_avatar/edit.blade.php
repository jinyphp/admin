{{-- 사용자 정보 표시 (읽기 전용) --}}
<div class="grid grid-cols-1 gap-6 sm:grid-cols-2 pb-6 border-b border-gray-200 dark:border-gray-700">
    {{-- 이름 --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            이름
        </label>
        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
            {{ $form['name'] ?? '-' }}
        </p>
    </div>

    {{-- 이메일 --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            이메일
        </label>
        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
            {{ $form['email'] ?? '-' }}
        </p>
    </div>

    {{-- 관리자 권한 --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            관리자 권한
        </label>
        <p class="mt-1">
            @if (($form['isAdmin'] ?? false) == 1)
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                    관리자
                </span>
            @else
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                    일반 사용자
                </span>
            @endif
        </p>
    </div>

    {{-- 가입일 --}}
    @if (isset($form['created_at']))
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                가입일
            </label>
            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ \Carbon\Carbon::parse($form['created_at'])->format('Y년 m월 d일 H:i') }}
            </p>
        </div>
    @endif

    {{-- 최종 수정일 --}}
    @if (isset($form['updated_at']))
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                최종 수정일
            </label>
            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ \Carbon\Carbon::parse($form['updated_at'])->format('Y년 m월 d일 H:i') }}
            </p>
        </div>
    @endif
</div>

{{-- 아바타 이미지 섹션 --}}
<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">
        아바타 이미지
    </label>

    <div class="flex items-start space-x-6">
        {{-- 현재 아바타 미리보기 --}}
        <div class="flex-shrink-0">
            @if ($photo)
                {{-- 새로 업로드한 이미지 미리보기 --}}
                <img class="h-32 w-32 rounded-full object-cover border-4 border-indigo-500 shadow-lg"
                    src="{{ $photo->temporaryUrl() }}" alt="새 아바타 미리보기">
                <p class="mt-2 text-xs text-center text-indigo-600 dark:text-indigo-400 font-medium">
                    새 이미지
                </p>
            @elseif($form['avatar'] ?? false)
                {{-- 기존 아바타 이미지 --}}
                <img class="h-32 w-32 rounded-full object-cover border-2 border-gray-200 dark:border-gray-600 shadow-lg"
                    src="{{ $form['avatar'] }}" alt="현재 아바타"
                    onerror="this.onerror=null; this.src='/images/default-avatar.png';">
                <p class="mt-2 text-xs text-center text-gray-500 dark:text-gray-400">
                    현재 이미지
                </p>
            @else
                {{-- 기본 아바타 아이콘 --}}
                <div
                    class="h-32 w-32 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center shadow-lg">
                    <svg class="h-16 w-16 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                    </svg>
                </div>
                <p class="mt-2 text-xs text-center text-gray-500 dark:text-gray-400">
                    기본 이미지
                </p>
            @endif
        </div>

        {{-- 업로드 섹션 --}}
        <div class="flex-1">
            {{-- 파일 업로드 입력 --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    새 아바타 이미지 업로드
                </label>
                
                {{-- 커스텀 파일 선택 버튼 --}}
                <div class="flex items-center space-x-3">
                    <label for="photo" class="cursor-pointer">
                        <span class="inline-flex items-center h-8 px-3 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-colors">
                            <svg class="h-3 w-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            파일 선택
                        </span>
                        <input type="file" 
                               wire:model="photo" 
                               id="photo" 
                               accept="image/*"
                               class="sr-only">
                    </label>
                    
                    {{-- 선택된 파일명 표시 --}}
                    @if($photo)
                        <span class="text-xs text-gray-600 dark:text-gray-400">
                            {{ $photo->getClientOriginalName() }}
                        </span>
                    @else
                        <span class="text-xs text-gray-500 dark:text-gray-500">
                            선택된 파일 없음
                        </span>
                    @endif
                </div>

                {{-- 에러 메시지 --}}
                @error('photo')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- 업로드 진행 상태 --}}
            <div wire:loading wire:target="photo">
                <div class="flex items-center text-sm text-indigo-600 dark:text-indigo-400">
                    <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    이미지 업로드 중...
                </div>
            </div>

            {{-- 파일 정보 및 제한사항 --}}
            <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <h4 class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">업로드 가이드</h4>
                <ul class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
                    <li class="flex items-start">
                        <svg class="h-3 w-3 mr-1 mt-0.5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 1.414L10.586 9.5H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                        지원 형식: JPG, PNG, GIF, WEBP
                    </li>
                    <li class="flex items-start">
                        <svg class="h-3 w-3 mr-1 mt-0.5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 1.414L10.586 9.5H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                        최대 크기: 5MB
                    </li>
                    <li class="flex items-start">
                        <svg class="h-3 w-3 mr-1 mt-0.5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 1.414L10.586 9.5H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                        권장 크기: 정사각형 이미지 (예: 500x500px)
                    </li>
                </ul>
            </div>

            {{-- 이미지 제거 버튼 --}}
            @if (($form['avatar'] ?? false) && $form['avatar'] !== '/images/default-avatar.png')
                <button type="button" wire:click="removeAvatar"
                    class="mt-4 inline-flex items-center h-8 px-3 text-xs font-medium text-red-600 bg-white dark:bg-gray-800 border border-red-300 dark:border-red-600 rounded hover:bg-red-50 dark:hover:bg-red-900 focus:outline-none focus:ring-1 focus:ring-red-500 transition-colors">
                    <svg class="h-3 w-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    제거
                </button>
            @endif
        </div>
    </div>
</div>
