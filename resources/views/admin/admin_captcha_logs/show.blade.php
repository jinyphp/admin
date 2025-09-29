{{-- CAPTCHA 로그 상세 정보 페이지 --}}

{{-- 헤더 섹션 --}}
<div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">CAPTCHA 로그 상세</h2>
                <p class="mt-1 text-xs text-gray-600">CAPTCHA 인증 시도에 대한 상세 정보</p>
            </div>
            <div class="flex items-center space-x-3">
                {{-- 상태 뱃지 --}}
                @if($data['action'] === 'captcha_success')
                    <span class="inline-flex items-center h-6 px-2.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        성공
                    </span>
                @elseif($data['action'] === 'captcha_failed')
                    <span class="inline-flex items-center h-6 px-2.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        실패
                    </span>
                @else
                    <span class="inline-flex items-center h-6 px-2.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        미입력
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
                    <label class="block text-xs font-medium text-gray-500 mb-1">로그 ID</label>
                    <p class="text-sm font-mono text-gray-900">#{{ $data['id'] ?? '-' }}</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">시도 시간</label>
                    <p class="text-sm text-gray-900">
                        @if($data['logged_at'] ?? false)
                            {{ \Carbon\Carbon::parse($data['logged_at'])->format('Y년 m월 d일 H:i:s') }}
                            <span class="text-xs text-gray-500 block">{{ \Carbon\Carbon::parse($data['logged_at'])->diffForHumans() }}</span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </p>
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
                    <label class="block text-xs font-medium text-gray-500 mb-1">사용자</label>
                    @if($data['user_id'] ?? false)
                        <a href="{{ route('admin.users.show', $data['user_id']) }}" 
                           class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            사용자 보기
                        </a>
                    @else
                        <span class="text-sm text-gray-400">비회원</span>
                    @endif
                </div>
            </div>

            {{-- 오른쪽 컬럼 --}}
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">IP 주소</label>
                    <p class="text-sm font-mono text-gray-900">{{ $data['ip_address'] ?? '-' }}</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">브라우저</label>
                    <p class="text-sm text-gray-900">{{ $data['browser'] ?? '-' }}</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">플랫폼</label>
                    <p class="text-sm text-gray-900">{{ $data['platform'] ?? '-' }}</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Device</label>
                    <p class="text-sm text-gray-900">{{ $data['device'] ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- CAPTCHA 상세 정보 섹션 --}}
<div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-sm font-semibold text-gray-900">CAPTCHA 검증 정보</h3>
    </div>
    <div class="px-6 py-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">검증 점수</label>
                @php
                    $details = is_string($data['details'] ?? null) ? json_decode($data['details'], true) : ($data['details'] ?? []);
                    $score = $details['score'] ?? null;
                @endphp
                @if($score !== null)
                    <div class="flex items-center space-x-2">
                        <span class="text-lg font-bold {{ $score >= 0.5 ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($score, 2) }}
                        </span>
                        <span class="text-xs text-gray-500">/ 1.00</span>
                        @if($score >= 0.7)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                높음
                            </span>
                        @elseif($score >= 0.5)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                보통
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                낮음
                            </span>
                        @endif
                    </div>
                @else
                    <span class="text-sm text-gray-400">점수 없음</span>
                @endif
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">검증 결과</label>
                <p class="text-sm text-gray-900">
                    @if($data['action'] === 'captcha_success')
                        <span class="text-green-600 font-medium">✓ 검증 성공</span>
                    @elseif($data['action'] === 'captcha_failed')
                        <span class="text-red-600 font-medium">✗ 검증 실패</span>
                    @else
                        <span class="text-yellow-600 font-medium">⚠ CAPTCHA 미입력</span>
                    @endif
                </p>
            </div>
        </div>

        {{-- 오류 메시지 --}}
        @if(isset($details['error']) && $details['error'])
        <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-4 w-4 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-xs font-medium text-red-800">오류 메시지</h3>
                    <p class="mt-1 text-xs text-red-700">{{ $details['error'] }}</p>
                </div>
            </div>
        </div>
        @endif

        {{-- 추가 세부 정보 --}}
        @if(isset($details['challenge_ts']) || isset($details['hostname']))
        <div class="mt-4 pt-4 border-t border-gray-200">
            <h4 class="text-xs font-medium text-gray-700 mb-3">추가 정보</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if(isset($details['challenge_ts']))
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Challenge 시간</label>
                    <p class="text-xs text-gray-700">{{ $details['challenge_ts'] }}</p>
                </div>
                @endif
                @if(isset($details['hostname']))
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Hostname</label>
                    <p class="text-xs text-gray-700">{{ $details['hostname'] }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

{{-- User Agent 정보 섹션 --}}
<div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-sm font-semibold text-gray-900">클라이언트 정보</h3>
    </div>
    <div class="px-6 py-4">
        <div class="space-y-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">User Agent</label>
                <p class="text-xs font-mono text-gray-700 break-all bg-gray-50 p-2 rounded">
                    {{ $data['user_agent'] ?? '-' }}
                </p>
            </div>
            
            @if($data['referer'] ?? false)
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Referer</label>
                <p class="text-xs text-gray-700">{{ $data['referer'] }}</p>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- 액션 버튼 --}}
<div class="flex items-center justify-between">
    <a href="{{ route('admin.captcha.logs') }}" 
       class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        목록으로 돌아가기
    </a>
    
    @if($data['user_id'] ?? false)
    <a href="{{ route('admin.users.show', $data['user_id']) }}" 
       class="inline-flex items-center px-3 py-1.5 border border-blue-300 text-xs font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
        </svg>
        사용자 정보 보기
    </a>
    @endif
</div>