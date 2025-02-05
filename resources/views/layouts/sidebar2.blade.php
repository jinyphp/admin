<nav id="sidebar" class="sidebar">
    <div class="sidebar-content js-simplebar">
        <a class="sidebar-brand" href="/admin">
            <span class="align-middle me-3">JinyPHP</span>
        </a>

        {{-- 사이드바 메뉴 --}}
        @livewire('site-widget-menu',[
                'code' => "admin_hyper",
                'key' => "side",
                'widget_path' => "side_menu",
                'viewFile' => "jiny-admin::layouts.sidebar_root"
        ])

        {{-- 사이드바 추가 메뉴 --}}
        @includeIf("jiny-admin::layouts.sidebar_cta")
    </div>
</nav>
