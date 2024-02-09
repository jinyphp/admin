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
                            <span>Jiny Administrator</span>
                        </h1>
                        <p class="text-gray-500">
                            관리자 접속을 위해서는 로그인이 필요로 합니다.
                        </p>
                    </div>
                    <!-- END Header -->

                    <!-- Sign In Form -->
                    <div class="flex flex-col rounded shadow-sm bg-white overflow-hidden">
                        <div class="p-5 lg:p-6 grow w-full">
                            <div class="sm:p-5 lg:px-10 lg:py-8">
                                <form onsubmit="return false;" class="space-y-6">
                                    <div class="space-y-1">
                                        <label for="email" name="email" class="font-medium">Email</label>
                                        <input
                                            class="block border border-gray-200 rounded px-5 py-3 leading-6 w-full focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                                            type="email" id="email" name="email" placeholder="Enter your email">
                                    </div>
                                    <div class="space-y-1">
                                        <label for="password" name="email" class="font-medium">Password</label>
                                        <input
                                            class="block border border-gray-200 rounded px-5 py-3 leading-6 w-full focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                                            type="password" id="password" name="password"
                                            placeholder="Enter your password">
                                    </div>
                                    <div>
                                        <button type="submit"
                                            class="inline-flex justify-center items-center space-x-2 border font-semibold focus:outline-none w-full px-4 py-3 leading-6 rounded border-blue-700 bg-blue-700 text-white hover:text-white hover:bg-blue-800 hover:border-blue-800 focus:ring focus:ring-blue-500 focus:ring-opacity-50 active:bg-blue-700 active:border-blue-700">
                                            Sign In
                                        </button>
                                        <div
                                            class="space-y-2 sm:flex sm:items-center sm:justify-between sm:space-x-2 sm:space-y-0 mt-4">
                                            <label class="flex items-center">
                                                <input type="checkbox" id="remember_me" name="remember_me"
                                                    class="border border-gray-200 rounded h-4 w-4 text-blue-500 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                                                <span class="ml-2">
                                                    Remember me
                                                </span>
                                            </label>
                                            <a href="javascript:void(0)"
                                                class="inline-block text-blue-600 hover:text-blue-400">Forgot
                                                Password?</a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="py-4 px-5 lg:px-6 w-full text-sm text-center bg-gray-50">
                            Don’t have an account yet?
                            <a class="font-medium text-blue-600 hover:text-blue-400" href="javascript:void(0)">Join us
                                today</a>
                        </div>
                    </div>
                    <!-- END Sign In Form -->

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
