<ul role="list" class="-mx-2 space-y-1">
    <li>
        <a href="{{ route('admin.system.dashboard', ['prefix'=>request()->route('prefix') ?? 'admin']) }}" 
           class="group flex gap-x-3 rounded-md {{ request()->routeIs('admin.system.dashboard') ? 'bg-white/5 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }} p-2 text-sm/6 font-semibold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 shrink-0">
                <path d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            Dashboard
        </a>
    </li>
    <li>
        <a href="{{ route('admin.system.users', ['prefix'=>request()->route('prefix') ?? 'admin']) }}" 
           class="group flex gap-x-3 rounded-md {{ request()->routeIs('admin.system.users*') ? 'bg-white/5 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }} p-2 text-sm/6 font-semibold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 shrink-0">
                <path d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            Users
        </a>
    </li>
    <li>
        <a href="{{ route('admin.system.modules', ['prefix'=>request()->route('prefix') ?? 'admin']) }}" 
           class="group flex gap-x-3 rounded-md {{ request()->routeIs('admin.system.modules*') ? 'bg-white/5 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }} p-2 text-sm/6 font-semibold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 shrink-0">
                <path d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            Modules
        </a>
    </li>
    <li>
        <a href="{{ route('admin.system.settings', ['prefix'=>request()->route('prefix') ?? 'admin']) }}" 
           class="group flex gap-x-3 rounded-md {{ request()->routeIs('admin.system.settings*') ? 'bg-white/5 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }} p-2 text-sm/6 font-semibold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 shrink-0">
                <path d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            Settings
        </a>
    </li>
    
    {{-- Security Section --}}
    <li>
        <div class="text-xs/6 font-semibold text-gray-400 mt-4">보안 관리</div>
    </li>
    <li>
        <a href="{{ route('admin.system.security.ip-whitelist', ['prefix'=>request()->route('prefix') ?? 'admin']) }}" 
           class="group flex gap-x-3 rounded-md {{ request()->routeIs('admin.system.security.ip-whitelist*') ? 'bg-white/5 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }} p-2 text-sm/6 font-semibold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 shrink-0">
                <path d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            IP 화이트리스트
        </a>
    </li>
    <li>
        <a href="{{ route('admin.system.ipblacklist', ['prefix'=>request()->route('prefix') ?? 'admin']) }}" 
           class="group flex gap-x-3 rounded-md {{ request()->routeIs('admin.system.ipblacklist*') ? 'bg-white/5 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }} p-2 text-sm/6 font-semibold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 shrink-0">
                <path d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            IP 블랙리스트
        </a>
    </li>
    <li>
        <a href="{{ route('admin.system.iptracking', ['prefix'=>request()->route('prefix') ?? 'admin']) }}" 
           class="group flex gap-x-3 rounded-md {{ request()->routeIs('admin.system.iptracking*') ? 'bg-white/5 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }} p-2 text-sm/6 font-semibold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 shrink-0">
                <path d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5a17.92 17.92 0 0 1-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            IP 추적 관리
        </a>
    </li>
    <li>
        <a href="{{ route('admin.system.captcha.logs', ['prefix'=>request()->route('prefix') ?? 'admin']) }}" 
           class="group flex gap-x-3 rounded-md {{ request()->routeIs('admin.system.captcha.logs*') ? 'bg-white/5 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }} p-2 text-sm/6 font-semibold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 shrink-0">
                <path d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            CAPTCHA 로그
        </a>
    </li>
    <li>
        <a href="{{ route('admin.system.user.logs', ['prefix'=>request()->route('prefix') ?? 'admin']) }}" 
           class="group flex gap-x-3 rounded-md {{ request()->routeIs('admin.system.user.logs*') ? 'bg-white/5 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }} p-2 text-sm/6 font-semibold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 shrink-0">
                <path d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            사용자 활동 로그
        </a>
    </li>
    <li>
        <a href="{{ route('admin.system.user.password.logs', ['prefix'=>request()->route('prefix') ?? 'admin']) }}" 
           class="group flex gap-x-3 rounded-md {{ request()->routeIs('admin.system.user.password.logs*') ? 'bg-white/5 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }} p-2 text-sm/6 font-semibold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 shrink-0">
                <path d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            비밀번호 로그
        </a>
    </li>
    <li>
        <a href="{{ route('admin.system.user.sessions', ['prefix'=>request()->route('prefix') ?? 'admin']) }}" 
           class="group flex gap-x-3 rounded-md {{ request()->routeIs('admin.system.user.sessions*') ? 'bg-white/5 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }} p-2 text-sm/6 font-semibold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 shrink-0">
                <path d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            세션 관리
        </a>
    </li>
    <li>
        <a href="{{ route('admin.system.user.2fa', ['prefix'=>request()->route('prefix') ?? 'admin']) }}" 
           class="group flex gap-x-3 rounded-md {{ request()->routeIs('admin.system.user.2fa*') ? 'bg-white/5 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }} p-2 text-sm/6 font-semibold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 shrink-0">
                <path d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            2단계 인증 관리
        </a>
    </li>
    
    {{-- Mail Management Section --}}
    <li>
        <a href="{{ route('admin.system.mail') }}" 
           class="text-xs/6 font-semibold {{ request()->routeIs('admin.system.mail*') ? 'text-white' : 'text-gray-400 hover:text-gray-300' }} mt-4 block">
            메일 관리
        </a>
    </li>
    <li>
        <a href="{{ route('admin.system.mail') }}" 
           class="group flex gap-x-3 rounded-md {{ request()->routeIs('admin.system.mail') && !request()->routeIs('admin.system.mail.*') ? 'bg-white/5 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }} p-2 text-sm/6 font-semibold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 shrink-0">
                <path d="M3 4.5h14.25M3 9h9.75M3 13.5h5.25m5.25-.75L17.25 9m0 0L21 12.75M17.25 9v12" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            대시보드
        </a>
    </li>
    <li>
        <a href="{{ route('admin.system.mail.templates') }}" 
           class="group flex gap-x-3 rounded-md {{ request()->routeIs('admin.system.mail.templates*') ? 'bg-white/5 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }} p-2 text-sm/6 font-semibold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 shrink-0">
                <path d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            템플릿
        </a>
    </li>
    <li>
        <a href="{{ route('admin.system.mail.logs') }}" 
           class="group flex gap-x-3 rounded-md {{ request()->routeIs('admin.system.mail.logs*') ? 'bg-white/5 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }} p-2 text-sm/6 font-semibold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 shrink-0">
                <path d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            발송 기록
        </a>
    </li>
    <li>
        <a href="{{ route('admin.system.mail.setting') }}" 
           class="group flex gap-x-3 rounded-md {{ request()->routeIs('admin.system.mail.setting*') ? 'bg-white/5 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }} p-2 text-sm/6 font-semibold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 shrink-0">
                <path d="M4.5 12a7.5 7.5 0 0 0 15 0m-15 0a7.5 7.5 0 1 1 15 0m-15 0H3m16.5 0H21m-1.5 0H12m-8.457 3.077 1.41-.513m14.095-5.13 1.41-.513M5.106 17.785l1.15-.964m11.49-9.642 1.149-.964M7.501 19.795l.75-1.3m7.5-12.99.75-1.3m-6.063 16.658.26-1.477m2.605-14.772.26-1.477m0 17.726-.26-1.477M10.698 4.614l-.26-1.477M16.5 19.794l-.75-1.299M7.5 4.205 12 12m6.894 5.785-1.149-.964M6.256 7.178l-1.15-.964m15.352 8.864-1.41-.513M4.954 9.435l-1.41-.514M12.002 12l-3.75 6.495" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            메일 설정
        </a>
    </li>
    
    {{-- Notification Section --}}
    <li>
        <div class="text-xs/6 font-semibold text-gray-400 mt-4">알림 관리</div>
    </li>
    <li>
        <a href="{{ route('admin.system.sms.provider', ['prefix'=>request()->route('prefix') ?? 'admin']) }}" 
           class="group flex gap-x-3 rounded-md {{ request()->routeIs('admin.system.sms.provider*') ? 'bg-white/5 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }} p-2 text-sm/6 font-semibold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 shrink-0">
                <path d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            SMS 제공자 설정
        </a>
    </li>
    <li>
        <a href="{{ route('admin.system.sms.send', ['prefix'=>request()->route('prefix') ?? 'admin']) }}" 
           class="group flex gap-x-3 rounded-md {{ request()->routeIs('admin.system.sms.send*') ? 'bg-white/5 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }} p-2 text-sm/6 font-semibold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 shrink-0">
                <path d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            SMS 발송 로그
        </a>
    </li>
    <li>
        <a href="{{ route('admin.system.notifications', ['prefix'=>request()->route('prefix') ?? 'admin']) }}" 
           class="group flex gap-x-3 rounded-md {{ request()->routeIs('admin.system.notifications*') ? 'bg-white/5 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }} p-2 text-sm/6 font-semibold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 shrink-0">
                <path d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            알림 규칙 설정
        </a>
    </li>
    
    {{-- User Management Section --}}
    <li>
        <div class="text-xs/6 font-semibold text-gray-400 mt-4">사용자 관리</div>
    </li>
    <li>
        <a href="{{ route('admin.system.user.type', ['prefix'=>request()->route('prefix') ?? 'admin']) }}" 
           class="group flex gap-x-3 rounded-md {{ request()->routeIs('admin.system.user.type*') ? 'bg-white/5 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }} p-2 text-sm/6 font-semibold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 shrink-0">
                <path d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M6 6h.008v.008H6V6Z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            사용자 유형 관리
        </a>
    </li>
    <li>
        <a href="{{ route('admin.system.avatar', ['prefix'=>request()->route('prefix') ?? 'admin']) }}" 
           class="group flex gap-x-3 rounded-md {{ request()->routeIs('admin.system.avatar*') ? 'bg-white/5 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }} p-2 text-sm/6 font-semibold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 shrink-0">
                <path d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            아바타 관리
        </a>
    </li>
    
    {{-- E-commerce Section --}}
    <li>
        <div class="text-xs/6 font-semibold text-gray-400 mt-4">이커머스</div>
    </li>
    <li>
        <a href="{{ route('admin.cms.ecommerce.dashboard') }}"
           class="group flex gap-x-3 rounded-md {{ request()->routeIs('admin.cms.ecommerce.dashboard*') ? 'bg-white/5 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }} p-2 text-sm/6 font-semibold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 shrink-0">
                <path d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72L4.318 3.44A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            이커머스 대시보드
        </a>
    </li>
    <li>
        <a href="{{ route('admin.cms.ecommerce.orders.index') }}"
           class="group flex gap-x-3 rounded-md {{ request()->routeIs('admin.cms.ecommerce.orders*') ? 'bg-white/5 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }} p-2 text-sm/6 font-semibold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 shrink-0">
                <path d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            주문 관리
        </a>
    </li>
    <li>
        <a href="#" onclick="alert('준비 중입니다.')"
           class="group flex gap-x-3 rounded-md text-gray-400 hover:text-white hover:bg-white/5 p-2 text-sm/6 font-semibold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 shrink-0">
                <path d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 1-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m6.75 4.5v-3a1.5 1.5 0 0 1 3 0v3m-3 0h3m-3-3h3m-3-3V9.75a1.5 1.5 0 0 1 3 0V12m-3 0h3" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            배송 관리 (준비중)
        </a>
    </li>
    <li>
        <a href="{{ route('admin.site.products.index') }}"
           class="group flex gap-x-3 rounded-md {{ request()->routeIs('admin.site.products*') ? 'bg-white/5 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }} p-2 text-sm/6 font-semibold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 shrink-0">
                <path d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            상품 관리
        </a>
    </li>
    <li>
        <a href="{{ route('admin.cms.cart.index') }}"
           class="group flex gap-x-3 rounded-md {{ request()->routeIs('admin.cms.cart*') ? 'bg-white/5 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }} p-2 text-sm/6 font-semibold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 shrink-0">
                <path d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            장바구니 관리
        </a>
    </li>
    <li>
        <a href="{{ route('admin.cms.currencies.index') }}"
           class="group flex gap-x-3 rounded-md {{ request()->routeIs('admin.cms.currencies*') ? 'bg-white/5 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }} p-2 text-sm/6 font-semibold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 shrink-0">
                <path d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            통화 관리
        </a>
    </li>
    <li>
        <a href="{{ route('admin.cms.tax.index') }}"
           class="group flex gap-x-3 rounded-md {{ request()->routeIs('admin.cms.tax*') ? 'bg-white/5 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }} p-2 text-sm/6 font-semibold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 shrink-0">
                <path d="M15 8.25H9m6 3H9m3 6-3-3h1.5a3 3 0 1 0 0-6M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            세율 관리
        </a>
    </li>

    {{-- Documents Section --}}
    <li>
        <div class="text-xs/6 font-semibold text-gray-400 mt-4">리소스</div>
    </li>
    <li>
        <a href="#" 
           class="group flex gap-x-3 rounded-md text-gray-400 hover:text-white hover:bg-white/5 p-2 text-sm/6 font-semibold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 shrink-0">
                <path d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            Documents
        </a>
    </li>
    <li>
        <a href="{{ route('admin.system.reports', ['prefix'=>request()->route('prefix') ?? 'admin']) }}" 
           class="group flex gap-x-3 rounded-md {{ request()->routeIs('admin.system.reports*') ? 'bg-white/5 text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }} p-2 text-sm/6 font-semibold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 shrink-0">
                <path d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            Reports
        </a>
    </li>
</ul>