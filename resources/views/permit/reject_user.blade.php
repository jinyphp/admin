<x-www-app>
    {{-- page-center --}}
    <main class="d-flex w-100 h-100">
        <div class="container d-flex flex-column">
            <div class="row vh-100">
                <div class="col-sm-10 col-md-5 mx-auto d-table h-100">
                    <div class="d-table-cell align-middle">

                        <section>
                            <header class="d-flex justify-content-between">
                                <h1 class="h2 mt-auto">관리자 페이지에 접속할 수 없습니다.</h1>
                            </header>

                            <div class="card">
                                <div class="card-body">
                                    <div class="text-center">
                                        {{$user->name}} 님은 권환이 없는 사용자로 관리자 페이지 접속을 시도하였습니다.
                                        접속 기록은 운영자에게 통보됩니다.
                                    </div>

                                    <div class="text-center mt-4">
                                        접속일자 : {{ date('Y-m-d H:i:s') }}
                                    </div>
                                </div>
                            </div>


                            <!-- Footer -->
                            <footer class="mt-4">
                                <div class="nav mb-4">
                                    <a class="nav-link text-decoration-underline p-0" href="/support/help">도움이
                                        필요하신가요?</a>
                                </div>
                                <p class="fs-xs mb-0">
                                    &copy; All rights reserved.
                                    Made by <span class="animate-underline"><a
                                            class="animate-target text-dark-emphasis text-decoration-none"
                                            href="https://jinyphp.com/" target="_blank" rel="noreferrer">jinyphp</a>
                                    </span>
                                </p>
                            </footer>
                        </section>

                        <!-- -->
                    </div>
                </div>
            </div>
        </div>
    </main>
</x-www-app>
