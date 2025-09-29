{{-- 새 아바타 생성 폼 --}}
<div class="space-y-6">
    {{-- 기본 정보 입력 --}}
    <div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
            사용자 정보
        </h3>
        
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            {{-- 이름 --}}
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    이름 <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       wire:model="form.name" 
                       id="name"
                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       placeholder="사용자 이름을 입력하세요">
                @error('form.name')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- 이메일 --}}
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    이메일 <span class="text-red-500">*</span>
                </label>
                <input type="email" 
                       wire:model.blur="form.email" 
                       id="email"
                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       placeholder="email@example.com">
                @error('form.email')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- 비밀번호 --}}
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    비밀번호 <span class="text-red-500">*</span>
                </label>
                <input type="password" 
                       wire:model="form.password" 
                       id="password"
                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       placeholder="최소 8자 이상">
                @error('form.password')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- 비밀번호 확인 --}}
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    비밀번호 확인 <span class="text-red-500">*</span>
                </label>
                <input type="password" 
                       wire:model="form.password_confirmation" 
                       id="password_confirmation"
                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       placeholder="비밀번호를 다시 입력하세요">
                @error('form.password_confirmation')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    {{-- 아바타 이미지 업로드 --}}
    <div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
            아바타 이미지
        </h3>

        <div class="flex items-start space-x-6">
            {{-- 미리보기 --}}
            <div class="flex-shrink-0">
                @if ($photo)
                    {{-- 업로드한 이미지 미리보기 --}}
                    <img class="h-32 w-32 rounded-full object-cover border-4 border-indigo-500 shadow-lg"
                         src="{{ $photo->temporaryUrl() }}" 
                         alt="아바타 미리보기">
                    <p class="mt-2 text-xs text-center text-indigo-600 dark:text-indigo-400 font-medium">
                        미리보기
                    </p>
                @else
                    {{-- 기본 아바타 아이콘 --}}
                    <div class="h-32 w-32 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center shadow-lg">
                        <svg class="h-16 w-16 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                    </div>
                    <p class="mt-2 text-xs text-center text-gray-500 dark:text-gray-400">
                        이미지 없음
                    </p>
                @endif
            </div>

            {{-- 업로드 컨트롤 --}}
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    아바타 이미지 선택
                </label>
                
                {{-- 파일 선택 버튼 --}}
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
                        <button type="button" 
                                wire:click="$set('photo', null)"
                                class="text-xs text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                            제거
                        </button>
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

                {{-- 업로드 진행 상태 --}}
                <div wire:loading wire:target="photo">
                    <div class="flex items-center text-sm text-indigo-600 dark:text-indigo-400 mt-2">
                        <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        이미지 업로드 중...
                    </div>
                </div>

                {{-- 업로드 가이드 --}}
                <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <h4 class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">업로드 가이드</h4>
                    <ul class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
                        <li class="flex items-start">
                            <svg class="h-3 w-3 mr-1 mt-0.5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 1.414L10.586 9.5H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd"/>
                            </svg>
                            지원 형식: JPG, PNG, GIF, WEBP
                        </li>
                        <li class="flex items-start">
                            <svg class="h-3 w-3 mr-1 mt-0.5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 1.414L10.586 9.5H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd"/>
                            </svg>
                            최대 크기: 2MB
                        </li>
                        <li class="flex items-start">
                            <svg class="h-3 w-3 mr-1 mt-0.5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 1.414L10.586 9.5H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd"/>
                            </svg>
                            권장 크기: 정사각형 이미지 (예: 500x500px)
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- 권한 설정 --}}
    <div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
            권한 설정
        </h3>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            {{-- 관리자 권한 --}}
            <div>
                <label for="isAdmin" class="flex items-center">
                    <input type="checkbox" 
                           wire:model="form.isAdmin" 
                           id="isAdmin"
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                        관리자 권한 부여
                    </span>
                </label>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    관리자 페이지에 접근할 수 있는 권한을 부여합니다.
                </p>
            </div>

            {{-- 사용자 타입 --}}
            @if(isset($userTypeOptions) && count($userTypeOptions) > 0)
            <div>
                <label for="utype" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    사용자 타입
                </label>
                <select wire:model="form.utype" 
                        id="utype"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">선택하세요</option>
                    @foreach($userTypeOptions as $code => $name)
                        <option value="{{ $code }}">{{ $name }}</option>
                    @endforeach
                </select>
                @error('form.utype')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            @endif
        </div>
    </div>
</div>