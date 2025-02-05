<div class="row">
    <div class="col-12 col-md-6 col-xxl d-flex">
        <div class="card flex-fill">
            <div class="card-body">
                <div class="row">
                    <div class="col mt-0">
                        <a href="/admin/site" class="text-decoration-none">
                            <h5 class="card-title">
                                사이트관리
                            </h5>
                        </a>
                    </div>

                    <div class="col-auto">

                    </div>
                </div>
                <h6 class="card-subtitle text-muted">
                    웹사이트를 운영관리 할 수 있는 CSM 도구 입니다.
                </h6>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xxl d-flex">
        <div class="card flex-fill">
            <div class="card-body">
                <div class="row">
                    <div class="col mt-0">
                        <a href="/admin/shop" class="text-decoration-none">
                            <h5 class="card-title">
                                쇼핑몰
                            </h5>
                        </a>
                    </div>

                    <div class="col-auto">

                    </div>
                </div>
                <h6 class="card-subtitle text-muted">
                    웹사이트를 운영관리 할 수 있는 CSM 도구 입니다.
                </h6>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xxl d-flex">
        <div class="card flex-fill">
            <div class="card-body">
                <div class="row">
                    <div class="col mt-0">
                        <a href="/admin/service" class="text-decoration-none">
                            <h5 class="card-title">
                                서비스관리
                            </h5>
                        </a>
                    </div>

                    <div class="col-auto">

                    </div>
                </div>
                <h6 class="card-subtitle text-muted">
                    웹사이트를 운영관리 할 수 있는 CSM 도구 입니다.
                </h6>

            </div>
        </div>
    </div>

</div>

@includeIf("jiny-admin::admin.dashboard.laravel")

{{-- @includeIf("jiny-admin::dashboard.module") --}}

{{-- @includeIf("jiny-admin::dashboard.site") --}}

{{-- @includeIf("jiny-admin::admin.dashboard.shop") --}}

{{-- @includeIf("jiny-admin::admin.dashboard.service") --}}

@includeIf("jiny-admin::admin.dashboard.erp")
