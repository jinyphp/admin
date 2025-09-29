{{-- CAPTCHA 로그 생성 불가 안내 --}}
<div class="p-6 bg-yellow-50 border border-yellow-200 rounded-lg">
    <div class="flex">
        <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
        </div>
        <div class="ml-3">
            <h3 class="text-sm font-medium text-yellow-800">
                CAPTCHA 로그 수동 생성 불가
            </h3>
            <div class="mt-2 text-xs text-yellow-700">
                <p>CAPTCHA 로그는 시스템에서 자동으로 생성됩니다.</p>
                <p class="mt-1">로그인 시도 시 CAPTCHA 검증 결과가 자동으로 기록됩니다.</p>
            </div>
            <div class="mt-4">
                <a href="{{ route('admin.captcha.logs') }}" 
                   class="text-xs font-medium text-yellow-800 hover:text-yellow-600">
                    ← CAPTCHA 로그 목록으로 돌아가기
                </a>
            </div>
        </div>
    </div>
</div>