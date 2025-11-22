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
                    <div>
                        @if(!($data['two_factor_status']['enabled'] ?? false))
                            <a href="{{ route('admin.system.user.2fa.edit', $data['user_id'] ?? $id ?? 1) }}"
                               class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                2FA 설정
                            </a>
                        @else
                            <a href="{{ route('admin.system.user.2fa.edit', $data['user_id'] ?? $id ?? 1) }}"
                               class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                2FA 관리
                            </a>
                        @endif
                    </div>
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
                    <form action="{{ route('admin.system.user.2fa.regenerate-backup', $data['user_id'] ?? $id ?? 1) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                                onclick="return confirm('백업 코드를 재생성하시겠습니까? 기존 백업 코드는 사용할 수 없게 됩니다.')"
                                class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            백업 코드 재생성
                        </button>
                    </form>
                    <button type="button"
                            onclick="confirmDisable2FA()"
                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                        2FA 비활성화
                    </button>
                </div>
            </div>

            {{-- 2FA 비활성화 확인 모달 --}}
            <div id="disableModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
                    <div class="mt-3 text-center">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/20">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white mt-4">2FA 비활성화 확인</h3>
                        <div class="mt-2 px-7 py-3">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $data['user_name'] ?? '사용자' }}님의 2FA를 비활성화하시겠습니까?<br>
                                이 작업으로 계정 보안이 약화될 수 있습니다.
                            </p>
                        </div>
                        <div class="flex justify-center space-x-4 mt-4">
                            <button onclick="closeDisableModal()"
                                    class="h-8 px-3 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded text-xs font-medium hover:bg-gray-400 dark:hover:bg-gray-500">
                                취소
                            </button>
                            <form action="{{ route('admin.system.user.2fa.disable', $data['user_id'] ?? $id ?? 1) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="h-8 px-3 bg-red-600 text-white rounded text-xs font-medium hover:bg-red-700">
                                    비활성화
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                function confirmDisable2FA() {
                    document.getElementById('disableModal').classList.remove('hidden');
                }

                function closeDisableModal() {
                    document.getElementById('disableModal').classList.add('hidden');
                }

                // 모달 외부 클릭 시 닫기
                document.getElementById('disableModal').addEventListener('click', function(event) {
                    if (event.target === this) {
                        closeDisableModal();
                    }
                });
            </script>
            @endif
        </div>
    </div>
</div>