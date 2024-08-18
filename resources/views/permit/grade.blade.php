<x-app>
    <x-bootstrap>
        <x-page-center>
            <div class="text-center mt-4">
                <h1 class="h2">접근 권환 등급이 없습니다.</h1>
                <p class="lead"></p>
            </div>

            <div class="card">
                <div class="card-body">
                    <p class="text-gray-500">
                            접속할 수 있는 관리자 등급이 아닙니다.
                    </p>
                    <p class="text-red-500">
                        권환이 없는 사용자가 지속적으로 접속을 시도하는 경우, 운영자에게 통보됩니다.
                    </p>

                    <ul>
                        <li>
                            {{$user->email}}
                        </li>
                        <li>
                            {{date("Y-m-d H:i:s")}}
                        </li>
                    </ul>

                </div>
            </div>
        </x-page-center>
    </x-bootstrap>
</x-app>
