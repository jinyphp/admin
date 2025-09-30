@extends('jiny-admin::layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        {{-- 헤더 섹션 --}}
        <div class="mb-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                        @if(isset($user))
                            {{ $user->name }} 2FA 관리
                        @else
                            2FA 관리
                        @endif
                    </h1>
                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                        2차 인증 설정을 관리합니다
                    </p>
                </div>
                <div>
                    <a href="{{ route('admin.system.user.2fa') }}" 
                       class="inline-flex items-center h-8 px-3 border border-gray-200 bg-white text-gray-700 text-xs font-medium rounded hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-colors">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        목록으로
                    </a>
                </div>
            </div>
        </div>

        {{-- 알림 메시지 --}}
        @if(session('success'))
            <div class="mb-3 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded">
                <div class="flex items-center">
                    <svg class="h-4 w-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-xs font-medium text-green-800 dark:text-green-200">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded">
                <div class="flex items-center">
                    <svg class="h-4 w-4 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-xs font-medium text-red-800 dark:text-red-200">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        {{-- 메인 컨텐츠 --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded overflow-hidden">
            @if(isset($user))
                @if($user->two_factor_enabled)
                    {{-- 2FA가 이미 활성화된 경우: 관리 화면 --}}
                    @include('jiny-admin::admin.admin_user2fa.partials.manage', ['user' => $user])
                @else
                    {{-- 2FA가 비활성화된 경우: 설정 화면 --}}
                    @include('jiny-admin::admin.admin_user2fa.partials.setup', ['user' => $user])
                @endif
            @else
                <div class="p-6">
                    <p class="text-gray-500 dark:text-gray-400">사용자 정보를 불러올 수 없습니다.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection