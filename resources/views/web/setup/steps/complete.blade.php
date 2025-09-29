<div class="text-center">
    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
        <svg class="h-6 w-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
    </div>
    
    <h3 class="mt-6 text-lg font-medium text-gray-900">설정 완료!</h3>
    <p class="mt-2 text-sm text-gray-600">
        @jiny/admin 초기 설정이 성공적으로 완료되었습니다.
    </p>
    
    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-md p-4">
        <div class="text-left">
            <h4 class="text-sm font-medium text-blue-900 mb-2">다음 단계:</h4>
            <ul class="text-sm text-blue-700 space-y-1">
                <li class="flex items-start">
                    <svg class="h-5 w-5 text-blue-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 1.414L10.586 9.5H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd"/>
                    </svg>
                    생성한 관리자 계정으로 로그인하세요
                </li>
                <li class="flex items-start">
                    <svg class="h-5 w-5 text-blue-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 1.414L10.586 9.5H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd"/>
                    </svg>
                    관리자 대시보드에서 추가 설정을 완료하세요
                </li>
                <li class="flex items-start">
                    <svg class="h-5 w-5 text-blue-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 1.414L10.586 9.5H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd"/>
                    </svg>
                    필요한 관리자 모듈을 생성하세요
                </li>
            </ul>
        </div>
    </div>
    
    <div class="mt-8">
        <button id="complete-btn" type="button" onclick="completeSetup()"
            class="inline-flex items-center h-10 px-6 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200">
            관리자 로그인 페이지로 이동
            <svg class="ml-2 -mr-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>
    </div>
</div>

<script>
    function completeSetup() {
        const button = document.getElementById('complete-btn');
        button.disabled = true;
        button.innerHTML = '완료 중...';
        
        fetch('/admin/setup/complete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // 약간의 지연 후 리다이렉트 (세션 안정화)
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 500);
            } else {
                showError('설정 완료 실패: ' + data.message);
                button.disabled = false;
                button.innerHTML = '관리자 로그인 페이지로 이동';
            }
        })
        .catch(error => {
            showError('설정 완료 중 오류가 발생했습니다: ' + error.message);
            button.disabled = false;
            button.innerHTML = '관리자 로그인 페이지로 이동';
        });
    }
</script>