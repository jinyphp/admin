{{-- 사용자 상세 정보 페이지 --}}

{{-- 아바타 섹션 --}}
<div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900">아바타</h3>
            <div class="flex items-center space-x-2">
                <a href="{{ route('admin.avatar') }}" 
                   class="inline-flex items-center text-xs text-blue-600 hover:text-blue-800 hover:underline">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                    </svg>
                    아바타 목록
                </a>
                <a href="{{ route('admin.avatar.edit', $data['id']) }}" 
                   class="inline-flex items-center text-xs text-blue-600 hover:text-blue-800 hover:underline">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    아바타 수정
                </a>
            </div>
        </div>
    </div>
    <div class="p-6">
        <div class="flex items-center space-x-6">
            {{-- 아바타 이미지 --}}
            <div class="flex-shrink-0">
                @if($data['avatar'] ?? false)
                    <img class="h-24 w-24 rounded-full object-cover border-2 border-gray-200" 
                         src="{{ asset('storage/' . $data['avatar']) }}" 
                         alt="{{ $data['name'] ?? '사용자' }} 아바타">
                @else
                    <div class="h-24 w-24 rounded-full bg-gray-200 flex items-center justify-center">
                        <svg class="h-12 w-12 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                @endif
            </div>
            
            {{-- 아바타 정보 --}}
            <div class="flex-1">
                <div class="text-sm">
                    <p class="text-gray-900 font-medium">{{ $data['name'] ?? '사용자' }}</p>
                    <p class="text-gray-500">{{ $data['email'] ?? '' }}</p>
                    @if($data['avatar'] ?? false)
                        <p class="mt-2 text-xs text-gray-500">
                            아바타 경로: <span class="font-mono bg-gray-100 px-1 py-0.5 rounded">{{ $data['avatar'] }}</span>
                        </p>
                    @else
                        <p class="mt-2 text-xs text-gray-400">아바타가 설정되지 않았습니다</p>
                    @endif
                </div>
                
                {{-- 아바타 관리 버튼 --}}
                <div class="mt-4 flex space-x-2">
                    @if($data['avatar'] ?? false)
                        <button wire:click="hookCustom('RemoveAvatar', { id: {{ $data['id'] }} })"
                                class="inline-flex items-center px-3 py-1.5 border border-red-300 text-xs font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            아바타 제거
                        </button>
                    @endif
                    
                    <a href="{{ route('admin.avatar.edit', $data['id']) }}"
                       class="inline-flex items-center px-3 py-1.5 border border-green-300 text-xs font-medium rounded-md text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        아바타 수정
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 헤더 섹션 --}}
<div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">사용자 상세 정보</h2>
                <p class="mt-1 text-xs text-gray-600">사용자의 계정 정보 및 활동 내역을 확인할 수 있습니다</p>
            </div>
            <div class="flex items-center space-x-3">
                {{-- 이메일 인증 상태 --}}
                @if($data['email_verified_at'] ?? false)
                    <span class="inline-flex items-center h-6 px-2.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        이메일 인증됨
                    </span>
                @else
                    <span class="inline-flex items-center h-6 px-2.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        이메일 미인증
                    </span>
                @endif
                
                {{-- 관리자 권한 상태 --}}
                @if($data['isAdmin'] ?? false)
                    <span class="inline-flex items-center h-6 px-2.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                        <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        관리자
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- 기본 정보 --}}
    <div class="px-6 py-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- 왼쪽 컬럼 --}}
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">사용자 ID</label>
                    <p class="text-sm font-mono text-gray-900">#{{ $data['id'] ?? '-' }}</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">이름</label>
                    <p class="text-sm font-semibold text-gray-900">{{ $data['name'] ?? '-' }}</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">이메일</label>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                        </svg>
                        <p class="text-sm text-gray-900">{{ $data['email'] ?? '-' }}</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">이메일 인증일</label>
                    <p class="text-sm text-gray-900">
                        @if($data['email_verified_at'] ?? false)
                            {{ \Carbon\Carbon::parse($data['email_verified_at'])->format('Y년 m월 d일 H:i') }}
                        @else
                            <span class="text-gray-400">미인증</span>
                        @endif
                    </p>
                </div>
            </div>

            {{-- 오른쪽 컬럼 --}}
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">
                        <a href="{{ route('admin.user.type') }}" class="inline-flex items-center text-gray-500 hover:text-blue-600 transition-colors">
                            사용자 유형
                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                        </a>
                    </label>
                    <div class="flex items-center space-x-2">
                        @if(isset($data['utype_name']) && $data['utype_name'])
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $data['utype_name'] }}
                            </span>
                            @if($data['utype'])
                                <span class="text-xs text-gray-500">({{ $data['utype'] }})</span>
                            @endif
                        @elseif($data['utype'] ?? false)
                            <span class="text-sm text-gray-900">{{ $data['utype'] }}</span>
                        @else
                            <span class="text-sm text-gray-400">-</span>
                        @endif
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">관리자 권한</label>
                    <p class="text-sm text-gray-900">
                        @if($data['isAdmin'] ?? false)
                            <span class="text-purple-600 font-medium">관리자</span>
                        @else
                            <span class="text-gray-600">일반 사용자</span>
                        @endif
                    </p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">
                        <a href="{{ route('admin.user.logs') }}" class="inline-flex items-center text-gray-500 hover:text-blue-600 transition-colors">
                            마지막 로그인
                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                        </a>
                    </label>
                    <div class="text-sm text-gray-900">
                        @if($data['last_login_at'] ?? false)
                            <a href="{{ route('admin.user.logs', ['user_id' => $data['id']]) }}" 
                               class="inline-flex items-center text-blue-600 hover:text-blue-800 hover:underline transition-colors">
                                {{ \Carbon\Carbon::parse($data['last_login_at'])->format('Y년 m월 d일 H:i') }}
                                <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </a>
                            <span class="text-xs text-gray-500 ml-1">({{ \Carbon\Carbon::parse($data['last_login_at'])->diffForHumans() }})</span>
                        @else
                            <span class="text-gray-400">기록 없음</span>
                        @endif
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">로그인 횟수</label>
                    <div class="flex items-center space-x-2">
                        @if(isset($data['login_count']) && $data['login_count'] > 0)
                            <span class="text-sm font-semibold text-gray-900">{{ $data['login_count'] }}</span>
                            <span class="text-xs text-gray-500">회</span>
                            @if($data['login_count'] >= 100)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    활발한 사용자
                                </span>
                            @elseif($data['login_count'] >= 50)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    정기 사용자
                                </span>
                            @endif
                        @else
                            <span class="text-sm text-gray-400">0회</span>
                        @endif
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">계정 상태</label>
                    <div class="flex items-center">
                        @if($data['deleted_at'] ?? false)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                삭제됨
                            </span>
                        @elseif($data['is_active'] ?? true)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                활성
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                비활성
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- 관리자 작업 버튼들 --}}
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        <p class="text-xs font-medium text-gray-500 mb-3">관리자 작업</p>
        <div class="flex flex-wrap gap-2">
            {{-- 이메일 인증 관리 --}}
            @if($data['email_verified_at'] ?? false)
                <button wire:click="hookCustom('EmailUnverify', { id: {{ $data['id'] }} })"
                        class="inline-flex items-center px-3 py-1.5 border border-yellow-300 text-xs font-medium rounded-md text-yellow-700 bg-yellow-50 hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    이메일 인증 취소
                </button>
            @else
                <button wire:click="hookCustom('EmailVerify', { id: {{ $data['id'] }} })"
                        class="inline-flex items-center px-3 py-1.5 border border-green-300 text-xs font-medium rounded-md text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    이메일 강제 인증
                </button>
            @endif
            
            {{-- 계정 상태 관리 --}}
            @if($data['is_active'] ?? true)
                <button wire:click="hookCustom('AccountDeactivate', { id: {{ $data['id'] }} })"
                        class="inline-flex items-center px-3 py-1.5 border border-red-300 text-xs font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                    </svg>
                    계정 비활성화
                </button>
            @else
                <button wire:click="hookCustom('AccountActivate', { id: {{ $data['id'] }} })"
                        class="inline-flex items-center px-3 py-1.5 border border-green-300 text-xs font-medium rounded-md text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    계정 활성화
                </button>
            @endif
            
            {{-- 사용자 정보 수정 --}}
            <a href="{{ route('admin.users.edit', $data['id']) }}"
               class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                사용자 정보 수정
            </a>
            
            {{-- 활동 로그 보기 --}}
            <a href="{{ route('admin.user.logs', ['user_id' => $data['id']]) }}"
               class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                활동 로그
            </a>
        </div>
    </div>
</div>

{{-- 추가 정보 섹션 --}}
<div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-sm font-semibold text-gray-900">추가 정보</h3>
    </div>
    <div class="px-6 py-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- 타임스탬프 정보 --}}
            <div class="space-y-3">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500">가입일</p>
                        <p class="text-xs text-gray-900">
                            @if($data['created_at'] ?? false)
                                {{ \Carbon\Carbon::parse($data['created_at'])->format('Y년 m월 d일 H:i:s') }}
                            @else
                                -
                            @endif
                        </p>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500">최근 수정일</p>
                        <p class="text-xs text-gray-900">
                            @if($data['updated_at'] ?? false)
                                {{ \Carbon\Carbon::parse($data['updated_at'])->format('Y년 m월 d일 H:i:s') }}
                            @else
                                -
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            {{-- 보안 정보 --}}
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">2차 인증 (2FA)</label>
                    <div class="flex items-center space-x-2">
                        @if($data['two_factor_enabled'] ?? false)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                활성화
                            </span>
                            @if($data['last_2fa_used_at'] ?? false)
                                <span class="text-xs text-gray-500">
                                    마지막 사용: {{ \Carbon\Carbon::parse($data['last_2fa_used_at'])->diffForHumans() }}
                                </span>
                            @endif
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                비활성화
                            </span>
                        @endif
                        <a href="{{ route('admin.user.2fa.edit', $data['id']) }}" 
                           class="text-xs text-blue-600 hover:text-blue-800 hover:underline">
                            2FA 관리
                        </a>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">2FA 설정일</label>
                    <p class="text-xs text-gray-600">
                        @if($data['two_factor_confirmed_at'] ?? false)
                            {{ \Carbon\Carbon::parse($data['two_factor_confirmed_at'])->format('Y년 m월 d일 H:i') }}
                        @else
                            <span class="text-gray-400">미설정</span>
                        @endif
                    </p>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- 비밀번호 보안 관리 섹션 --}}
