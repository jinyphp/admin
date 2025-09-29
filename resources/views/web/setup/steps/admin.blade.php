<div>
    <h3 class="text-lg font-medium text-gray-900 mb-4">최고 관리자 계정 생성</h3>
    <p class="text-sm text-gray-600 mb-4">
        시스템을 관리할 최고 관리자(Super Admin) 계정을 생성합니다.
    </p>
    
    <div class="mb-6 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">주의사항</h3>
                <div class="mt-1 text-sm text-yellow-700">
                    <p>이 계정은 모든 시스템 권한을 가진 슈퍼 관리자로 생성됩니다.</p>
                    <p class="mt-1">생성된 계정 정보를 안전하게 보관하세요.</p>
                </div>
            </div>
        </div>
    </div>

    <form id="admin-form" class="space-y-4">
        <div>
            <label for="name" class="block text-xs font-medium text-gray-700 mb-1">
                이름
            </label>
            <input type="text" name="name" id="name" required
                placeholder="관리자 이름"
                class="block w-full h-10 px-3 text-sm border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 text-gray-900">
        </div>

        <div>
            <label for="email" class="block text-xs font-medium text-gray-700 mb-1">
                이메일
            </label>
            <input type="email" name="email" id="email" required
                placeholder="admin@example.com"
                class="block w-full h-10 px-3 text-sm border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 text-gray-900">
        </div>

        <div>
            <label for="password" class="block text-xs font-medium text-gray-700 mb-1">
                비밀번호
            </label>
            <input type="password" name="password" id="password" required minlength="8"
                placeholder="••••••••" onkeyup="checkPasswordStrength()"
                class="block w-full h-10 px-3 text-sm border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 text-gray-900">
            
            <!-- 비밀번호 강도 표시 -->
            <div class="mt-2">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs text-gray-600">비밀번호 강도</span>
                    <span id="password-strength-text" class="text-xs font-medium"></span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-1.5">
                    <div id="password-strength-bar" class="h-1.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>
            
            <!-- 비밀번호 규칙 -->
            <div class="mt-2 p-2 bg-gray-50 rounded text-xs">
                <p class="font-medium text-gray-700 mb-1">비밀번호 규칙:</p>
                <ul class="space-y-0.5 text-gray-600">
                    <li id="rule-length" class="flex items-center">
                        <svg id="rule-length-icon" class="w-3 h-3 mr-1.5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        최소 8자 이상
                    </li>
                    <li id="rule-uppercase" class="flex items-center">
                        <svg id="rule-uppercase-icon" class="w-3 h-3 mr-1.5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        대문자 포함 (권장)
                    </li>
                    <li id="rule-number" class="flex items-center">
                        <svg id="rule-number-icon" class="w-3 h-3 mr-1.5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        숫자 포함 (권장)
                    </li>
                    <li id="rule-special" class="flex items-center">
                        <svg id="rule-special-icon" class="w-3 h-3 mr-1.5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        특수문자 포함 (권장)
                    </li>
                </ul>
            </div>
        </div>

        <div>
            <label for="password_confirmation" class="block text-xs font-medium text-gray-700 mb-1">
                비밀번호 확인
            </label>
            <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8"
                placeholder="••••••••"
                class="block w-full h-10 px-3 text-sm border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 text-gray-900">
        </div>

        <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
            <div class="flex items-start">
                <svg class="h-4 w-4 text-yellow-400 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <p class="text-xs text-yellow-800">
                    이 정보는 나중에 로그인할 때 사용됩니다. 안전한 곳에 기록해두세요.
                </p>
            </div>
        </div>
    </form>

    <div class="mt-8 flex justify-end">
        <button id="create-btn" type="button" onclick="createAdmin()"
            class="w-full sm:w-auto h-10 px-6 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200">
            관리자 생성
        </button>
    </div>
</div>

