<div>
    <h3 class="text-lg font-medium text-gray-900 mb-4">추가 설정 (선택사항)</h3>
    <p class="text-sm text-gray-600 mb-6">
        관리자 시스템의 추가 설정을 구성합니다. 나중에 변경할 수 있습니다.
    </p>

    <form id="settings-form" class="space-y-6">
        <!-- Site Settings -->
        <div class="bg-gray-50 rounded-lg p-4">
            <h4 class="text-sm font-medium text-gray-900 mb-4">사이트 설정</h4>
            
            <div class="space-y-4">
                <div>
                    <label for="site_name" class="block text-xs font-medium text-gray-700 mb-1">
                        사이트 이름
                    </label>
                    <input type="text" name="site_name" id="site_name" placeholder="My Admin Site"
                        class="block w-full h-10 px-3 text-sm border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 text-gray-900">
                </div>

                <div>
                    <label for="site_description" class="block text-xs font-medium text-gray-700 mb-1">
                        사이트 설명
                    </label>
                    <textarea name="site_description" id="site_description" rows="3" 
                        placeholder="관리자 시스템입니다."
                        class="block w-full px-3 py-2 text-sm border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 text-gray-900"></textarea>
                </div>

                <div>
                    <label for="admin_prefix" class="block text-xs font-medium text-gray-700 mb-1">
                        관리자 URL 경로
                    </label>
                    <input type="text" name="admin_prefix" id="admin_prefix" value="admin" placeholder="admin"
                        class="block w-full h-10 px-3 text-sm border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 text-gray-900">
                    <p class="mt-1 text-xs text-gray-500">관리자 페이지 접근 경로 (예: /admin)</p>
                </div>
            </div>
        </div>

        <!-- Security Settings -->
        <div class="bg-gray-50 rounded-lg p-4">
            <h4 class="text-sm font-medium text-gray-900 mb-4">보안 설정</h4>
            
            <div class="space-y-4">
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input id="enable_2fa" name="enable_2fa" type="checkbox" value="1"
                            class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="enable_2fa" class="font-medium text-gray-700">2단계 인증 활성화</label>
                        <p class="text-gray-500">관리자 로그인 시 추가 인증을 요구합니다.</p>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input id="enable_captcha" name="enable_captcha" type="checkbox" value="1"
                            class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="enable_captcha" class="font-medium text-gray-700">CAPTCHA 활성화</label>
                        <p class="text-gray-500">로그인 시 자동화된 공격을 방지합니다.</p>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input id="enable_ip_whitelist" name="enable_ip_whitelist" type="checkbox" value="1"
                            class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="enable_ip_whitelist" class="font-medium text-gray-700">IP 화이트리스트 활성화</label>
                        <p class="text-gray-500">특정 IP에서만 관리자 접근을 허용합니다.</p>
                    </div>
                </div>

                <div>
                    <label for="session_lifetime" class="block text-xs font-medium text-gray-700 mb-1">
                        세션 유지 시간 (분)
                    </label>
                    <input type="number" name="session_lifetime" id="session_lifetime" value="120" min="5" max="1440"
                        class="block w-full h-10 px-3 text-sm border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 text-gray-900">
                    <p class="mt-1 text-xs text-gray-500">자동 로그아웃까지의 시간 (5-1440분)</p>
                </div>
            </div>
        </div>
    </form>

    <div class="mt-8 flex justify-between">
        <button type="button" onclick="skipSettings()"
            class="h-10 px-6 border border-gray-300 text-sm font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
            건너뛰기
        </button>
        <button id="save-btn" type="button" onclick="saveSettings()"
            class="h-10 px-6 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200">
            설정 저장
        </button>
    </div>
</div>

<script>
    function saveSettings() {
        const form = document.getElementById('settings-form');
        const formData = new FormData(form);
        const data = {};
        
        // Handle text inputs
        ['site_name', 'site_description', 'admin_prefix', 'session_lifetime'].forEach(field => {
            const value = formData.get(field);
            if (value) data[field] = value;
        });
        
        // Handle checkboxes
        ['enable_2fa', 'enable_captcha', 'enable_ip_whitelist'].forEach(field => {
            data[field] = document.getElementById(field).checked;
        });
        
        const button = document.getElementById('save-btn');
        button.disabled = true;
        button.innerHTML = '저장 중...';
        
        fetch('/admin/setup/save-settings', {
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
                showSuccess('설정이 저장되었습니다.');
                setTimeout(() => nextStep(), 1000);
            } else {
                showError('설정 저장 실패: ' + result.message);
                button.disabled = false;
                button.innerHTML = '설정 저장';
            }
        })
        .catch(error => {
            showError('설정 저장 중 오류가 발생했습니다: ' + error.message);
            button.disabled = false;
            button.innerHTML = '설정 저장';
        });
    }

    function skipSettings() {
        if (confirm('설정을 건너뛰시겠습니까? 나중에 변경할 수 있습니다.')) {
            nextStep();
        }
    }
</script>