<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>JinyPHP</title>
    {{-- @vite('resources/css/app.css') --}}
    <link rel="stylesheet" href="{{ asset('css/app.4387550e.css') }}">
    @stack('css')
    @livewireStyles
</head>

<body>
    <!-- Page Container -->
    <div id="page-container" class="flex flex-col mx-auto w-full min-h-screen bg-gray-100">
        <!-- Page Content -->
        <main id="page-content" class="flex flex-auto flex-col max-w-full">
            <div
                class="min-h-screen flex items-center justify-center relative overflow-hidden max-w-10xl mx-auto p-4 lg:p-8 w-full">
                <!-- Patterns Background -->
                <div
                    class="pattern-dots-md text-gray-300 absolute top-0 right-0 w-32 h-32 lg:w-48 lg:h-48 transform translate-x-16 translate-y-16">
                </div>
                <div
                    class="pattern-dots-md text-gray-300 absolute bottom-0 left-0 w-32 h-32 lg:w-48 lg:h-48 transform -translate-x-16 -translate-y-16">
                </div>
                <!-- END Patterns Background -->

                <!-- Sign In Section -->
                <div class="py-6 lg:py-0 w-full md:w-8/12 lg:w-6/12 xl:w-4/12 relative">
                    <!-- Header -->
                    <div class="mb-8 text-center">
                        <h1 class="text-4xl font-bold inline-flex items-center mb-1 space-x-3">
                            <span>Access Reject</span>
                        </h1>
                        <p class="text-gray-500">
                            관리자만 접속이 가능합니다.
                        </p>
                        <p class="text-red-500">
                            권환이 없는 사용자가 지속적으로 접속을 시도하는 경우, 운영자에게 통보됩니다.
                        </p>

                        @if (session('message'))
                        <div class="alert alert-success">
                            {{ session('error') }}
                        </div>
                        @endif
                    </div>
                    <!-- END Header -->

                    <!-- Footer -->
                    <div class="text-sm text-gray-500 text-center mt-6">
                        <a class="font-medium text-blue-600 hover:text-blue-400" href="https://tailkit.com"
                            target="_blank">JinyERP</a> by <a class="font-medium text-blue-600 hover:text-blue-400"
                            href="https://www.jinyphp.com" target="_blank">JinyPHP with Laravel</a>
                    </div>
                    <!-- END Footer -->
                </div>
                <!-- END Sign In Section -->
            </div>
        </main>
        <!-- END Page Content -->
    </div>
    <!-- END Page Container -->

    @livewireScripts
    @stack('scripts')
</body>

</html>
