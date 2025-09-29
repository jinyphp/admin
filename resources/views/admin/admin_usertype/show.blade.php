{{-- 헤더 섹션 --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">사용자 등급 상세</h2>
                    <p class="mt-1 text-xs text-gray-600">등급 정보를 확인할 수 있습니다</p>
                </div>
                <div class="flex items-center space-x-3">
                    {{-- 상태 배지 --}}
                    @if($data['enable'] ?? false)
                        <span class="inline-flex items-center h-6 px-2.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            활성화
                        </span>
                    @else
                        <span class="inline-flex items-center h-6 px-2.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            비활성화
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- 기본 정보 --}}
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- 왼쪽 컬럼 --}}
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">타입 코드</label>
                        <div class="flex items-center">
                            <span class="inline-flex items-center h-8 px-3 rounded bg-blue-50 text-blue-700 text-xs font-mono font-semibold">
                                {{ $data['code'] ?? '-' }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">등급명</label>
                        <p class="text-sm font-semibold text-gray-900">{{ $data['name'] ?? '-' }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">설명</label>
                        <p class="text-xs text-gray-700">{{ $data['description'] ?? '설명이 없습니다.' }}</p>
                    </div>
                </div>

                {{-- 오른쪽 컬럼 --}}
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">권한 레벨</label>
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center">
                                <span class="text-lg font-bold text-gray-900">{{ $data['level'] ?? 0 }}</span>
                                <span class="ml-1 text-xs text-gray-500">/100</span>
                            </div>
                            {{-- 레벨 바 --}}
                            <div class="flex-1 max-w-xs">
                                <div class="w-full bg-gray-200 rounded-full h-1.5">
                                    <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ min(($data['level'] ?? 0), 100) }}%"></div>
                                </div>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            @if(($data['level'] ?? 0) >= 100)
                                최고 관리자 권한
                            @elseif(($data['level'] ?? 0) >= 50)
                                관리자 권한
                            @elseif(($data['level'] ?? 0) >= 10)
                                스태프 권한
                            @else
                                기본 권한
                            @endif
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">정렬 순서</label>
                        <p class="text-sm text-gray-900">{{ $data['pos'] ?? 0 }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">ID</label>
                        <p class="text-xs text-gray-700 font-mono">#{{ $data['id'] ?? '' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 타임스탬프 정보 --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-900">시간 정보</h3>
        </div>
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500">생성일</p>
                        <p class="text-xs text-gray-900">{{ $data['created_at'] ?? '-' }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500">수정일</p>
                        <p class="text-xs text-gray-900">{{ $data['updated_at'] ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
