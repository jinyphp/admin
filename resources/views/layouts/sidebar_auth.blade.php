<li class="sidebar-header">
    <a class="text-decoration-none" style="color: inherit;" href="/{{ prefix('admin') }}/auth">
        회원관리
    </a>
</li>

<li class="sidebar-item">
    <a class="sidebar-link" href="/{{ prefix('admin') }}/auth/users">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person"
            viewBox="0 0 16 16">
            <path
                d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z" />
        </svg>

        <span class="align-middle">회원목록</span>
        <span class="badge badge-sidebar-primary">New</span>
    </a>
</li>

<li class="sidebar-item">
    <a data-bs-target="#auth-info" data-bs-toggle="collapse" class="sidebar-link collapsed">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-people"
            viewBox="0 0 16 16">
            <path
                d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1zm-7.978-1L7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002-.014.002zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0M6.936 9.28a6 6 0 0 0-1.23-.247A7 7 0 0 0 5 9c-4 0-5 3-5 4q0 1 1 1h4.216A2.24 2.24 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816M4.92 10A5.5 5.5 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0m3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4" />
        </svg>

        <span class="align-middle">회원정보</span>
    </a>
    <ul id="auth-info" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/auth/users">
                회원목록
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/auth/profile">
                프로파일
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/auth/address">
                주소록
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/auth/phone">
                연락처
            </a>
        </li>

        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/auth/avata">
                아바타
            </a>
        </li>
    </ul>
</li>

<li class="sidebar-item">
    <a data-bs-target="#auth" data-bs-toggle="collapse" class="sidebar-link collapsed">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
            class="bi bi-shield-check" viewBox="0 0 16 16">
            <path
                d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56" />
            <path
                d="M10.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0" />
        </svg>

        <span class="align-middle">회원관리</span>
    </a>
    <ul id="auth" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/auth/sleeper">
                휴면회원
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/auth/password">
                패스워드 만기
            </a>
        </li>

        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/auth/auth">
                승인대기
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/auth/unregist">
                회원탈퇴
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/auth/reserve">
                예약회원
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/auth/blacklist">
                블랙회원
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/auth/admin">
                관리자
            </a>
        </li>
    </ul>
</li>


<li class="sidebar-item">
    <a data-bs-target="#auth-agree" data-bs-toggle="collapse" class="sidebar-link collapsed">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-c-circle"
            viewBox="0 0 16 16">
            <path
                d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8m15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8.146 4.992c-1.212 0-1.927.92-1.927 2.502v1.06c0 1.571.703 2.462 1.927 2.462.979 0 1.641-.586 1.729-1.418h1.295v.093c-.1 1.448-1.354 2.467-3.03 2.467-2.091 0-3.269-1.336-3.269-3.603V7.482c0-2.261 1.201-3.638 3.27-3.638 1.681 0 2.935 1.054 3.029 2.572v.088H9.875c-.088-.879-.768-1.512-1.729-1.512" />
        </svg>

        <span class="align-middle">약관관리</span>
    </a>
    <ul id="auth-agree" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/auth/agree">
                약관
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/auth/agree/log">
                약관로그
            </a>
        </li>
    </ul>
</li>


<li class="sidebar-item">
    <a data-bs-target="#oauth" data-bs-toggle="collapse" class="sidebar-link collapsed">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
            class="bi bi-link-45deg" viewBox="0 0 16 16">
            <path
                d="M4.715 6.542 3.343 7.914a3 3 0 1 0 4.243 4.243l1.828-1.829A3 3 0 0 0 8.586 5.5L8 6.086a1 1 0 0 0-.154.199 2 2 0 0 1 .861 3.337L6.88 11.45a2 2 0 1 1-2.83-2.83l.793-.792a4 4 0 0 1-.128-1.287z" />
            <path
                d="M6.586 4.672A3 3 0 0 0 7.414 9.5l.775-.776a2 2 0 0 1-.896-3.346L9.12 3.55a2 2 0 1 1 2.83 2.83l-.793.792c.112.42.155.855.128 1.287l1.372-1.372a3 3 0 1 0-4.243-4.243z" />
        </svg>

        <span class="align-middle">소셜연동</span>
    </a>
    <ul id="oauth" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/auth/oauth">
                공급자
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/auth/oauth/users">
                연동기록
            </a>
        </li>
    </ul>
