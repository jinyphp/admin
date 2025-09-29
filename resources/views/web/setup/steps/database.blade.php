<div>
    <h3 class="text-lg font-medium text-gray-900 mb-4">데이터베이스 설정</h3>
    <p class="text-sm text-gray-600 mb-6">
        데이터베이스 연결을 확인하고 필요한 테이블을 생성합니다.
    </p>

    <div id="database-info" class="mb-6 hidden">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h4 class="text-sm font-medium text-gray-900 mb-4">데이터베이스 정보</h4>
                <dl id="db-details" class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                    <!-- DB 정보가 여기에 동적으로 추가됨 -->
                </dl>
            </div>
        </div>
    </div>

    <div id="database-status" class="mb-6">
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="animate-spin h-5 w-5 text-blue-600 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm text-gray-600">데이터베이스 연결 확인 중...</span>
            </div>
        </div>
    </div>
    
    <div id="migration-check-section" class="hidden mb-6">
        <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-blue-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-blue-800">미적용 마이그레이션 확인</p>
                        <p class="text-xs text-blue-600 mt-1">데이터베이스에 적용되지 않은 마이그레이션을 확인할 수 있습니다.</p>
                    </div>
                </div>
                <button type="button" onclick="checkPendingMigrations()" 
                    class="ml-4 h-9 px-4 bg-blue-100 text-blue-700 text-sm font-medium rounded hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200">
                    마이그레이션 상태 확인
                </button>
            </div>
        </div>
    </div>
    
    <div id="migration-status-result" class="hidden mb-6">
        <!-- 마이그레이션 상태 결과가 여기에 표시됨 -->
    </div>

    <div id="migration-section" class="hidden">
        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">마이그레이션이 필요합니다</h3>
                    <p class="mt-2 text-sm text-yellow-700">
                        데이터베이스 테이블을 생성하기 위해 마이그레이션을 실행해야 합니다.
                    </p>
                    <div class="mt-4">
                        <button type="button" onclick="runMigrations()" 
                            class="h-10 px-6 bg-yellow-100 text-yellow-700 text-sm font-medium rounded hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition duration-200">
                            마이그레이션 실행
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="migration-output" class="hidden mb-6">
        <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
            <pre class="text-green-400 text-xs font-mono" id="migration-log"></pre>
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
        checkDatabase();
    });

    function checkDatabase() {
        fetch('/admin/setup/check-database')
            .then(response => response.json())
            .then(data => {
                const statusDiv = document.getElementById('database-status');
                
                if (data.connected) {
                    // DB 정보 표시
                    displayDatabaseInfo(data.connectionInfo, data.tableCount);
                    
                    // 마이그레이션 체크 섹션 표시
                    document.getElementById('migration-check-section').classList.remove('hidden');
                    
                    if (data.tablesExist) {
                        statusDiv.innerHTML = `
                            <div class="bg-green-50 border border-green-200 rounded-md p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-green-800">데이터베이스가 준비되었습니다 (테이블 ${data.tableCount}개)</p>
                                    </div>
                                </div>
                            </div>
                        `;
                        enableNextButton();
                    } else {
                        statusDiv.innerHTML = `
                            <div class="bg-green-50 border border-green-200 rounded-md p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-green-800">데이터베이스 연결 성공</p>
                                    </div>
                                </div>
                            </div>
                        `;
                        document.getElementById('migration-section').classList.remove('hidden');
                    }
                } else {
                    statusDiv.innerHTML = `
                        <div class="bg-red-50 border border-red-200 rounded-md p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-800">${data.message}</p>
                                </div>
                            </div>
                        </div>
                    `;
                }
            })
            .catch(error => {
                showError('데이터베이스 확인 중 오류가 발생했습니다: ' + error.message);
            });
    }

    function runMigrations() {
        const button = event.target;
        button.disabled = true;
        button.innerHTML = '마이그레이션 실행 중...';
        
        fetch('/admin/setup/run-migrations', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('migration-section').classList.add('hidden');
                document.getElementById('migration-output').classList.remove('hidden');
                document.getElementById('migration-log').textContent = data.output;
                
                showSuccess('마이그레이션이 성공적으로 완료되었습니다.');
                enableNextButton();
            } else {
                showError('마이그레이션 실패: ' + data.message);
                button.disabled = false;
                button.innerHTML = '마이그레이션 실행';
            }
        })
        .catch(error => {
            showError('마이그레이션 실행 중 오류가 발생했습니다: ' + error.message);
            button.disabled = false;
            button.innerHTML = '마이그레이션 실행';
        });
    }

    function displayDatabaseInfo(connectionInfo, tableCount) {
        const infoDiv = document.getElementById('database-info');
        const detailsDiv = document.getElementById('db-details');
        
        let detailsHtml = '';
        
        if (connectionInfo.driver === 'SQLite') {
            detailsHtml = `
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">데이터베이스 타입</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            ${connectionInfo.driver}
                        </span>
                    </dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">데이터베이스 파일</dt>
                    <dd class="mt-1 text-sm text-gray-900">${connectionInfo.database}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">파일 경로</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono text-xs bg-gray-50 p-2 rounded">${connectionInfo.path}</dd>
                </div>
            `;
        } else {
            detailsHtml = `
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">데이터베이스 타입</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            ${connectionInfo.driver}
                        </span>
                    </dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">호스트</dt>
                    <dd class="mt-1 text-sm text-gray-900">${connectionInfo.host}:${connectionInfo.port}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">데이터베이스명</dt>
                    <dd class="mt-1 text-sm text-gray-900">${connectionInfo.database}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">테이블 수</dt>
                    <dd class="mt-1 text-sm text-gray-900">${tableCount}개</dd>
                </div>
            `;
        }
        
        detailsDiv.innerHTML = detailsHtml;
        infoDiv.classList.remove('hidden');
    }

    function checkPendingMigrations() {
        const button = event.target;
        button.disabled = true;
        button.innerHTML = '확인 중...';
        
        fetch('/admin/setup/check-pending-migrations')
            .then(response => response.json())
            .then(data => {
                const resultDiv = document.getElementById('migration-status-result');
                
                if (data.success) {
                    let resultHtml = '';
                    
                    if (data.hasPending) {
                        resultHtml = `
                            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <h3 class="text-sm font-medium text-yellow-800">
                                            미적용 마이그레이션 ${data.pendingCount}개 발견
                                        </h3>
                                        <div class="mt-2 text-sm text-yellow-700">
                                            <p>전체 ${data.totalMigrations}개 중 ${data.ranMigrations}개 적용됨</p>
                                            <details class="mt-2">
                                                <summary class="cursor-pointer font-medium">미적용 마이그레이션 목록 보기</summary>
                                                <ul class="mt-2 list-disc list-inside text-xs font-mono bg-white p-2 rounded">
                                                    ${data.pendingMigrations.map(m => `<li>${m}</li>`).join('')}
                                                </ul>
                                            </details>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        // 마이그레이션 섹션도 표시
                        document.getElementById('migration-section').classList.remove('hidden');
                    } else {
                        resultHtml = `
                            <div class="bg-green-50 border border-green-200 rounded-md p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-green-800">
                                            모든 마이그레이션이 적용되었습니다
                                        </p>
                                        <p class="text-xs text-green-700 mt-1">
                                            ${data.totalMigrations}개의 마이그레이션이 모두 실행되었습니다.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                    
                    resultDiv.innerHTML = resultHtml;
                    resultDiv.classList.remove('hidden');
                } else {
                    showError('마이그레이션 상태 확인 실패: ' + data.message);
                }
                
                button.disabled = false;
                button.innerHTML = '마이그레이션 상태 확인';
            })
            .catch(error => {
                showError('마이그레이션 상태 확인 중 오류가 발생했습니다: ' + error.message);
                button.disabled = false;
                button.innerHTML = '마이그레이션 상태 확인';
            });
    }
    
    function enableNextButton() {
        const nextBtn = document.getElementById('next-btn');
        nextBtn.disabled = false;
        nextBtn.className = 'h-10 px-6 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200';
    }
</script>