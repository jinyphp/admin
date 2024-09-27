<x-ui-divider>라라벨 및 JinyPHP</x-ui-divider>
<div class="row">
    <div class="col-4">
        <div class="card">
            <div class="card-header">
                <x-flex-between>
                    <div>
                        <h5 class="card-title">
                            <a href="/admin/laravel">
                            라라벨
                            </a>
                        </h5>
                        <h6 class="card-subtitle text-muted">
                            JinyPHP의 기본베이스인 라라벨을 관리합니다.
                        </h6>
                    </div>
                    <div>
                        @icon("info-circle.svg")
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
            </div>
        </div>
    </div>

    <div class="col-4">
        <div class="card">
            <div class="card-header">
                <x-flex-between>
                    <div>
                        <h5 class="card-title">
                            <a href="/admin/actions">
                            Actions
                            </a>
                        </h5>
                        <h6 class="card-subtitle text-muted">
                         uri 별 actions 설정값을 관리합니다.
                        </h6>
                    </div>
                    <div>
                        @icon("info-circle.svg")
                    </div>
                </x-flex-between>
            </div>
            <div class="card-body">

            </div>
        </div>

    </div>

    <div class="col-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <a href="/admin/locale">지역설정</a>
                </h5>
                <h6 class="card-subtitle text-muted">
                    시스템의 서비스 지역을 설정합니다.
                </h6>
            </div>
            <div class="card-body">
                <x-badge-primary>
                    <a href="/admin/locale/country">
                    국가
                    </a>
                </x-badge-primary>

                <x-badge-success>
                    <a href="/admin/locale/language">
                    언어
                    </a>
                </x-badge-success>

                <x-badge-secondary>통화</x-badge-secondary>
            </div>
        </div>

    </div>

    <div class="col-4">
        <div class="card">
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
                        @icon("info-circle.svg")
                    </div>
                </x-flex-between>
            </div>
            <div class="card-body">

            </div>
        </div>

    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">모듈관리</h5>
                <h6 class="card-subtitle text-muted">
                    ...
                </h6>
            </div>
            <div class="card-body">
                <x-badge-primary>설치모듈</x-badge-primary>
                <x-badge-success>모듈스토어</x-badge-success>
                <x-badge-secondary>설정</x-badge-secondary>
            </div>
        </div>
    </div>
</div>

<x-ui-divider>웹서비스 관리</x-ui-divider>


<div class="page-title-box mt-4">
    <x-flex class="align-items-center gap-2">
        <h2 class="align-middle h3 d-inline">
            웹서비스 관리
        </h2>
        <x-badge-info>Admin</x-badge-info>
    </x-flex>
    <p>
        웹사이트 및 쇼핑몰등의 외부 서비스를 관리합니다.
    </p>
</div>

{{-- 시스템 관련 기능 --}}
<div class="row">

    <div class="col-4">
        <div class="card">
            <div class="card-header">
                <x-flex-between>
                    <div>
                        <h5 class="card-title">
                            <a href="/admin/site">
                            사이트관리
                            </a>
                        </h5>
                        <h6 class="card-subtitle text-muted">
                            웹사이트를 운영관리 할 수 있는 CSM 도구 입니다.
                        </h6>
                    </div>
                    <div>
                        @icon("info-circle.svg")
                    </div>
                </x-flex-between>
            </div>

        </div>
    </div>


    <div class="col-4">
        <div class="card">
            <div class="card-header">
                <x-flex-between>
                    <div>
                        <h5 class="card-title">
                            <a href="/admin/shop">
                            쇼핑몰
                            </a>
                        </h5>
                        <h6 class="card-subtitle text-muted">
                            쇼핑몰을 관리합니다.
                        </h6>
                    </div>
                    <div>
                        @icon("info-circle.svg")
                    </div>
                </x-flex-between>
            </div>

        </div>
    </div>

    <div class="col-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">UI 및 위젯</h5>
                <h6 class="card-subtitle text-muted">
                    UI 컴포넌트와 위젯을 관리합니다.
                </h6>
            </div>
            <div class="card-body">

            </div>
        </div>
    </div>

    <div class="col-4">
        <div class="card">
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
            </div>
        </div>
    </div>

    <div class="col-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <a href="/admin/service/">
                        서비스관리
                    </a>
                </h5>
                <h6 class="card-subtitle text-muted">
                    구독형 서비스를 관리 합니다.
                </h6>
            </div>
            <div class="card-body">
                <x-badge-primary>Plan</x-badge-primary>
                <x-badge-primary>Reseller</x-badge-primary>
            </div>
        </div>
    </div>
</div>

<hr>

<div class="page-title-box mt-4">
    <x-flex class="align-items-center gap-2">
        <h2 class="align-middle h3 d-inline">
            ERP 기능 관리
        </h2>
        <x-badge-info>Admin</x-badge-info>
    </x-flex>
    <p>
        업무 처리를 관리하는 ERP 기능입니다.
    </p>
</div>

<div class="row">
    <div class="col-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">ERP 기본</h5>
                <h6 class="card-subtitle text-muted">
                    업무를 처리할 수 있는 시스템 입니다.
                </h6>
            </div>
            <div class="card-body">
                <x-badge-primary>사업자관리</x-badge-primary>





            </div>
        </div>
    </div>

    <div class="col-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <a href="/admin/hr">
                        HR 인사관리
                    </a>
                </h5>
                <h6 class="card-subtitle text-muted">

                </h6>
            </div>
            <div class="card-body">
                <x-badge-primary>근태관리</x-badge-primary>
                <x-badge-primary>급여관리</x-badge-primary>
                <x-badge-primary>발급</x-badge-primary>





            </div>
        </div>
    </div>

    <div class="col-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">기업 자산관리</h5>
                <h6 class="card-subtitle text-muted">

                </h6>
            </div>
            <div class="card-body">

            </div>
        </div>
    </div>

    <div class="col-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <a href="/admin/crm/">
                        CRM 고객관리
                    </a>
                </h5>
                <h6 class="card-subtitle text-muted">

                </h6>
            </div>
            <div class="card-body">

            </div>
        </div>
    </div>

    <div class="col-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <a href="/admin/sales/">
                        Sales 판매재고
                    </a>
                </h5>
                <h6 class="card-subtitle text-muted">

                </h6>
            </div>
            <div class="card-body">

            </div>
        </div>
    </div>

    <div class="col-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">업무전자결제</h5>
                <h6 class="card-subtitle text-muted">

                </h6>
            </div>
            <div class="card-body">

            </div>
        </div>
    </div>

    <div class="col-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">SCM 공급망관리</h5>
                <h6 class="card-subtitle text-muted">

                </h6>
            </div>
            <div class="card-body">

            </div>
        </div>
    </div>


    <div class="col-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Work 업무프로세스</h5>
                <h6 class="card-subtitle text-muted">

                </h6>
            </div>
            <div class="card-body">

            </div>
        </div>
    </div>



    <div class="col-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">업무관리</h5>
                <h6 class="card-subtitle text-muted">
                    업무관리 협어 도구를 관리 합니다.
                </h6>
            </div>
            <div class="card-body">

            </div>
        </div>
    </div>
</div>

