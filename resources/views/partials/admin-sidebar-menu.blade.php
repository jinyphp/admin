{{-- Admin Sidebar Menu --}}
<nav class="mt-5 px-2">
    {{-- 대시보드 --}}
    <a href="{{ route('admin.system.dashboard') }}" 
       class="group flex items-center px-2 py-2 text-base leading-6 font-medium rounded-md 
              {{ request()->routeIs('admin.system.dashboard') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">
        <svg class="mr-4 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
        대시보드
    </a>

    {{-- 사용자 관리 --}}
    <div class="mt-5">
        <h3 class="px-3 text-xs leading-4 font-semibold text-gray-400 uppercase tracking-wider">
            사용자 관리
        </h3>
        <div class="mt-2 space-y-1">
            <a href="{{ route('admin.system.users') }}" 
               class="group flex items-center px-2 py-2 text-sm leading-5 font-medium rounded-md
                      {{ request()->routeIs('admin.system.users*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">
                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                사용자 목록
            </a>
            
            <a href="{{ route('admin.system.user.types') }}" 
               class="group flex items-center px-2 py-2 text-sm leading-5 font-medium rounded-md
                      {{ request()->routeIs('admin.system.user.types*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">
                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                사용자 타입
            </a>

            <a href="{{ route('admin.system.user.logs') }}" 
               class="group flex items-center px-2 py-2 text-sm leading-5 font-medium rounded-md
                      {{ request()->routeIs('admin.system.user.logs*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">
                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                활동 로그
            </a>
        </div>
    </div>

    {{-- 보안 설정 --}}
    <div class="mt-5">
        <h3 class="px-3 text-xs leading-4 font-semibold text-gray-400 uppercase tracking-wider">
            보안 설정
        </h3>
        <div class="mt-2 space-y-1">
            <a href="{{ route('admin.system.2fa') }}" 
               class="group flex items-center px-2 py-2 text-sm leading-5 font-medium rounded-md
                      {{ request()->routeIs('admin.system.2fa*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">
                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                2단계 인증
            </a>

            <a href="{{ route('admin.system.ip.whitelist') }}" 
               class="group flex items-center px-2 py-2 text-sm leading-5 font-medium rounded-md
                      {{ request()->routeIs('admin.system.ip*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">
                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                IP 관리
            </a>

            <a href="{{ route('admin.system.captcha.logs') }}" 
               class="group flex items-center px-2 py-2 text-sm leading-5 font-medium rounded-md
                      {{ request()->routeIs('admin.system.captcha*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">
                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                CAPTCHA 로그
            </a>
        </div>
    </div>

    {{-- 알림 설정 --}}
    <div class="mt-5">
        <h3 class="px-3 text-xs leading-4 font-semibold text-gray-400 uppercase tracking-wider">
            알림 설정
        </h3>
        <div class="mt-2 space-y-1">
            <a href="{{ route('admin.system.notifications') }}" 
               class="group flex items-center px-2 py-2 text-sm leading-5 font-medium rounded-md
                      {{ request()->routeIs('admin.system.notifications') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">
                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                알림 대시보드
            </a>

            <a href="{{ route('admin.system.emailtemplates') }}" 
               class="group flex items-center px-2 py-2 text-sm leading-5 font-medium rounded-md
                      {{ request()->routeIs('admin.system.emailtemplates*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">
                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                이메일 템플릿
            </a>

            <a href="{{ route('admin.system.notifications.webhooks') }}" 
               class="group flex items-center px-2 py-2 text-sm leading-5 font-medium rounded-md
                      {{ request()->routeIs('admin.system.notifications.webhooks*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">
                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
                웹훅 채널
            </a>

            <a href="{{ route('admin.system.notifications.push') }}" 
               class="group flex items-center px-2 py-2 text-sm leading-5 font-medium rounded-md
                      {{ request()->routeIs('admin.system.notifications.push*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">
                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                푸시 알림
            </a>

            <a href="{{ route('admin.system.sms.provider') }}" 
               class="group flex items-center px-2 py-2 text-sm leading-5 font-medium rounded-md
                      {{ request()->routeIs('admin.system.sms*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">
                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
                SMS 설정
            </a>

            <a href="{{ route('admin.system.notifications.event-channels') }}" 
               class="group flex items-center px-2 py-2 text-sm leading-5 font-medium rounded-md
                      {{ request()->routeIs('admin.system.notifications.event-channels*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">
                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                </svg>
                이벤트 채널 매핑
            </a>

            <a href="{{ route('admin.system.notifications.statistics') }}" 
               class="group flex items-center px-2 py-2 text-sm leading-5 font-medium rounded-md
                      {{ request()->routeIs('admin.system.notifications.statistics*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">
                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                통계 및 로그
            </a>
        </div>
    </div>

    {{-- 파트너 관리 --}}
    <div class="mt-5">
        <h3 class="px-3 text-xs leading-4 font-semibold text-gray-400 uppercase tracking-wider">
            파트너 관리
        </h3>
        <div class="mt-2 space-y-1">
            <a href="{{ route('admin.partner.dashboard') }}"
               class="group flex items-center px-2 py-2 text-sm leading-5 font-medium rounded-md
                      {{ request()->routeIs('admin.partner.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">
                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                파트너 대시보드
            </a>

            <a href="{{ route('admin.partner.users.index') }}"
               class="group flex items-center px-2 py-2 text-sm leading-5 font-medium rounded-md
                      {{ request()->routeIs('admin.partner.users.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">
                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                파트너 회원
            </a>

            <a href="{{ route('admin.partner.sales.index') }}"
               class="group flex items-center px-2 py-2 text-sm leading-5 font-medium rounded-md
                      {{ request()->routeIs('admin.partner.sales.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">
                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
                매출 관리
            </a>

            <a href="{{ route('admin.partner.network.commission.index') }}"
               class="group flex items-center px-2 py-2 text-sm leading-5 font-medium rounded-md
                      {{ request()->routeIs('admin.partner.network.commission.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">
                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                </svg>
                커미션 관리
            </a>

            <a href="{{ route('admin.partner.network.tree') }}"
               class="group flex items-center px-2 py-2 text-sm leading-5 font-medium rounded-md
                      {{ request()->routeIs('admin.partner.network.tree*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">
                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 5v4m8-4v4m-6 10V9h4v10" />
                </svg>
                네트워크 트리
            </a>

            <a href="{{ route('admin.partner.tiers.index') }}"
               class="group flex items-center px-2 py-2 text-sm leading-5 font-medium rounded-md
                      {{ request()->routeIs('admin.partner.tiers.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">
                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                등급 관리
            </a>

            <a href="{{ route('admin.partner.type.index') }}"
               class="group flex items-center px-2 py-2 text-sm leading-5 font-medium rounded-md
                      {{ request()->routeIs('admin.partner.type.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">
                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                타입 관리
            </a>
        </div>
    </div>

    {{-- 시스템 설정 --}}
    <div class="mt-5">
        <h3 class="px-3 text-xs leading-4 font-semibold text-gray-400 uppercase tracking-wider">
            시스템 설정
        </h3>
        <div class="mt-2 space-y-1">
            <a href="{{ route('admin.system.mail.setting') }}" 
               class="group flex items-center px-2 py-2 text-sm leading-5 font-medium rounded-md
                      {{ request()->routeIs('admin.system.mail.setting*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">
                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                메일 설정
            </a>
        </div>
    </div>

    {{-- 문서 --}}
    <div class="mt-5">
        <h3 class="px-3 text-xs leading-4 font-semibold text-gray-400 uppercase tracking-wider">
            문서
        </h3>
        <div class="mt-2 space-y-1">
            <a href="/admin/docs/security" target="_blank"
               class="group flex items-center px-2 py-2 text-sm leading-5 font-medium rounded-md
                      text-gray-300 hover:text-white hover:bg-gray-700">
                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                보안 문서
            </a>

            <a href="/admin/docs/notifications" target="_blank"
               class="group flex items-center px-2 py-2 text-sm leading-5 font-medium rounded-md
                      text-gray-300 hover:text-white hover:bg-gray-700">
                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                알림 문서
            </a>

            <a href="/admin/docs/middleware" target="_blank"
               class="group flex items-center px-2 py-2 text-sm leading-5 font-medium rounded-md
                      text-gray-300 hover:text-white hover:bg-gray-700">
                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
                미들웨어 가이드
            </a>
        </div>
    </div>
</nav>