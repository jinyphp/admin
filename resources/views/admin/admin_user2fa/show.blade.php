{{-- 2FA 상세 정보 표시 --}}
<div class="space-y-6">
    {{-- 사용자 정보 섹션 --}}
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <h3 class="text-lg font-semibold mb-4">사용자 정보</h3>
            <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">이름</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $data['user_name'] ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">이메일</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $data['user_email'] ?? '-' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- 2FA 상태 섹션 --}}
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <h3 class="text-lg font-semibold mb-4">2FA 상태</h3>
            
            {{-- 상태 표시 --}}
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        @if($data['two_factor_status']['enabled'] ?? false)
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-lg font-medium text-green-900">2FA 활성화됨</h4>
                                <p class="text-sm text-gray-500">이 계정은 2단계 인증으로 보호되고 있습니다.</p>
                            </div>
                        @else
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.618 5.984A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-lg font-medium text-gray-900">2FA 비활성화됨</h4>
                                <p class="text-sm text-gray-500">이 계정은 2단계 인증을 사용하지 않습니다.</p>
                            </div>
                        @endif
                    </div>
                    
                    {{-- 주요 액션 버튼 --}}
                    @if(!($data['two_factor_status']['enabled'] ?? false))
                    <div>
                        <a href="{{ route('admin.system.user.2fa.edit', $data['user_id'] ?? $id ?? 1) }}"
                           class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            2FA 설정
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            {{-- 상세 정보 --}}
            <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">활성 상태</dt>
                    <dd class="mt-1 text-sm">
                        @if($data['two_factor_status']['enabled'] ?? false)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                활성
                            </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                비활성
                            </span>
                        @endif
                    </dd>
                </div>
                @if($data['two_factor_status']['confirmed_at'] ?? null)
                <div>
                    <dt class="text-sm font-medium text-gray-500">활성화 일시</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ \Carbon\Carbon::parse($data['two_factor_status']['confirmed_at'])->format('Y-m-d H:i:s') }}
                    </dd>
                </div>
                @endif
                @if($data['two_factor_status']['last_used_at'] ?? null)
                <div>
                    <dt class="text-sm font-medium text-gray-500">마지막 사용</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ \Carbon\Carbon::parse($data['two_factor_status']['last_used_at'])->format('Y-m-d H:i:s') }}
                    </dd>
                </div>
                @endif
                @if($data['two_factor_status']['backup_codes_count'] ?? 0)
                <div>
                    <dt class="text-sm font-medium text-gray-500">남은 백업 코드</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $data['two_factor_status']['backup_codes_count'] }}개
                    </dd>
                </div>
                @endif
            </dl>

            {{-- 추가 액션 버튼들 (2FA 활성화 시에만 표시) --}}
            @if($data['two_factor_status']['enabled'] ?? false)
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h4 class="text-sm font-medium text-gray-700 mb-3">추가 작업</h4>
                <div class="flex gap-3">
                    <button type="button" 
                            wire:click="callCustomAction('regenerateBackupCodes', {})"
                            class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        백업 코드 재생성
                    </button>
                    <button type="button" 
                            wire:click="callCustomAction('disableTwoFactor', {})"
                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200">
                        2FA 비활성화
                    </button>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>