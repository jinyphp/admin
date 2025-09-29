<!-- Desktop Header -->
<header class="hidden lg:block sticky top-0 z-40 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-white/10">
    <div class="flex h-16 items-center gap-x-4 px-4 sm:px-6 lg:px-8">
        <!-- Page Title -->
        <h1 class="flex-1 text-lg font-semibold text-gray-900 dark:text-white">
            @yield('page-title', 'Dashboard')
        </h1>

        <!-- Top Navigation -->
        <div class="flex items-center gap-x-4">
            <!-- Search -->
            <button type="button" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <span class="sr-only">Search</span>
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </button>

            <!-- Notifications -->
            <button type="button" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <span class="sr-only">View notifications</span>
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </button>

            <!-- Profile Dropdown -->
            <div class="relative">
                <button type="button" class="flex items-center text-sm rounded-full bg-white dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <span class="sr-only">Open user menu</span>
                    <img class="h-8 w-8 rounded-full" 
                         src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" 
                         alt="">
                </button>
            </div>
        </div>
    </div>
</header>