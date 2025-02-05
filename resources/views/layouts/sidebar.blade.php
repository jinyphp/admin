<nav id="sidebar" class="sidebar">
    <div class="sidebar-content js-simplebar simplebar-scrollable-y" data-simplebar="init">
        <div class="simplebar-wrapper" style="margin: 0px;">
            <div class="simplebar-height-auto-observer-wrapper">
                <div class="simplebar-height-auto-observer"></div>
            </div>
            <div class="simplebar-mask">
                <div class="simplebar-offset" style="right: 0px; bottom: 0px;">
                    <div class="simplebar-content-wrapper" tabindex="0" role="region" aria-label="scrollable content"
                        style="height: 100%; overflow: hidden scroll;">
                        <div class="simplebar-content" style="padding: 0px;">

                            <a class="sidebar-brand" href="/{{ prefix('admin') }}">
                                <span class="align-middle me-3">JinyPHP</span>
                            </a>

                            <ul class="sidebar-nav">
                                {{--
                                <li class="sidebar-header">
                                        Dashboards
                                </li>
                                --}}
                                {{-- <li class="sidebar-item">
                                    <a class="sidebar-link" href="/{{ prefix('admin') }}">
                                        <span class="align-middle">Dashboards</span>
                                    </a>
                                </li> --}}

                                @includeIf('jiny-admin::layouts.sidebar_app')

                                @includeIf('jiny-admin::layouts.sidebar_auth')

                                @includeIf('jiny-admin::layouts.sidebar_theme')

                            </ul>

                            @includeIf('jiny-admin::layouts.sidebar_cta')

                        </div>
                    </div>
                </div>
            </div>
            <div class="simplebar-placeholder" style="width: 260px; height: 1655px;"></div>
        </div>
        <div class="simplebar-track simplebar-horizontal" style="visibility: hidden;">
            <div class="simplebar-scrollbar" style="width: 0px; display: none;"></div>
        </div>
        <div class="simplebar-track simplebar-vertical" style="visibility: visible;">
            <div class="simplebar-scrollbar"
                style="height: 666px; transform: translate3d(0px, 0px, 0px); display: block;"></div>
        </div>
    </div>
</nav>