<div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-sm font-semibold text-gray-900">비밀번호 보안 관리</h3>
    </div>
    <div class="px-6 py-4">
        <div class="space-y-4">
            {{-- 비밀번호 변경 및 만료 정보 --}}
            <div class="bg-blue-50 rounded-lg p-4 mb-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">마지막 비밀번호 변경</label>
                        <p class="text-sm text-gray-900">
                            @if($data['password_changed_at'] ?? false)
                                {{ \Carbon\Carbon::parse($data['password_changed_at'])->format('Y-m-d H:i') }}
                                <span class="text-xs text-gray-500 block">{{ \Carbon\Carbon::parse($data['password_changed_at'])->diffForHumans() }}</span>
                            @else
                                <span class="text-gray-400">변경 기록 없음</span>
                            @endif
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">패스워드 만료 상태</label>
                        <div class="">
                            @if($data['password_expires_at'] ?? false)
                                @php
                                    $expiresAt = \Carbon\Carbon::parse($data['password_expires_at']);
                                    $daysRemaining = now()->diffInDays($expiresAt, false);
                                    $isExpired = $expiresAt->isPast();
                                @endphp
                                
                                @if($isExpired)
                                    <div class="flex items-center space-x-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                            만료됨
                                        </span>
                                    </div>
                                    <p class="text-xs text-red-600 mt-1">
                                        {{ abs($daysRemaining) }}일 전 만료되었습니다
                                    </p>
                                @elseif($daysRemaining <= 7)
                                    <div class="flex items-center space-x-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            만료 임박
                                        </span>
                                    </div>
                                    <p class="text-xs text-yellow-700 mt-1">
                                        {{ $daysRemaining }}일 후 만료 ({{ $expiresAt->format('Y-m-d') }})
                                    </p>
                                @else
                                    <p class="text-sm text-gray-900">
                                        {{ $expiresAt->format('Y-m-d H:i') }}
                                        <span class="text-xs text-gray-500 block">{{ $daysRemaining }}일 후 만료</span>
                                    </p>
                                @endif
                            @else
                                <span class="text-sm text-gray-400">만료 기한 설정 안됨</span>
                            @endif
                            
                            @if($data['password_must_change'] ?? false)
                                <div class="mt-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        다음 로그인 시 변경 필요
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                {{-- 패스워드 만료 일수 설정 --}}
                @if($data['password_expiry_days'] ?? false)
                <div class="mt-3 pt-3 border-t border-blue-100">
                    <span class="text-xs text-gray-600">패스워드 만료 기간: </span>
                    <span class="text-xs font-semibold text-gray-900">{{ $data['password_expiry_days'] }}일</span>
                </div>
                @endif
            </div>
            
            {{-- 로그인 실패 정보 --}}
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">로그인 실패 횟수</label>
                        <p class="text-lg font-semibold {{ ($data['failed_login_attempts'] ?? 0) > 3 ? 'text-red-600' : 'text-gray-900' }}">
                            {{ $data['failed_login_attempts'] ?? 0 }}회
                        </p>
                        @if(($data['failed_login_attempts'] ?? 0) >= 5)
                            <p class="text-xs text-red-600 mt-1">⚠️ 최대 실패 횟수 도달</p>
                        @endif
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">계정 잠금 상태</label>
                        @if($data['account_locked_until'] ?? false)
                            @if(\Carbon\Carbon::parse($data['account_locked_until'])->isFuture())
                                <div class="flex items-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                        </svg>
                                        잠김
                                    </span>
                                </div>
                                <p class="text-xs text-red-600 mt-1">
                                    {{ \Carbon\Carbon::parse($data['account_locked_until'])->diffForHumans() }}까지
                                </p>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    잠금 해제 대기
                                </span>
                            @endif
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2H7V7a3 3 0 016 0v2a1 1 0 102 0V7a5 5 0 00-5-5z"/>
                                </svg>
                                정상
                            </span>
                        @endif
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">비밀번호 변경 강제</label>
                        @if($data['force_password_change'] ?? false)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                필요함
                            </span>
                        @else
                            <span class="text-sm text-gray-600">불필요</span>
                        @endif
                    </div>
                </div>
            </div>
            
            {{-- 관리자 작업 버튼들 --}}
            <div class="border-t pt-4">
                <p class="text-xs font-medium text-gray-500 mb-3">관리자 작업</p>
                <div class="flex flex-wrap gap-2">
                    {{-- 로그인 실패 횟수 초기화 --}}
                    @if(($data['failed_login_attempts'] ?? 0) > 0)
                        <button wire:click="hookCustom('PasswordReset', ['id' => {{ $data['id'] }}, 'action' => 'reset_attempts'])"
                                class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            실패 횟수 초기화
                        </button>
                    @endif
                    
                    {{-- 계정 잠금 해제 --}}
                    @if(($data['account_locked_until'] ?? false) && \Carbon\Carbon::parse($data['account_locked_until'])->isFuture())
                        <button wire:click="hookCustom('PasswordReset', ['id' => {{ $data['id'] }}, 'action' => 'unlock_account'])"
                                class="inline-flex items-center px-3 py-1.5 border border-red-300 text-xs font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                            </svg>
                            계정 잠금 해제
                        </button>
                    @endif
                    
                    {{-- 비밀번호 변경 강제 상태에 따른 버튼 표시 --}}
                    @if($data['force_password_change'] ?? false)
                        {{-- 강제 설정 해제 버튼 --}}
                        <button wire:click="hookCustom('PasswordResetCancel', { id: {{ $data['id'] }} })"
                                class="inline-flex items-center px-3 py-1.5 border border-green-300 text-xs font-medium rounded-md text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            비밀번호 강제 해제
                        </button>
                    @else
                        {{-- 강제 설정 버튼 --}}
                        <button wire:click="hookCustom('PasswordResetForce', { id: {{ $data['id'] }} })"
                                class="inline-flex items-center px-3 py-1.5 border border-orange-300 text-xs font-medium rounded-md text-orange-700 bg-orange-50 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                            </svg>
                            비밀번호 변경 강제
                        </button>
                    @endif
                    
                    {{-- 비밀번호 로그 보기 --}}
                    <a href="{{ route('admin.user.password.logs', ['email' => $data['email']]) }}"
                       class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        비밀번호 시도 로그
                    </a>
                    
                    {{-- 패스워드 관리 페이지 --}}
                    <a href="{{ route('admin.user.password', ['user_id' => $data['id']]) }}"
                       class="inline-flex items-center px-3 py-1.5 border border-blue-300 text-xs font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                        패스워드 관리
                    </a>
                    
                    {{-- 패스워드 만료 연장 버튼 --}}
                    @if($data['password_expires_at'] ?? false)
                        <button wire:click="hookCustom('PasswordExpiryExtend', { id: {{ $data['id'] }} })"
                                class="inline-flex items-center px-3 py-1.5 border border-indigo-300 text-xs font-medium rounded-md text-indigo-700 bg-indigo-50 hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            패스워드 만료 연장
                        </button>
                    @endif
                </div>
                
                {{-- 경고 메시지 --}}
                @if(($data['failed_login_attempts'] ?? 0) >= 3)
                <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-4 w-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-xs text-yellow-800">
                                이 사용자는 로그인 실패가 {{ $data['failed_login_attempts'] }}회 발생했습니다.
                                @if($data['failed_login_attempts'] >= 5)
                                    계정이 일시적으로 잠길 수 있습니다.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- 활동 로그 섹션 (선택적) --}}
@if(isset($logs) && count($logs) > 0)
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-sm font-semibold text-gray-900">최근 활동 로그</h3>
    </div>
    <div class="px-6 py-4">
        <div class="space-y-3">
            @foreach($logs->take(5) as $log)
            <div class="flex items-start space-x-3 pb-3 border-b border-gray-100 last:border-0">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-xs text-gray-900">{{ $log->action }}</p>
                    <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif