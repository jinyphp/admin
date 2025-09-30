{{-- 백업 코드 섹션 --}}
<div class="border-b border-gray-200 dark:border-gray-700 pb-4">
    <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">백업 코드</h3>
    
    @if(isset($backupCodes) && count($backupCodes) > 0)
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded p-3">
            <div class="flex items-start mb-3">
                <svg class="h-4 w-4 text-yellow-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div class="ml-3">
                    <p class="text-xs text-yellow-800 dark:text-yellow-200 font-medium">
                        중요: 백업 코드를 안전한 곳에 보관하세요
                    </p>
                    <p class="text-xs text-yellow-700 dark:text-yellow-300 mt-1">
                        2FA 앱이나 전화에 접근할 수 없을 때 이 코드를 사용할 수 있습니다.
                        각 코드는 한 번만 사용 가능합니다.
                    </p>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-2 mb-3">
                @foreach($backupCodes as $code)
                    <code class="bg-white dark:bg-gray-800 px-2 py-1.5 rounded border border-yellow-300 dark:border-yellow-600 text-xs font-mono text-center">
                        {{ $code }}
                    </code>
                @endforeach
            </div>
            
            <div class="flex space-x-2">
                <button onclick="copyBackupCodes()" 
                        class="inline-flex items-center h-8 px-3 border border-yellow-600 dark:border-yellow-500 text-xs font-medium rounded text-yellow-700 dark:text-yellow-300 bg-white dark:bg-gray-800 hover:bg-yellow-50 dark:hover:bg-yellow-900/30">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    복사
                </button>
                
                <button onclick="downloadBackupCodes()" 
                        class="inline-flex items-center h-8 px-3 border border-yellow-600 dark:border-yellow-500 text-xs font-medium rounded text-yellow-700 dark:text-yellow-300 bg-white dark:bg-gray-800 hover:bg-yellow-50 dark:hover:bg-yellow-900/30">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    다운로드
                </button>
                
                <button onclick="printBackupCodes()" 
                        class="inline-flex items-center h-8 px-3 border border-yellow-600 dark:border-yellow-500 text-xs font-medium rounded text-yellow-700 dark:text-yellow-300 bg-white dark:bg-gray-800 hover:bg-yellow-50 dark:hover:bg-yellow-900/30">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    인쇄
                </button>
            </div>
        </div>
        
        <script>
        function copyBackupCodes() {
            const codes = @json($backupCodes);
            const text = codes.join('\n');
            navigator.clipboard.writeText(text).then(function() {
                showNotification('백업 코드가 클립보드에 복사되었습니다.', 'success');
            }).catch(function(err) {
                console.error('복사 실패:', err);
                showNotification('복사에 실패했습니다.', 'error');
            });
        }
        
        function downloadBackupCodes() {
            const codes = @json($backupCodes);
            const appName = '{{ config('app.name', 'Laravel') }}';
            const userEmail = '{{ $user->email ?? '' }}';
            const date = new Date().toLocaleDateString('ko-KR');
            
            const content = `${appName} - 2FA 백업 코드
=====================================

사용자: ${userEmail}
생성일: ${date}

백업 코드:
-------------------------------------
${codes.join('\n')}
-------------------------------------

⚠️ 주의사항:
- 이 코드들을 안전한 곳에 보관하세요.
- 각 코드는 한 번만 사용할 수 있습니다.
- 2FA 앱에 접근할 수 없을 때 사용하세요.
- 이 파일을 안전하게 보관하세요.`;
            
            const blob = new Blob([content], { type: 'text/plain;charset=utf-8' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `2fa-backup-codes-${Date.now()}.txt`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            
            showNotification('백업 코드가 다운로드되었습니다.', 'success');
        }
        
        function printBackupCodes() {
            const codes = @json($backupCodes);
            const appName = '{{ config('app.name', 'Laravel') }}';
            const userEmail = '{{ $user->email ?? '' }}';
            const date = new Date().toLocaleDateString('ko-KR');
            
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>2FA 백업 코드</title>
                    <style>
                        body {
                            font-family: monospace;
                            padding: 20px;
                            max-width: 600px;
                            margin: 0 auto;
                        }
                        h1 { font-size: 18px; margin-bottom: 10px; }
                        .info { margin-bottom: 20px; font-size: 12px; }
                        .codes {
                            display: grid;
                            grid-template-columns: repeat(2, 1fr);
                            gap: 10px;
                            margin: 20px 0;
                        }
                        .code {
                            padding: 8px;
                            border: 1px solid #ccc;
                            text-align: center;
                            font-size: 14px;
                            font-weight: bold;
                        }
                        .warning {
                            margin-top: 20px;
                            padding: 10px;
                            background: #fffbeb;
                            border: 1px solid #fbbf24;
                            font-size: 11px;
                        }
                        @media print {
                            .warning { background: #fff; }
                        }
                    </style>
                </head>
                <body>
                    <h1>${appName} - 2FA 백업 코드</h1>
                    <div class="info">
                        <p>사용자: ${userEmail}</p>
                        <p>생성일: ${date}</p>
                    </div>
                    <div class="codes">
                        ${codes.map(code => `<div class="code">${code}</div>`).join('')}
                    </div>
                    <div class="warning">
                        <strong>⚠️ 주의사항:</strong><br>
                        • 이 코드들을 안전한 곳에 보관하세요.<br>
                        • 각 코드는 한 번만 사용할 수 있습니다.<br>
                        • 2FA 앱에 접근할 수 없을 때 사용하세요.
                    </div>
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }
        
        function showNotification(message, type = 'info') {
            // 간단한 알림 표시 (실제로는 더 나은 UI 컴포넌트 사용)
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 px-4 py-2 rounded-lg text-sm text-white z-50 ${
                type === 'success' ? 'bg-green-600' : 
                type === 'error' ? 'bg-red-600' : 
                'bg-blue-600'
            }`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
        </script>
    @else
        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 text-center">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                백업 코드가 생성되지 않았습니다.
            </p>
            <form action="{{ route('admin.system.user.2fa.regenerate-backup', $user->id) }}" method="POST" class="mt-3">
                @csrf
                <button type="submit" 
                        class="inline-flex items-center h-8 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    백업 코드 생성
                </button>
            </form>
        </div>
    @endif
</div>