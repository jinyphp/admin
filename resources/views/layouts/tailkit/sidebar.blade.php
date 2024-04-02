<nav id="page-sidebar"
            class="flex flex-col fixed top-0 left-0 bottom-0 w-full lg:w-64 h-full bg-[#313947] text-gray-200 z-50 transform transition-transform duration-500 ease-out"
            x-bind:class="{
                '-translate-x-full': !mobileSidebarOpen,
                'translate-x-0': mobileSidebarOpen,
                'lg:-translate-x-full': !desktopSidebarOpen,
                'lg:translate-x-0': desktopSidebarOpen,
            }"
            aria-label="Main Sidebar Navigation">
            <!-- Sidebar Header -->
            <div
                class="h-16 bg-gray-600 bg-opacity-25 flex-none flex items-center justify-between lg:justify-center px-4 w-full">
                <!-- Brand -->
                <a href="/admin"
                    class="inline-flex items-center space-x-2 font-bold text-lg tracking-wide text-white-600 hover:text-white-400 text-white hover:opacity-75">

                    <span>Admin</span>
                </a>
                <!-- END Brand -->

                <!-- Close Sidebar on Mobile -->
                <div class="lg:hidden">
                    <button type="button"
                        class="inline-flex justify-center items-center space-x-2 border font-semibold focus:outline-none px-3 py-2 leading-5 text-sm rounded border-transparent text-white opacity-75 hover:opacity-100 focus:ring focus:ring-gray-500 focus:ring-opacity-25 active:opacity-75"
                        x-on:click="mobileSidebarOpen = false">
                        <svg class="hi-solid hi-x inline-block w-4 h-4 -mx-1" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
                <!-- END Close Sidebar on Mobile -->
            </div>
            <!-- END Sidebar Header -->

            <!-- Sidebar Navigation -->
            <div class="overflow-y-auto">
                <div class="p-4 w-full">

                    <ul class="text-gray-300 space-y-1">
                        <li>
                            <!-- -->
                            <div>
                                <a href="/admin"
                                    class="flex items-center px-3 py-2 hover:bg-gray-800 hover:bg-opacity-50 hover:text-white rounded-t transition ease-out duration-100">
                                    {{--@icon("home.svg")--}}
                                    <span class="grow text-sm font-medium">Dashboard</span>
                                    <!----> <!---->
                                </a>
                            </div>
                            <!---->
                        </li>
                        <li>
                            <div
                                class="px-3 mt-5 mb-2 text-xs font-medium uppercase tracking-wider text-white text-opacity-40">
                                Backend UI
                            </div>
                            <!----> <!---->
                        </li>
                        <li><!---->
                            <a href="#"
                                class="flex items-center px-3 py-2 hover:bg-gray-800 hover:bg-opacity-50 hover:text-white rounded-t transition ease-out duration-100 rounded-b">
                                {{--@icon('layout.svg')--}}
                                <span class="grow text-sm font-medium">Layouts</span>
                                <!---->
                                <svg
                                    class="hi-solid hi-chevron-down inline-block w-5 h-5 ml-2 transform transition ease-out duration-150 text-gray-500 rotate-90"
                                    fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </a>

                            <ul class="p-2 text-gray-400 bg-gray-800 bg-opacity-50 text-sm rounded-b ">
                                <li><!---->
                                    <div><a href="#/app/backend/layouts/stacked"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Stacked</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">10</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/layouts/dark-sidebar"
                                            class="text-white flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"
                                            aria-current="page"><!----> <span class="grow">Dark Sidebar</span> <!---->
                                            <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">5</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/layouts/light-sidebar"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Light Sidebar</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">5</span></a>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        <li><!----> <a href="#"
                                class="flex items-center px-3 py-2 hover:bg-gray-800 hover:bg-opacity-50 hover:text-white rounded-t transition ease-out duration-100 rounded-b"><svg
                                    class="hi-solid hi-puzzle inline-block w-5 h-5 mr-2 text-gray-500 text-opacity-75"
                                    fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M10 3.5a1.5 1.5 0 013 0V4a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-.5a1.5 1.5 0 000 3h.5a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-.5a1.5 1.5 0 00-3 0v.5a1 1 0 01-1 1H6a1 1 0 01-1-1v-3a1 1 0 00-1-1h-.5a1.5 1.5 0 010-3H4a1 1 0 001-1V6a1 1 0 011-1h3a1 1 0 001-1v-.5z">
                                    </path>
                                </svg> <span class="grow text-sm font-medium">Components</span> <!----> <svg
                                    class="hi-solid hi-chevron-down inline-block w-5 h-5 ml-2 transform transition ease-out duration-150 text-gray-500 rotate-90"
                                    fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd"></path>
                                </svg></a>
                            <ul class="p-2 text-gray-400 bg-gray-800 bg-opacity-50 text-sm rounded-b hidden">
                                <li><!---->
                                    <div><a href="#/app/backend/components/alerts"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Alerts</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">6</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/components/avatars"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Avatars</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">10</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/components/badges"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Badges</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">6</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/components/banners"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Banners</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">8</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/components/breadcrumbs"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Breadcrumbs</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">4</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/components/buttons"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Buttons</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">7</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/components/button-groups"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Button Groups</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">5</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/components/cards"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Cards</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">7</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/components/cards-in-grid"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Cards in Grid</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">3</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/components/card-headings"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Card Headings</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">4</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/components/dividers"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Dividers</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">13</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/components/dropdowns"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Dropdowns</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">4</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/components/form-actions"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Form Actions</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">5</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/components/form-elements"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Form Elements</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">11</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/components/form-input-groups"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Form Input Groups</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">7</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/components/form-layouts"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Form Layouts</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">5</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/components/form-switches"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Form Switches</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">7</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/components/list-groups"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">List Groups</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">6</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/components/media-objects"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Media Objects</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">4</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/components/modals"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Modals</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">4</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/components/navigation"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Navigation</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">9</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/components/page-headings"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Page Headings</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">4</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/components/pagination"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Pagination</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">4</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/components/progress-bars"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Progress Bars</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">6</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/components/statistics"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Statistics</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">6</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/components/tables"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Tables</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">4</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/components/tabs"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Tabs</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">8</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/components/timeline"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Timeline</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">8</span></a>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        <li><!----> <a href="#"
                                class="flex items-center px-3 py-2 hover:bg-gray-800 hover:bg-opacity-50 hover:text-white rounded-t transition ease-out duration-100 rounded-b"><svg
                                    class="hi-solid hi-document-text inline-block w-5 h-5 mr-2 text-gray-500 text-opacity-75"
                                    fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                        d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z"
                                        clip-rule="evenodd"></path>
                                </svg> <span class="grow text-sm font-medium">Pages</span> <!----> <svg
                                    class="hi-solid hi-chevron-down inline-block w-5 h-5 ml-2 transform transition ease-out duration-150 text-gray-500 rotate-90"
                                    fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd"></path>
                                </svg></a>
                            <ul class="p-2 text-gray-400 bg-gray-800 bg-opacity-50 text-sm rounded-b hidden">
                                <li>
                                    <div
                                        class="block mx-3 py-2 mb-2 font-medium text-xs text-white text-opacity-50 uppercase tracking-wider border-b-2 border-white border-opacity-5 mt-2">
                                        Authentication</div> <!---->
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/pages/auth/sign-in"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Sign In</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">2</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/pages/auth/sign-up"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Sign Up</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">2</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/pages/auth/password-reset"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Password Reset</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">2</span></a>
                                    </div>
                                </li>
                                <li>
                                    <div
                                        class="block mx-3 py-2 mb-2 font-medium text-xs text-white text-opacity-50 uppercase tracking-wider border-b-2 border-white border-opacity-5 mt-4">
                                        Special</div> <!---->
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/pages/special/installation"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Installation</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">1</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/pages/special/status"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Status</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">1</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/pages/special/maintenance"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Maintenance</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">1</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/pages/special/errors"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Errors</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">6</span></a>
                                    </div>
                                </li>
                                <li>
                                    <div
                                        class="block mx-3 py-2 mb-2 font-medium text-xs text-white text-opacity-50 uppercase tracking-wider border-b-2 border-white border-opacity-5 mt-4">
                                        Partials</div> <!---->
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/pages/partials/invoice"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Invoice</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">1</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/backend/pages/partials/user-profile"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">User Profile</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">1</span></a>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <div
                                class="px-3 mt-5 mb-2 text-xs font-medium uppercase tracking-wider text-white text-opacity-40">
                                Marketing UI</div> <!----> <!---->
                        </li>
                        <li><!----> <a href="#"
                                class="flex items-center px-3 py-2 hover:bg-gray-800 hover:bg-opacity-50 hover:text-white rounded-t transition ease-out duration-100 rounded-b"><svg
                                    class="hi-solid hi-template inline-block w-5 h-5 mr-2 text-gray-500 text-opacity-75"
                                    fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z">
                                    </path>
                                </svg> <span class="grow text-sm font-medium">Layouts</span> <!----> <svg
                                    class="hi-solid hi-chevron-down inline-block w-5 h-5 ml-2 transform transition ease-out duration-150 text-gray-500 rotate-90"
                                    fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd"></path>
                                </svg></a>
                            <ul class="p-2 text-gray-400 bg-gray-800 bg-opacity-50 text-sm rounded-b hidden">
                                <li><!---->
                                    <div><a href="#/app/marketing/layouts/hero-light"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Hero Light</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">1</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/marketing/layouts/hero-dark"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Hero Dark</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">1</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/marketing/layouts/hero-brand"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Hero Brand</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">1</span></a>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        <li><!----> <a href="#"
                                class="flex items-center px-3 py-2 hover:bg-gray-800 hover:bg-opacity-50 hover:text-white rounded-t transition ease-out duration-100 rounded-b"><svg
                                    class="hi-solid hi-collection inline-block w-5 h-5 mr-2 text-gray-500 text-opacity-75"
                                    fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z">
                                    </path>
                                </svg> <span class="grow text-sm font-medium">Sections</span> <!----> <svg
                                    class="hi-solid hi-chevron-down inline-block w-5 h-5 ml-2 transform transition ease-out duration-150 text-gray-500 rotate-90"
                                    fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd"></path>
                                </svg></a>
                            <ul class="p-2 text-gray-400 bg-gray-800 bg-opacity-50 text-sm rounded-b hidden">
                                <li>
                                    <div
                                        class="block mx-3 py-2 mb-2 font-medium text-xs text-white text-opacity-50 uppercase tracking-wider border-b-2 border-white border-opacity-5 mt-2">
                                        Main Structure</div> <!---->
                                </li>
                                <li><!---->
                                    <div><a href="#/app/marketing/sections/hero"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Hero</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">12</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/marketing/sections/main-headers"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Main Headers</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">5</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/marketing/sections/page-headers"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Page Headers</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">10</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/marketing/sections/footers"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Footers</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">10</span></a>
                                    </div>
                                </li>
                                <li>
                                    <div
                                        class="block mx-3 py-2 mb-2 font-medium text-xs text-white text-opacity-50 uppercase tracking-wider border-b-2 border-white border-opacity-5 mt-4">
                                        Page Content</div> <!---->
                                </li>
                                <li><!---->
                                    <div><a href="#/app/marketing/sections/about"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">About</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">3</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/marketing/sections/blog-lists"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Blog Lists</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">6</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/marketing/sections/blog-posts"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Blog Posts</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">2</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/marketing/sections/checkout"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Checkout</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">4</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/marketing/sections/comments"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Comments</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">3</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/marketing/sections/contact"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Contact</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">7</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/marketing/sections/cta"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">CTA</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">9</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/marketing/sections/faq"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">FAQ</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">4</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/marketing/sections/features"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Features</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">12</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/marketing/sections/image-overlays"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Image Overlays</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">10</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/marketing/sections/logos"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Logos</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">6</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/marketing/sections/newsletter"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Newsletter</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">7</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/marketing/sections/product-lists"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Product Lists</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">4</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/marketing/sections/product-details"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Product Details</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">2</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/marketing/sections/pricing"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Pricing</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">7</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/marketing/sections/statistics"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Statistics</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">6</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/marketing/sections/team"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Team</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">4</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/marketing/sections/testimonials"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Testimonials</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">5</span></a>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        <li><!----> <a href="#"
                                class="flex items-center px-3 py-2 hover:bg-gray-800 hover:bg-opacity-50 hover:text-white rounded-t transition ease-out duration-100 rounded-b"><svg
                                    class="hi-solid hi-document-text inline-block w-5 h-5 mr-2 text-gray-500 text-opacity-75"
                                    fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                        d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z"
                                        clip-rule="evenodd"></path>
                                </svg> <span class="grow text-sm font-medium">Pages</span> <!----> <svg
                                    class="hi-solid hi-chevron-down inline-block w-5 h-5 ml-2 transform transition ease-out duration-150 text-gray-500 rotate-90"
                                    fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd"></path>
                                </svg></a>
                            <ul class="p-2 text-gray-400 bg-gray-800 bg-opacity-50 text-sm rounded-b hidden">
                                <li><!---->
                                    <div><a href="#/app/marketing/pages/home"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Home</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">1</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/marketing/pages/features"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Features</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">1</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/marketing/pages/pricing"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Pricing</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">1</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/marketing/pages/contact"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Contact</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">1</span></a>
                                    </div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/marketing/pages/about"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">About</span> <!----> <span
                                                class="w-7 h-5 ml-2 flex items-center justify-center text-xs font-semibold rounded-full bg-gray-800 bg-opacity-75 text-white text-opacity-50">1</span></a>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <div
                                class="px-3 mt-5 mb-2 text-xs font-medium uppercase tracking-wider text-white text-opacity-40">
                                Extras</div> <!----> <!---->
                        </li>
                        <li><!----> <a href="#"
                                class="flex items-center px-3 py-2 hover:bg-gray-800 hover:bg-opacity-50 hover:text-white rounded-t transition ease-out duration-100 rounded-b"><svg
                                    class="hi-solid hi-cube inline-block w-5 h-5 mr-2 text-gray-500 text-opacity-75"
                                    fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M11 17a1 1 0 001.447.894l4-2A1 1 0 0017 15V9.236a1 1 0 00-1.447-.894l-4 2a1 1 0 00-.553.894V17zM15.211 6.276a1 1 0 000-1.788l-4.764-2.382a1 1 0 00-.894 0L4.789 4.488a1 1 0 000 1.788l4.764 2.382a1 1 0 00.894 0l4.764-2.382zM4.447 8.342A1 1 0 003 9.236V15a1 1 0 00.553.894l4 2A1 1 0 009 17v-5.764a1 1 0 00-.553-.894l-4-2z">
                                    </path>
                                </svg> <span class="grow text-sm font-medium">Icons</span> <!----> <svg
                                    class="hi-solid hi-chevron-down inline-block w-5 h-5 ml-2 transform transition ease-out duration-150 text-gray-500 rotate-90"
                                    fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd"></path>
                                </svg></a>
                            <ul class="p-2 text-gray-400 bg-gray-800 bg-opacity-50 text-sm rounded-b hidden">
                                <li><!---->
                                    <div><a href="#/app/icons/heroicons"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Heroicons</span> <!----> <!----></a></div>
                                </li>
                            </ul>
                        </li>
                        <li><!----> <a href="#"
                                class="flex items-center px-3 py-2 hover:bg-gray-800 hover:bg-opacity-50 hover:text-white rounded-t transition ease-out duration-100 rounded-b"><svg
                                    class="hi-solid hi-cog inline-block w-5 h-5 mr-2 text-gray-500 text-opacity-75"
                                    fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                        d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z"
                                        clip-rule="evenodd"></path>
                                </svg> <span class="grow text-sm font-medium">Tools</span> <!----> <svg
                                    class="hi-solid hi-chevron-down inline-block w-5 h-5 ml-2 transform transition ease-out duration-150 text-gray-500 rotate-90"
                                    fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd"></path>
                                </svg></a>
                            <ul class="p-2 text-gray-400 bg-gray-800 bg-opacity-50 text-sm rounded-b hidden">
                                <li><!---->
                                    <div><a href="#/app/tools/button-builder"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Button Builder</span> <!----> <!----></a></div>
                                </li>
                                <li><!---->
                                    <div><a href="#/app/tools/colors"
                                            class="flex items-center px-3 py-2 transition ease-out duration-100 hover:text-white"><!---->
                                            <span class="grow">Colors</span> <!----> <!----></a></div>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <div
                                class="px-3 mt-5 mb-2 text-xs font-medium uppercase tracking-wider text-white text-opacity-40">
                                More</div> <!----> <!---->
                        </li>
                        <li><!---->
                            <div><a href="#/app/changelog"
                                    class="flex items-center px-3 py-2 hover:bg-gray-800 hover:bg-opacity-50 hover:text-white rounded-t transition ease-out duration-100"><svg
                                        class="hi-solid hi-clipboard-list inline-block w-5 h-5 mr-2 text-gray-500 text-opacity-75"
                                        fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                                        <path fill-rule="evenodd"
                                            d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                                            clip-rule="evenodd"></path>
                                    </svg> <span class="grow text-sm font-medium">Changelog</span> <!----> <!----></a>
                            </div> <!---->
                        </li>
                        <li><!---->
                            <div><a href="#/app/documentation"
                                    class="flex items-center px-3 py-2 hover:bg-gray-800 hover:bg-opacity-50 hover:text-white rounded-t transition ease-out duration-100"><svg
                                        class="hi-solid hi-document-text inline-block w-5 h-5 mr-2 text-gray-500 text-opacity-75"
                                        fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd"
                                            d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z"
                                            clip-rule="evenodd"></path>
                                    </svg> <span class="grow text-sm font-medium">Documentation</span> <!---->
                                    <!----></a></div> <!---->
                        </li>
                        <li><!---->
                            <div><a href="#/app/license"
                                    class="flex items-center px-3 py-2 hover:bg-gray-800 hover:bg-opacity-50 hover:text-white rounded-t transition ease-out duration-100"><svg
                                        class="hi-solid hi-badge-check inline-block w-5 h-5 mr-2 text-gray-500 text-opacity-75"
                                        fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd"
                                            d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd"></path>
                                    </svg> <span class="grow text-sm font-medium">License</span> <!----> <!----></a>
                            </div> <!---->
                        </li>
                    </ul>


                    <nav class="space-y-1">
                        <a href="javascript:void(0)"
                            class="flex items-center space-x-3 px-3 font-medium rounded text-gray-200 bg-gray-800 bg-opacity-50">
                            <span class="flex-none flex items-center opacity-50">
                                <svg class="hi-outline hi-home inline-block w-5 h-5" stroke="currentColor"
                                    fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                            </span>
                            <span class="py-2 grow">Dashboard</span>
                            <span
                                class="px-2 py-1 rounded-full text-xs font-medium leading-4 bg-opacity-10 text-gray-100 bg-gray-400">3</span>
                        </a>
                        <div class="px-3 pt-5 pb-2 text-xs font-medium uppercase tracking-wider text-gray-400">Projects
                        </div>
                        <a href="javascript:void(0)"
                            class="flex items-center space-x-3 px-3 font-medium rounded text-gray-300 hover:text-gray-100 hover:bg-gray-800 hover:bg-opacity-50 active:bg-gray-800 active:bg-opacity-25">
                            <span class="flex-none flex items-center opacity-50">
                                <svg class="hi-outline hi-view-grid inline-block w-5 h-5" stroke="currentColor"
                                    fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                            </span>
                            <span class="py-2 grow">Manage</span>
                            <span
                                class="px-2 py-1 rounded-full text-xs font-medium leading-4 bg-opacity-10 text-gray-100 bg-gray-400">99+</span>
                        </a>
                        <a href="javascript:void(0)"
                            class="flex items-center space-x-3 px-3 font-medium rounded text-gray-300 hover:text-gray-100 hover:bg-gray-800 hover:bg-opacity-50 active:bg-gray-800 active:bg-opacity-25">
                            <span class="flex-none flex items-center opacity-50">
                                <svg class="hi-outline hi-clipboard-list inline-block w-5 h-5" stroke="currentColor"
                                    fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                            </span>
                            <span class="py-2 grow">Tasks</span>
                            <span
                                class="px-2 py-1 rounded-full text-xs font-medium leading-4 bg-opacity-10 text-gray-100 bg-gray-400">9</span>
                        </a>
                        <a href="javascript:void(0)"
                            class="flex items-center space-x-3 px-3 font-medium rounded text-gray-300 hover:text-gray-100 hover:bg-gray-800 hover:bg-opacity-50 active:bg-gray-800 active:bg-opacity-25">
                            <span class="flex-none flex items-center opacity-50">
                                <svg class="hi-outline hi-users inline-block w-5 h-5" stroke="currentColor"
                                    fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </span>
                            <span class="py-2 grow">Clients</span>
                            <span
                                class="px-2 py-1 rounded-full text-xs font-medium leading-4 bg-opacity-10 text-gray-100 bg-gray-400">26</span>
                        </a>
                        <a href="javascript:void(0)"
                            class="flex items-center space-x-3 px-3 font-medium rounded text-gray-300 hover:text-gray-100 hover:bg-gray-800 hover:bg-opacity-50 active:bg-gray-800 active:bg-opacity-25">
                            <span class="flex-none flex items-center opacity-50">
                                <svg class="hi-outline hi-plus-circle inline-block w-5 h-5 text-emerald-300"
                                    stroke="currentColor" fill="none" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                            <span class="py-2 grow">Add New</span>
                        </a>
                        <div class="px-3 pt-5 pb-2 text-xs font-medium uppercase tracking-wider text-gray-400">Account
                        </div>
                        <a href="javascript:void(0)"
                            class="flex items-center space-x-3 px-3 font-medium rounded text-gray-300 hover:text-gray-100 hover:bg-gray-800 hover:bg-opacity-50 active:bg-gray-800 active:bg-opacity-25">
                            <span class="flex-none flex items-center opacity-50">
                                <svg class="hi-outline hi-user inline-block w-5 h-5" stroke="currentColor"
                                    fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </span>
                            <span class="py-2 grow">Profile</span>
                        </a>
                        <a href="javascript:void(0)"
                            class="flex items-center space-x-3 px-3 font-medium rounded text-gray-300 hover:text-gray-100 hover:bg-gray-800 hover:bg-opacity-50 active:bg-gray-800 active:bg-opacity-25">
                            <span class="flex-none flex items-center opacity-50">
                                <svg class="hi-outline hi-cog inline-block w-5 h-5" stroke="currentColor"
                                    fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </span>
                            <span class="py-2 grow">Settings</span>
                        </a>
                        <a href="javascript:void(0)"
                            class="flex items-center space-x-3 px-3 font-medium rounded text-gray-300 hover:text-gray-100 hover:bg-gray-800 hover:bg-opacity-50 active:bg-gray-800 active:bg-opacity-25">
                            <span class="flex-none flex items-center opacity-50">
                                <svg class="hi-outline hi-lock-open inline-block w-5 h-5 text-red-300"
                                    stroke="currentColor" fill="none" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                </svg>
                            </span>
                            <span class="py-2 grow">Log out</span>
                        </a>
                    </nav>
                </div>
            </div>
            <!-- END Sidebar Navigation -->
        </nav>
