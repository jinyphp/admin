{{-- <x-ui-divider>라라벨 및 JinyPHP</x-ui-divider> --}}

<div class="row">
    <div class="col-lg-6 col-xl-5 d-flex">
        <div class="w-100 h-100">

            <div class="row">
                <div class="col-sm-6 col-lg-12 col-xxl-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header">
                            <x-flex-between>
                                <div>
                                    <h5 class="card-title">
                                        <a href="/admin/laravel">
                                            라라벨 시스템
                                        </a>
                                    </h5>
                                    <h6 class="card-subtitle text-muted">
                                        JinyPHP의 기본베이스인 라라벨을 관리합니다.
                                    </h6>
                                </div>
                                <div>
                                    @icon('info-circle.svg')
                                </div>
                            </x-flex-between>
                        </div>
                        <div class="card-body">
                            <x-badge-secondary>
                                <a href="/admin/laravel/migrations">
                                    마이그레이션
                                </a>
                            </x-badge-secondary>

                            <x-badge-secondary>
                                <a href="/admin/laravel/view">
                                    Cache
                                </a>
                            </x-badge-secondary>

                            <a href="/admin/actions">
                                Actions
                            </a>

                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-12 col-xxl-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header">
                            <x-flex-between>
                                <div>
                                    <h5 class="card-title">
                                        <a href="/admin/auth">
                                            회원관리 및 인증
                                        </a>
                                    </h5>
                                    <h6 class="card-subtitle text-muted">
                                        가입된 회원 및 인증을 관리합니다.
                                    </h6>
                                </div>
                                <div>
                                    @icon('info-circle.svg')
                                </div>
                            </x-flex-between>
                        </div>
                        <div class="card-body">
                            <a href="/admin/permit/roles">
                                권환설정
                            </a>
                        </div>

                    </div>
                </div>
            </div>

            <div class="row d-lg-none d-xxl-flex">

                <div class="col-sm-6 col-lg-12 col-xxl-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header">
                            <h5 class="card-title">모듈관리</h5>
                            <h6 class="card-subtitle text-muted">
                                모듈기능을 통하여 웹서비스를 확장합니다.
                            </h6>
                        </div>
                        <div class="card-body">
                            <a href="/admin/license">
                                라이선스
                            </a>
                            <x-badge-primary>설치모듈</x-badge-primary>
                            <x-badge-success>모듈스토어</x-badge-success>
                            <x-badge-secondary>설정</x-badge-secondary>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-12 col-xxl-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header">
                            <h5 class="card-title">테마관리</h5>
                            <h6 class="card-subtitle text-muted">
                                테마를 이용하여 다양한 디자인을 적용합니다.
                            </h6>
                        </div>
                        <div class="card-body">
                            <x-badge-primary>설치테마</x-badge-primary>
                            <x-badge-success>테마스토어</x-badge-success>
                            <x-badge-secondary>설정</x-badge-secondary>

                            <span>위젯</span>
                        </div>
                    </div>
                </div>






            </div>
        </div>
    </div>

    <div class="col-lg-6 col-xl-7">
        <div class="card flex-fill w-100 h-100">
            <div class="card-header">
                <div class="card-actions float-end">
                    <div class="dropdown position-relative">
                        <a href="#" data-bs-toggle="dropdown" data-bs-display="static">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" data-lucide="more-horizontal"
                                class="lucide lucide-more-horizontal align-middle">
                                <circle cx="12" cy="12" r="1"></circle>
                                <circle cx="19" cy="12" r="1"></circle>
                                <circle cx="5" cy="12" r="1"></circle>
                            </svg>
                        </a>

                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="/admin/locale/country">국가</a>
                            <a class="dropdown-item" href="/admin/locale/language">언어</a>
                            <a class="dropdown-item" href="#">통화</a>
                        </div>
                    </div>
                </div>
                <a href="/admin/locale" class="text-decoration-none">
                    <h5 class="card-title mb-0">지역설정</h5>
                </a>
            </div>
            <div class="card-body p-2">
                <div id="world_map" style="height:350px;"></div>

                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        const map = new jsVectorMap({
                            selector: '#world_map',
                            map: 'world',
                            zoomButtons: true,

                            regionStyle: {
                                initial: {
                                    fill: '#e4e4e4'
                                }
                            },

                            zoomOnScroll: false,

                            markers: [
                                // {
                                //     name: "대한민국",
                                //     coords: [37.5665, 126.9780] // 위도(latitude), 경도(longitude)
                                // },
                                // {
                                //     name: "미국",
                                //     coords: [38.8977, -77.0365]
                                // },
                                // {
                                //     name: "영국",
                                //     coords: [51.5074, -0.1278]
                                // }
                                @foreach (DB::table('country')->get() as $country)
                                    {
                                        name: "{{ $country->name }}",
                                        coords: [{{ $country->latitude }}, {{ $country->longitude }}]
                                    }
                                    @if (!$loop->last)
                                        ,
                                    @endif
                                @endforeach
                            ],

                            markerStyle: {
                                initial: {
                                    fill: '#4680ff'
                                }
                            },

                            labels: {
                                markers: {
                                    render: (marker) => marker.name
                                }
                            }
                        });
                    });
                </script>
            </div>

        </div>
    </div>
</div>
