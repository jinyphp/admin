@php
    $currentRoute = request()->route()->getName() ?? '';
    $isMobile = isset($mobile) && $mobile;
@endphp

<li>
    <ul role="list" class="-mx-2 space-y-1">
        <!-- Dashboard -->
        <li>
            <a href="{{ route('admin.system.dashboard') }}"
               class="group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold {{ str_starts_with($currentRoute, 'admin.system.dashboard') ? 'bg-white/5 text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                <svg class="size-6 shrink-0"
                     fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                대시보드
            </a>
        </li>
    </ul>
</li>

<!-- 사용자 관리 -->
<li>
    <div class="text-xs/6 font-semibold text-gray-400">사용자 관리</div>
    <ul role="list" class="-mx-2 mt-2 space-y-1">
        <!-- 사용자 목록 -->
        <li>
            <a href="{{ route('admin.system.users') }}"
               class="group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold {{ str_starts_with($currentRoute, 'admin.system.users') && !str_contains($currentRoute, 'type') ? 'bg-white/5 text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                <svg class="size-6 shrink-0"
                     fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
                사용자 목록
            </a>
        </li>

        <!-- 사용자 유형 -->
        <li>
            <a href="{{ route('admin.system.user.type') }}"
               class="group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold {{ str_starts_with($currentRoute, 'admin.system.user.type') ? 'bg-white/5 text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                <svg class="size-6 shrink-0"
                     fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                </svg>
                사용자 유형
            </a>
        </li>

        <!-- 2FA 관리 -->
        <li>
            <a href="{{ route('admin.system.user.2fa') }}"
               class="group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold {{ str_starts_with($currentRoute, 'admin.system.user.2fa') || str_starts_with($currentRoute, 'admin.system.2fa') ? 'bg-white/5 text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                <svg class="size-6 shrink-0"
                     fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                </svg>
                2FA 관리
            </a>
        </li>
    </ul>
</li>

<!-- 활동 모니터링 -->
<li>
    <div class="text-xs/6 font-semibold text-gray-400">활동 모니터링</div>
    <ul role="list" class="-mx-2 mt-2 space-y-1">
        <!-- 활동 로그 -->
        <li>
            <a href="{{ route('admin.system.user.logs') }}"
               class="group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold {{ str_starts_with($currentRoute, 'admin.system.user.logs') ? 'bg-white/5 text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                <svg class="size-6 shrink-0"
                     fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                활동 로그
            </a>
        </li>

        <!-- 세션 관리 -->
        <li>
            <a href="{{ route('admin.system.user.sessions') }}"
               class="group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold {{ str_starts_with($currentRoute, 'admin.system.user.sessions') ? 'bg-white/5 text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                <svg class="size-6 shrink-0"
                     fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" />
                </svg>
                세션 관리
            </a>
        </li>

        <!-- 통계 -->
        <li>
            <a href="{{ route('admin.system.user.stats') }}"
               class="group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold {{ str_starts_with($currentRoute, 'admin.system.user.stats') ? 'bg-white/5 text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                <svg class="size-6 shrink-0"
                     fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                </svg>
                통계
            </a>
        </li>
    </ul>
</li>

<!-- 보안 -->
<li>
    <div class="text-xs/6 font-semibold text-gray-400">보안</div>
    <ul role="list" class="-mx-2 mt-2 space-y-1">
        <!-- IP 화이트리스트 -->
        <li>
            <a href="{{ route('admin.system.security.ip-whitelist') }}"
               class="group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold {{ str_starts_with($currentRoute, 'admin.system.security.ip-whitelist') ? 'bg-white/5 text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                <svg class="size-6 shrink-0"
                     fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                </svg>
                IP 화이트리스트
            </a>
        </li>

        <!-- 비밀번호 로그 -->
        <li>
            <a href="{{ route('admin.system.user.password.logs') }}"
               class="group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold {{ str_starts_with($currentRoute, 'admin.system.user.password') ? 'bg-white/5 text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                <svg class="size-6 shrink-0"
                     fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                </svg>
                비밀번호 보안
            </a>
        </li>

        <!-- CAPTCHA 로그 -->
        <li>
            <a href="{{ route('admin.system.captcha.logs') }}"
               class="group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold {{ str_starts_with($currentRoute, 'admin.system.captcha') ? 'bg-white/5 text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                <svg class="size-6 shrink-0"
                     fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M2.25 15a4.5 4.5 0 004.5 4.5H18a3.75 3.75 0 001.332-7.257 3 3 0 00-3.758-3.848 5.25 5.25 0 00-10.233 2.33A4.502 4.502 0 002.25 15z" />
                </svg>
                CAPTCHA 로그
            </a>
        </li>
    </ul>
</li>