</li>

<li class="sidebar-item">
    <a data-bs-target="#auth-permit" data-bs-toggle="collapse" class="sidebar-link collapsed">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
            class="bi bi-check2-circle" viewBox="0 0 16 16">
            <path
                d="M2.5 8a5.5 5.5 0 0 1 8.25-4.764.5.5 0 0 0 .5-.866A6.5 6.5 0 1 0 14.5 8a.5.5 0 0 0-1 0 5.5 5.5 0 1 1-11 0" />
            <path
                d="M15.354 3.354a.5.5 0 0 0-.708-.708L8 9.293 5.354 6.646a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0z" />
        </svg>

        <span class="align-middle">권환</span>
    </a>
    <ul id="auth-permit" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/permit">
                사용자 권환
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/permit/role">
                역할
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/auth/grade">
                등급
            </a>
        </li>
    </ul>
</li>

<li class="sidebar-item">
    <a data-bs-target="#auth-log" data-bs-toggle="collapse" class="sidebar-link collapsed">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
            class="bi bi-soundwave" viewBox="0 0 16 16">
            <path fill-rule="evenodd"
                d="M8.5 2a.5.5 0 0 1 .5.5v11a.5.5 0 0 1-1 0v-11a.5.5 0 0 1 .5-.5m-2 2a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5m4 0a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5m-6 1.5A.5.5 0 0 1 5 6v4a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m8 0a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m-10 1A.5.5 0 0 1 3 7v2a.5.5 0 0 1-1 0V7a.5.5 0 0 1 .5-.5m12 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0V7a.5.5 0 0 1 .5-.5" />
        </svg>

        <span class="align-middle">접속기록</span>
    </a>
    <ul id="auth-log" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/auth/logs">
                접속기록
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/auth/log/daily">
                일일기록
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/auth/log/count">
                로그 횟수
            </a>
        </li>
    </ul>
</li>

<li class="sidebar-item">
    <a data-bs-target="#auth-setting" data-bs-toggle="collapse" class="sidebar-link collapsed">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
            class="bi bi-gear" viewBox="0 0 16 16">
            <path
                d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492M5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0" />
            <path
                d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115z" />
        </svg>

        <span class="align-middle">회원설정</span>
    </a>
    <ul id="auth-setting" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/auth/locale">
                사용자 로케일
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/auth/country">
                국가
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/auth/language">
                언어
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/auth/setting">
                설정
            </a>
        </li>
    </ul>
</li>


<li class="sidebar-item">
    <a data-bs-target="#emoney" data-bs-toggle="collapse" class="sidebar-link collapsed">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
            class="bi bi-currency-dollar" viewBox="0 0 16 16">
            <path
                d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V3.467c1.122.11 1.879.714 2.07 1.616h1.47c-.166-1.6-1.54-2.748-3.54-2.875V1H7.591v1.233c-1.939.23-3.27 1.472-3.27 3.156 0 1.454.966 2.483 2.661 2.917l.61.162v4.031c-1.149-.17-1.94-.8-2.131-1.718zm3.391-3.836c-1.043-.263-1.6-.825-1.6-1.616 0-.944.704-1.641 1.8-1.828v3.495l-.2-.05zm1.591 1.872c1.287.323 1.852.859 1.852 1.769 0 1.097-.826 1.828-2.2 1.939V8.73z" />
        </svg>

        <span class="align-middle">이머니</span>
    </a>
    <ul id="emoney" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/auth/emoney">Overview</a>
        </li>
    </ul>
</li>


<li class="sidebar-item">
    <a data-bs-target="#auth-noti" data-bs-toggle="collapse" class="sidebar-link collapsed">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
            class="bi bi-bell" viewBox="0 0 16 16">
            <path
                d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6" />
        </svg>

        <span class="align-middle">알림</span>
    </a>
    <ul id="auth-noti" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/auth/mail">
                메일발송
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link" href="/{{ prefix('admin') }}/auth/message">
                메시지
            </a>
        </li>
    </ul>
</li>