<script>
    function checkPasswordStrength() {
        const password = document.getElementById('password').value;
        let strength = 0;
        
        // 규칙 체크
        const hasLength = password.length >= 8;
        const hasUppercase = /[A-Z]/.test(password);
        const hasNumber = /[0-9]/.test(password);
        const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);
        
        // 규칙 아이콘 업데이트
        updateRuleIcon('rule-length', 'rule-length-icon', hasLength);
        updateRuleIcon('rule-uppercase', 'rule-uppercase-icon', hasUppercase);
        updateRuleIcon('rule-number', 'rule-number-icon', hasNumber);
        updateRuleIcon('rule-special', 'rule-special-icon', hasSpecial);
        
        // 강도 계산
        if (hasLength) strength += 25;
        if (hasUppercase) strength += 25;
        if (hasNumber) strength += 25;
        if (hasSpecial) strength += 25;
        
        // 강도 표시 업데이트
        const strengthBar = document.getElementById('password-strength-bar');
        const strengthText = document.getElementById('password-strength-text');
        
        strengthBar.style.width = strength + '%';
        
        if (strength === 0) {
            strengthBar.className = 'h-1.5 rounded-full transition-all duration-300 bg-gray-300';
            strengthText.textContent = '';
        } else if (strength <= 25) {
            strengthBar.className = 'h-1.5 rounded-full transition-all duration-300 bg-red-500';
            strengthText.textContent = '매우 약함';
            strengthText.className = 'text-xs font-medium text-red-600';
        } else if (strength <= 50) {
            strengthBar.className = 'h-1.5 rounded-full transition-all duration-300 bg-orange-500';
            strengthText.textContent = '약함';
            strengthText.className = 'text-xs font-medium text-orange-600';
        } else if (strength <= 75) {
            strengthBar.className = 'h-1.5 rounded-full transition-all duration-300 bg-yellow-500';
            strengthText.textContent = '보통';
            strengthText.className = 'text-xs font-medium text-yellow-600';
        } else {
            strengthBar.className = 'h-1.5 rounded-full transition-all duration-300 bg-green-500';
            strengthText.textContent = '강함';
            strengthText.className = 'text-xs font-medium text-green-600';
        }
    }
    
    function updateRuleIcon(ruleId, iconId, isMet) {
        const rule = document.getElementById(ruleId);
        const icon = document.getElementById(iconId);
        
        if (isMet) {
            icon.className = 'w-3 h-3 mr-1.5 text-green-500';
            rule.className = 'flex items-center text-green-700';
        } else {
            icon.className = 'w-3 h-3 mr-1.5 text-gray-400';
            rule.className = 'flex items-center';
        }
    }

    function createAdmin() {
        const form = document.getElementById('admin-form');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        
        // Validation
        if (!data.name || !data.email || !data.password || !data.password_confirmation) {
            showError('모든 필드를 입력해주세요.');
            return;
        }
        
        if (data.password !== data.password_confirmation) {
            showError('비밀번호가 일치하지 않습니다.');
            return;
        }
        
        if (data.password.length < 8) {
            showError('비밀번호는 최소 8자 이상이어야 합니다.');
            return;
        }
        
        const button = document.getElementById('create-btn');
        button.disabled = true;
        button.innerHTML = '생성 중...';
        
        fetch('/admin/setup/create-admin', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showSuccess('관리자 계정이 성공적으로 생성되었습니다.');
                
                // Redirect to setup page (which will show complete step)
                if (result.redirect) {
                    // Force reload to refresh the page and show complete step
                    setTimeout(() => {
                        window.location.href = result.redirect + '?step=complete';
                    }, 1500);
                } else {
                    // Fallback: reload current page
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            } else {
                showError('관리자 생성 실패: ' + result.message);
                button.disabled = false;
                button.innerHTML = '관리자 생성';
            }
        })
        .catch(error => {
            showError('관리자 생성 중 오류가 발생했습니다: ' + error.message);
            button.disabled = false;
            button.innerHTML = '관리자 생성';
        });
    }
</script>