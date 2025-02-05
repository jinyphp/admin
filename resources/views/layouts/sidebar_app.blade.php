
<li class="sidebar-header">
    <a class="text-decoration-none" style="color: inherit;" href="/{{ prefix('admin') }}">
        Apps
    </a>
</li>

<li class="sidebar-item">
    <a data-bs-target="#ecommerce" data-bs-toggle="collapse" class="sidebar-link collapsed">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box"
            viewBox="0 0 16 16">
            <path
                d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5 8 5.961 14.154 3.5zM15 4.239l-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1 1 0 0 1-.629.928l-7.185 2.874a.5.5 0 0 1-.372 0L.63 13.09a1 1 0 0 1-.63-.928V3.5a.5.5 0 0 1 .314-.464z" />
        </svg>

        <span class="align-middle">라라벨</span>
    </a>
    <ul id="ecommerce" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/laravel">
                Overview
                {{-- <span class="badge badge-sidebar-primary">New</span> --}}
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/laravel/database">
                데이터베이스
                {{-- <span class="badge badge-sidebar-primary">New</span> --}}
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/laravel/setting">
                환경설정
                {{-- <span class="badge badge-sidebar-primary">New</span> --}}
            </a>
        </li>

    </ul>
</li>

<li class="sidebar-item">
    <a data-bs-target="#projects" data-bs-toggle="collapse" class="sidebar-link collapsed">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-geo-alt"
            viewBox="0 0 16 16">
            <path
                d="M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A32 32 0 0 1 8 14.58a32 32 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10" />
            <path d="M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4m0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6" />
        </svg>

        <span class="align-middle">지역설정</span>
    </a>
    <ul id="projects" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/locale">
                Overview
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/locale/country">
                국가
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/locale/language">
                언어
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/locale/currency">
                통화
            </a>
        </li>
    </ul>
</li>

<li class="sidebar-item">
    <a class="sidebar-link" href="/{{ prefix('admin') }}/modules">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-grid-3x2-gap" viewBox="0 0 16 16">
            <path d="M4 4v2H2V4zm1 7V9a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1m0-5V4a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1m5 5V9a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1m0-5V4a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1M9 4v2H7V4zm5 0h-2v2h2zM4 9v2H2V9zm5 0v2H7V9zm5 0v2h-2V9zm-3-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1zm1 4a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1V9a1 1 0 0 0-1-1z"/>
        </svg>

        <span class="align-middle">모듈</span>
        {{-- <span class="badge badge-sidebar-primary">New</span> --}}
    </a>
</li>

<li class="sidebar-item">
    <a class="sidebar-link" href="/{{ prefix('admin') }}/license">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-key" viewBox="0 0 16 16">
            <path d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L13 9.207l-.646.647a.5.5 0 0 1-.708 0L11 9.207l-.646.647a.5.5 0 0 1-.708 0L9 9.207l-.646.647A.5.5 0 0 1 8 10h-.535A4 4 0 0 1 0 8m4-3a3 3 0 1 0 2.712 4.285A.5.5 0 0 1 7.163 9h.63l.853-.854a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.793-.793-1-1h-6.63a.5.5 0 0 1-.451-.285A3 3 0 0 0 4 5"/>
            <path d="M4 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
        </svg>

        <span class="align-middle">라이센스</span>
        {{-- <span class="badge badge-sidebar-primary">New</span> --}}
    </a>
</li>
