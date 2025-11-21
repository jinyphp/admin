<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Setup - @jiny/admin</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {}
            }
        }
    </script>

    <!-- Alpine.js CDN for interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @livewireStyles
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        {{-- <div class="sm:mx-auto sm:w-full sm:max-w-2xl">
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                @jiny/admin 초기 설정
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                관리자 시스템을 설정하는 과정입니다
            </p>
        </div> --}}
        <div class="text-center mb-2">
                <div
                    class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 dark:bg-blue-900 mb-4">
                    <svg class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-1">@jiny/admin 초기 설정</h3>
                {{-- <p class="text-lg text-gray-600 dark:text-gray-400">
                    관리자 시스템을 설정하는 과정입니다
                </p> --}}
        </div>

        <div class="mt-2 sm:mx-auto sm:w-full sm:max-w-2xl">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                <!-- Progress Bar -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        @foreach($steps as $index => $step)
                            <div class="flex items-center {{ $index < count($steps) - 1 ? 'flex-1' : '' }}">
                                <div class="relative">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-medium transition-all duration-200
                                        {{ $currentStep == $step ? 'bg-blue-600 text-white ring-2 ring-blue-300' : '' }}
                                        {{ array_search($currentStep, $steps) > $index ? 'bg-green-600 text-white cursor-pointer hover:ring-2 hover:ring-green-300' : '' }}
                                        {{ array_search($currentStep, $steps) < $index ? 'bg-gray-300 text-gray-500' : '' }}"
                                        @if(array_search($currentStep, $steps) > $index)
                                            onclick="goToStep('{{ $step }}')"
                                            title="클릭하여 {{ ucfirst($step) }} 단계로 이동"
                                        @endif>
                                        @if(array_search($currentStep, $steps) > $index)
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        @else
                                            {{ $index + 1 }}
                                        @endif
                                    </div>
                                    <div class="absolute -bottom-6 left-1/2 transform -translate-x-1/2 text-xs whitespace-nowrap
                                        {{ array_search($currentStep, $steps) > $index ? 'text-green-600 font-medium cursor-pointer hover:text-green-700' : '' }}"
                                        @if(array_search($currentStep, $steps) > $index)
                                            onclick="goToStep('{{ $step }}')"
                                        @endif>
                                        {{ ucfirst($step) }}
                                    </div>
                                </div>
                                @if($index < count($steps) - 1)
                                    <div class="flex-1 h-1 mx-2 {{ array_search($currentStep, $steps) > $index ? 'bg-green-600' : 'bg-gray-300' }}"></div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Step Content -->
                <div class="mt-10" id="step-content">
                    @if($currentStep == 'requirements')
                        @include('jiny-admin::web.setup.steps.requirements')
                    @elseif($currentStep == 'database')
                        @include('jiny-admin::web.setup.steps.database')
                    @elseif($currentStep == 'admin')
                        @include('jiny-admin::web.setup.steps.admin')
                    @elseif($currentStep == 'complete')
                        @include('jiny-admin::web.setup.steps.complete')
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-6 text-xs text-gray-400 text-center">
                <p>본 로그인은 관리자 전용입니다. 무단 사용 시 법적 처벌을 받을 수 있습니다.</p>
                <p class="mt-1">© 2025 Jiny Admin. All rights reserved.</p>
        </div>
    </div>

    <script>
        function nextStep() {
            fetch('/admin/setup/next-step', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            });
        }

        function goToStep(step) {
            if (confirm('해당 단계로 이동하시겠습니까?')) {
                fetch('/admin/setup/go-to-step', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ step: step })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        showError(data.message);
                    }
                })
                .catch(error => {
                    showError('단계 이동 중 오류가 발생했습니다: ' + error.message);
                });
            }
        }

        function showError(message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'mt-4 p-4 bg-red-50 border border-red-200 rounded-md';
            alertDiv.innerHTML = `
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-800">${message}</p>
                    </div>
                </div>
            `;

            const existingAlert = document.querySelector('.bg-red-50');
            if (existingAlert) {
                existingAlert.remove();
            }

            document.getElementById('step-content').appendChild(alertDiv);
        }

        function showSuccess(message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'mt-4 p-4 bg-green-50 border border-green-200 rounded-md';
            alertDiv.innerHTML = `
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-800">${message}</p>
                    </div>
                </div>
            `;

            const existingAlert = document.querySelector('.bg-green-50');
            if (existingAlert) {
                existingAlert.remove();
            }

            document.getElementById('step-content').appendChild(alertDiv);
        }
    </script>

    @livewireScripts
</body>
</html>
