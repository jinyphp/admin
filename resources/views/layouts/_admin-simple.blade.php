<!DOCTYPE html>
<html lang="ko" class="h-full bg-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {}
            }
        }
    </script>

    <!-- Alpine.js CDN for interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @livewireStyles
</head>
<body class="h-full">
    <!-- Desktop Sidebar -->
    <div class="hidden bg-gray-900 lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
        <div class="flex grow flex-col gap-y-5 overflow-y-auto border-r border-gray-200 px-6">
            <div class="flex h-16 shrink-0 items-center">
                <h2 class="text-white">Admin Panel</h2>
            </div>
            <nav class="flex flex-1 flex-col">
                <ul role="list" class="flex flex-1 flex-col gap-y-7">
                    <li>
                        <ul role="list" class="-mx-2 space-y-1">
                            <li>
                                <a href="#" class="group flex gap-x-3 rounded-md text-gray-400 hover:text-white hover:bg-white/5 p-2 text-sm/6 font-semibold">
                                    Dashboard
                                </a>
                            </li>
                            <li>
                                <a href="#" class="group flex gap-x-3 rounded-md text-gray-400 hover:text-white hover:bg-white/5 p-2 text-sm/6 font-semibold">
                                    Templates
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <main class="py-10 lg:pl-72">
        <div class="px-4 sm:px-6 lg:px-8">
            @yield('content')
        </div>
    </main>

    @livewireScripts
</body>
</html>