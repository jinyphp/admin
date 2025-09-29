<div>
    <h3 class="text-lg font-medium text-gray-900 mb-4">시스템 요구사항 확인</h3>
    <p class="text-sm text-gray-600 mb-6">
        @jiny/admin을 실행하기 위한 시스템 요구사항을 확인합니다.
    </p>

    <div id="requirements-list" class="space-y-3">
        <div class="flex items-center justify-center py-8">
            <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="ml-3 text-gray-600">요구사항 확인 중...</span>
        </div>
    </div>

    <div class="mt-8 flex justify-end">
        <button id="next-btn" type="button" onclick="nextStep()" disabled
            class="h-10 px-6 bg-gray-400 text-white text-sm font-medium rounded cursor-not-allowed">
            다음 단계로
        </button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        checkRequirements();
    });

    function checkRequirements() {
        fetch('/admin/setup/check-requirements')
            .then(response => response.json())
            .then(data => {
                displayRequirements(data);
            })
            .catch(error => {
                showError('요구사항 확인 중 오류가 발생했습니다: ' + error.message);
            });
    }

    function displayRequirements(data) {
        const container = document.getElementById('requirements-list');
        let html = '<div class="bg-white overflow-hidden shadow rounded-lg divide-y divide-gray-200">';
        
        for (const [key, req] of Object.entries(data.requirements)) {
            html += `
                <div class="px-4 py-3">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">${req.name}</p>
                            <p class="text-xs text-gray-500">필요: ${req.required} | 현재: ${req.current}</p>
                        </div>
                        <div class="ml-4">
                            ${req.satisfied 
                                ? '<svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>'
                                : '<svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>'
                            }
                        </div>
                    </div>
                    ${!req.satisfied ? `<p class="text-xs text-red-600 mt-1">${req.message}</p>` : ''}
                </div>
            `;
        }
        
        html += '</div>';
        container.innerHTML = html;
        
        if (data.allSatisfied) {
            const nextBtn = document.getElementById('next-btn');
            nextBtn.disabled = false;
            nextBtn.className = 'h-10 px-6 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200';
            
            showSuccess('모든 시스템 요구사항이 충족되었습니다.');
        } else {
            showError('일부 시스템 요구사항이 충족되지 않았습니다. 위의 항목들을 확인해주세요.');
        }
    }
</script>