<!-- 알림 -->
<li>
    <div class="text-xs/6 font-semibold text-gray-400">알림</div>
    <ul role="list" class="-mx-2 mt-2 space-y-1">
        <!-- 메일 설정 -->
        <li>
            <a href="{{ route('admin.system.mail') }}"
               class="group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold {{ str_starts_with($currentRoute, 'admin.system.mail') ? 'bg-white/5 text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                <svg class="size-6 shrink-0"
                     fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                </svg>
                메일 알림
            </a>
        </li>

        <!-- SMS 발송 -->
        <li>
            <a href="{{ route('admin.system.sms') }}"
               class="group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold {{ str_starts_with($currentRoute, 'admin.system.sms') ? 'bg-white/5 text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                <svg class="size-6 shrink-0"
                     fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                </svg>
                SMS 관리
            </a>
        </li>

        <!-- SMS 제공업체 -->
        <li>
            <a href="{{ route('admin.system.webhook') }}"
               class="group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold {{ str_starts_with($currentRoute, 'admin.system.webhook') ? 'bg-white/5 text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                <svg class="size-6 shrink-0"
                     fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                </svg>
                WebHook
            </a>
        </li>
    </ul>
</li>

<!-- 개발 도구 -->
{{-- @if(config('app.debug'))
<li>
    <div class="text-xs/6 font-semibold text-gray-400">개발 도구</div>
    <ul role="list" class="-mx-2 mt-2 space-y-1">
        <!-- 템플릿 -->
        <li>
            <a href="{{ route('admin.system.templates') }}"
               class="group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold {{ str_starts_with($currentRoute, 'admin.system.templates') ? 'bg-white/5 text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                <svg class="size-6 shrink-0"
                     fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                템플릿
            </a>
        </li>

        <!-- 테스트 -->
        <li>
            <a href="{{ route('admin.system.test') }}"
               class="group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold {{ str_starts_with($currentRoute, 'admin.system.test') ? 'bg-white/5 text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                <svg class="size-6 shrink-0"
                     fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                </svg>
                테스트
            </a>
        </li>

        <!-- Hello -->
        <li>
            <a href="{{ route('admin.system.hello') }}"
               class="group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold {{ str_starts_with($currentRoute, 'admin.system.hello') ? 'bg-white/5 text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                <svg class="size-6 shrink-0"
                     fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15.182 15.182a4.5 4.5 0 01-6.364 0M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z" />
                </svg>
                Hello
            </a>
        </li>
    </ul>
</li>
@endif --}}

<!-- 사용자 정보 -->
<li class="-mx-6 mt-auto">
    @if(Auth::check())
    <a href="{{ route('admin.system.users.show', Auth::id()) }}" class="flex items-center gap-x-4 px-6 py-3 text-sm/6 font-semibold text-white hover:bg-white/5">
        @if(Auth::user()->avatar && Auth::user()->avatar !== '/images/default-avatar.png')
            <img src="{{ Auth::user()->avatar }}"
                 alt="{{ Auth::user()->name }}"
                 class="size-8 rounded-full object-cover outline -outline-offset-1 outline-white/10"
                 onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'size-8 rounded-full bg-gray-800 flex items-center justify-center text-white text-sm font-medium outline -outline-offset-1 outline-white/10\'>{{ mb_strtoupper(mb_substr(Auth::user()->name ?? Auth::user()->email, 0, 1)) }}</div><span aria-hidden=\'true\'>{{ Auth::user()->name ?? Auth::user()->email }}</span>';">
        @else
            @php
                $initial = mb_strtoupper(mb_substr(Auth::user()->name ?? Auth::user()->email ?? '?', 0, 1));
                $colors = ['bg-red-600', 'bg-yellow-600', 'bg-green-600', 'bg-blue-600', 'bg-indigo-600', 'bg-purple-600', 'bg-pink-600'];
                $colorIndex = crc32(Auth::user()->name ?? Auth::user()->email) % count($colors);
                $bgColor = $colors[$colorIndex];
            @endphp
            <div class="size-8 rounded-full {{ $bgColor }} flex items-center justify-center text-white text-sm font-medium outline -outline-offset-1 outline-white/10">
                {{ $initial }}
            </div>
        @endif
        <span class="sr-only">Your profile</span>
        <span aria-hidden="true">{{ Auth::user()->name ?? Auth::user()->email }}</span>
    </a>

    <!-- 로그아웃 -->
    <form method="POST" action="{{ route('admin.logout') }}" class="px-6 pb-3">
        @csrf
        <button type="submit" class="flex w-full items-center gap-x-4 rounded-md px-3 py-2 text-sm/6 font-semibold text-gray-400 hover:bg-white/5 hover:text-white">
            <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
            </svg>
            로그아웃
        </button>
    </form>
    @endif
</li>
