<!-- Desktop Sidebar -->
<div class="hidden bg-gray-900 lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
    <div class="flex grow flex-col gap-y-5 overflow-y-auto border-r border-gray-200 px-6 dark:border-white/10 dark:bg-black/10">
        <div class="flex h-16 shrink-0 items-center">
            <img src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=600" alt="Admin Panel" class="h-8 w-auto dark:hidden" />
            <img src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=500" alt="Admin Panel" class="relative h-8 w-auto not-dark:hidden" />
        </div>
        <nav class="flex flex-1 flex-col">
            <ul role="list" class="flex flex-1 flex-col gap-y-7">
                <li>
                    @include('jiny-admin::layouts.partials.navigation')
                </li>
                <li>
                    @include('jiny-admin::layouts.partials.teams')
                </li>
                <li class="-mx-6 mt-auto">
                    @include('jiny-admin::layouts.partials.profile')
                </li>
            </ul>
        </nav>
    </div>
</div>