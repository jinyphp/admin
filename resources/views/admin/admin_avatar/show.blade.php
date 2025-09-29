{{-- 아바타 상세 정보 표시 --}}
<div class="space-y-6">
    {{-- 아바타 이미지 섹션 --}}
    <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                아바타 이미지
            </h3>
        </div>
        <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-5 sm:px-6">
            <div class="flex items-center space-x-4">
                @if($data->avatar && $data->avatar !== '/images/default-avatar.png')
                    <img class="h-32 w-32 rounded-full object-cover shadow-lg"
                         src="{{ $data->avatar }}"
                         alt="{{ $data->name }}의 아바타"
                         onerror="this.onerror=null; this.src='/images/default-avatar.png';">
                @else
                    <div class="h-32 w-32 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center shadow-lg">
                        <svg class="h-16 w-16 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                    </div>
                @endif
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        @if($data->avatar && $data->avatar !== '/images/default-avatar.png')
                            사용자 정의 아바타
                        @else
                            기본 아바타
                        @endif
                    </p>
                    @if($data->avatar && $data->avatar !== '/images/default-avatar.png')
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                            경로: {{ $data->avatar }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- 사용자 정보 섹션 --}}
    <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                사용자 정보
            </h3>
        </div>
        <div class="border-t border-gray-200 dark:border-gray-700">
            <dl>
                {{-- ID --}}
                <div class="bg-gray-50 dark:bg-gray-900 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        ID
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 sm:mt-0 sm:col-span-2">
                        {{ $data->id }}
                    </dd>
                </div>

                {{-- 이름 --}}
                <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        이름
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 sm:mt-0 sm:col-span-2">
                        {{ $data->name }}
                    </dd>
                </div>

                {{-- 이메일 --}}
                <div class="bg-gray-50 dark:bg-gray-900 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        이메일
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 sm:mt-0 sm:col-span-2">
                        {{ $data->email }}
                    </dd>
                </div>

                {{-- 관리자 권한 --}}
                <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        관리자 권한
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 sm:mt-0 sm:col-span-2">
                        @if($data->isAdmin)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                관리자
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                일반 사용자
                            </span>
                        @endif
                    </dd>
                </div>

                {{-- 사용자 타입 --}}
                @if($data->utype)
                    <div class="bg-gray-50 dark:bg-gray-900 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            사용자 타입
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 sm:mt-0 sm:col-span-2">
                            {{ $data->utype }}
                        </dd>
                    </div>
                @endif

                {{-- 이메일 인증 --}}
                <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        이메일 인증
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 sm:mt-0 sm:col-span-2">
                        @if($data->email_verified_at)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                인증됨
                            </span>
                            <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">
                                {{ \Carbon\Carbon::parse($data->email_verified_at)->format('Y년 m월 d일') }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                미인증
                            </span>
                        @endif
                    </dd>
                </div>

                {{-- 생성일 --}}
                <div class="bg-gray-50 dark:bg-gray-900 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        가입일
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 sm:mt-0 sm:col-span-2">
                        {{ \Carbon\Carbon::parse($data->created_at)->format('Y년 m월 d일 H:i:s') }}
                    </dd>
                </div>

                {{-- 최종 수정일 --}}
                <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        최종 수정일
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 sm:mt-0 sm:col-span-2">
                        {{ \Carbon\Carbon::parse($data->updated_at)->format('Y년 m월 d일 H:i:s') }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</div>