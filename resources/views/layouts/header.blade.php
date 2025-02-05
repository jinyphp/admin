<nav class="navbar navbar-expand navbar-bg">
    <a class="sidebar-toggle">
        <i class="hamburger align-self-center"></i>
    </a>

    <form class="d-none d-sm-inline-block">
        <div class="input-group input-group-navbar">
            <input type="text" class="form-control" placeholder="Search projects…"
                aria-label="Search">
            <button class="btn" type="button">
                <i class="align-middle" data-lucide="search"></i>
            </button>
        </div>
    </form>

    <ul class="navbar-nav">
        <li class="nav-item px-2 dropdown d-none d-sm-inline-block">
            <a class="nav-link dropdown-toggle" href="#" id="servicesDropdown" role="button"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Mega menu
            </a>
            <div class="dropdown-menu dropdown-menu-start dropdown-mega"
                aria-labelledby="servicesDropdown">
                <div class="d-md-flex align-items-start justify-content-start">
                    <div class="dropdown-mega-list">
                        <div class="dropdown-header">UI Elements</div>
                        <a class="dropdown-item" href="#">Alerts</a>
                        <a class="dropdown-item" href="#">Buttons</a>
                        <a class="dropdown-item" href="#">Cards</a>
                        <a class="dropdown-item" href="#">Carousel</a>
                        <a class="dropdown-item" href="#">General</a>
                        <a class="dropdown-item" href="#">Grid</a>
                        <a class="dropdown-item" href="#">Modals</a>
                        <a class="dropdown-item" href="#">Tabs</a>
                        <a class="dropdown-item" href="#">Typography</a>
                    </div>
                    <div class="dropdown-mega-list">
                        <div class="dropdown-header">Forms</div>
                        <a class="dropdown-item" href="#">Layouts</a>
                        <a class="dropdown-item" href="#">Basic Inputs</a>
                        <a class="dropdown-item" href="#">Input Groups</a>
                        <a class="dropdown-item" href="#">Advanced Inputs</a>
                        <a class="dropdown-item" href="#">Editors</a>
                        <a class="dropdown-item" href="#">Validation</a>
                        <a class="dropdown-item" href="#">Wizard</a>
                    </div>
                    <div class="dropdown-mega-list">
                        <div class="dropdown-header">Tables</div>
                        <a class="dropdown-item" href="#">Basic Tables</a>
                        <a class="dropdown-item" href="#">Responsive Table</a>
                        <a class="dropdown-item" href="#">Table with Buttons</a>
                        <a class="dropdown-item" href="#">Column Search</a>
                        <a class="dropdown-item" href="#">Muulti Selection</a>
                        <a class="dropdown-item" href="#">Ajax Sourced Data</a>
                    </div>
                </div>
            </div>
        </li>
    </ul>

    <div class="navbar-collapse collapse">
        <ul class="navbar-nav navbar-align">
            {{-- 알람 --}}
            @php
                $new_messages = DB::table('user_messages')
                    ->where('status','new')
                    ->count();
            @endphp
            <li class="nav-item dropdown">
                <a class="nav-icon dropdown-toggle" href="#" id="messagesDropdown"
                    data-bs-toggle="dropdown">
                    <div class="position-relative">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chat-square-text" viewBox="0 0 16 16">
                            <path d="M14 1a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1h-2.5a2 2 0 0 0-1.6.8L8 14.333 6.1 11.8a2 2 0 0 0-1.6-.8H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1zM2 0a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2.5a1 1 0 0 1 .8.4l1.9 2.533a1 1 0 0 0 1.6 0l1.9-2.533a1 1 0 0 1 .8-.4H14a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2z"/>
                            <path d="M3 3.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5M3 6a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9A.5.5 0 0 1 3 6m0 2.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"/>
                        </svg>
                        <span class="indicator">{{$new_messages}}</span>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end py-0"
                    aria-labelledby="messagesDropdown">
                    <div class="dropdown-menu-header">
                        <div class="position-relative">
                            {{$new_messages}} 새로운 메시지
                        </div>
                    </div>
                    <div class="list-group">
                        @foreach(DB::table('user_messages')
                            ->where('to_user_id',Auth::user()->id)
                            ->limit(5)
                            ->get() as $item)
                        <a href="#" class="list-group-item">
                            <div class="row g-0 align-items-center">
                                <div class="col-2">

                                    <img src="/home/user/avatar/{{$item->user_id}}"
                                        class="img-fluid rounded-circle"
                                        alt="Carl Jenkins" width="40" height="40">
                                </div>
                                <div class="col-10 ps-2">
                                    <div>{{$item->name}}</div>
                                    <div class="text-muted small mt-1">
                                        {{$item->subject}}
                                    </div>
                                    <div class="text-muted small mt-1">
                                        {{$item->created_at}}
                                    </div>
                                </div>
                            </div>
                        </a>
                        @endforeach

                    </div>
                    <div class="dropdown-menu-footer">
                        <a href="/admin/auth/message" class="text-muted">모든 메시지 읽기</a>
                    </div>
                </div>
            </li>

            {{-- 메시지 --}}
            <li class="nav-item dropdown">
                <a class="nav-icon dropdown-toggle" href="#" id="alertsDropdown"
                    data-bs-toggle="dropdown">
                    <div class="position-relative">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
                            <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6"/>
                        </svg>


                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end py-0"
                    aria-labelledby="alertsDropdown">
                    <div class="dropdown-menu-header">
                        4 New Notifications
                    </div>
                    <div class="list-group">
                        <a href="#" class="list-group-item">
                            <div class="row g-0 align-items-center">
                                <div class="col-2">
                                    <i class="text-danger" data-lucide="alert-circle"></i>
                                </div>
                                <div class="col-10">
                                    <div>Update completed</div>
                                    <div class="text-muted small mt-1">Restart server 12 to complete the
                                        update.</div>
                                    <div class="text-muted small mt-1">2h ago</div>
                                </div>
                            </div>
                        </a>
                        <a href="#" class="list-group-item">
                            <div class="row g-0 align-items-center">
                                <div class="col-2">
                                    <i class="text-warning" data-lucide="bell"></i>
                                </div>
                                <div class="col-10">
                                    <div>Lorem ipsum</div>
                                    <div class="text-muted small mt-1">Aliquam ex eros, imperdiet vulputate
                                        hendrerit et.</div>
                                    <div class="text-muted small mt-1">6h ago</div>
                                </div>
                            </div>
                        </a>
                        <a href="#" class="list-group-item">
                            <div class="row g-0 align-items-center">
                                <div class="col-2">
                                    <i class="text-primary" data-lucide="home"></i>
                                </div>
                                <div class="col-10">
                                    <div>Login from 192.186.1.1</div>
                                    <div class="text-muted small mt-1">8h ago</div>
                                </div>
                            </div>
                        </a>
                        <a href="#" class="list-group-item">
                            <div class="row g-0 align-items-center">
                                <div class="col-2">
                                    <i class="text-success" data-lucide="user-plus"></i>
                                </div>
                                <div class="col-10">
                                    <div>New connection</div>
                                    <div class="text-muted small mt-1">Anna accepted your request.</div>
                                    <div class="text-muted small mt-1">12h ago</div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="dropdown-menu-footer">
                        <a href="#" class="text-muted">Show all notifications</a>
                    </div>
                </div>
            </li>



            {{-- 언어 설정 --}}
            <li class="nav-item dropdown">
                <a class="nav-flag dropdown-toggle" href="#" id="languageDropdown"
                    data-bs-toggle="dropdown">
                    <img src="/images/flags/us.png" alt="English" />
                </a>
                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                    <a class="dropdown-item" href="#">
                        <img src="/images/flags/us.png" alt="English" width="20"
                            class="align-middle me-1" />
                        <span class="align-middle">English</span>
                    </a>
                    <a class="dropdown-item" href="#">
                        <img src="/images/flags/es.png" alt="Spanish" width="20"
                            class="align-middle me-1" />
                        <span class="align-middle">Spanish</span>
                    </a>
                    <a class="dropdown-item" href="#">
                        <img src="/images/flags/de.png" alt="German" width="20"
                            class="align-middle me-1" />
                        <span class="align-middle">German</span>
                    </a>
                    <a class="dropdown-item" href="#">
                        <img src="/images/flags/nl.png" alt="Dutch" width="20"
                            class="align-middle me-1" />
                        <span class="align-middle">Dutch</span>
                    </a>
                </div>
            </li>

            {{-- 사용자 정보 --}}
            <li class="nav-item dropdown">
                <a class="nav-icon dropdown-toggle d-inline-block d-sm-none" href="#"
                    data-bs-toggle="dropdown">
                    <i class="align-middle" data-lucide="settings"></i>
                </a>

                <a class="nav-link dropdown-toggle d-none d-sm-inline-block" href="#"
                    data-bs-toggle="dropdown">
                    @auth
                    <img src="/home/user/avatar/{{Auth::user()->id}}"
                        class="avatar img-fluid rounded-circle me-1 mt-n2 mb-n2"
                        alt="Chris Wood" width="40" height="40" />
                    <span>{{Auth::user()->name}}</span>
                    @endauth


                </a>
                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="/admin/profile">
                        <i class="align-middle me-1" data-lucide="user"></i>
                        프로필
                    </a>
                    <a class="dropdown-item" href="#">
                        <i class="align-middle me-1"
                            data-lucide="pie-chart"></i>
                            Analytics
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="/admin/profile/setting">
                        설정
                    </a>
                    <a class="dropdown-item" href="javascript:void(0)">Help</a>
                    @auth
                    <a class="dropdown-item" href="/logout">
                        로그아웃
                    </a>
                    @endauth
                </div>
            </li>
        </ul>
    </div>
</nav>
