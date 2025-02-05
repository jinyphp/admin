<!DOCTYPE html>
<html lang="ko">

    <head>
        <meta charset="utf-8" />
        <title>Dashboard | Hyper - Responsive Bootstrap 5 Admin Dashboard</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
        <meta content="Coderthemes" name="author" />

        <link rel="stylesheet" href="https://fonts.googleapis.com/earlyaccess/notosanskr.css">

        <!-- App favicon -->
        <link rel="shortcut icon" href="/assets/images/favicon.ico">

        <!-- Theme Config Js -->
        <script src="/assets/js/hyper-config.js"></script>

        <!-- App css -->
        <link href="/assets/css/app-saas.min.css" rel="stylesheet" type="text/css" id="app-style" />

        <!-- Icons css -->
        <link href="/assets/css/icons.min.css" rel="stylesheet" type="text/css" />


        {{--
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        --}}

        <script src="//unpkg.com/alpinejs" defer></script>

        <style>
            body {
                font-family: 'Noto Sans CJK', sans-serif;
            }
        </style>


        @livewireStyles
        @stack('css')
    </head>

    <body>
        <!-- Begin page -->
        <div class="wrapper">

            <!-- ========== Topbar Start ========== -->
            @include("jiny-admin::layouts.hyper.header")
            <!-- ========== Topbar End ========== -->

            <!-- ========== Left Sidebar Start ========== -->
            @include("jiny-admin::layouts.hyper.sidebar-left")
            <!-- ========== Left Sidebar End ========== -->

            <!-- ============================================================== -->
            <!-- Start Page Content here -->
            <!-- ============================================================== -->

            <div class="content-page">
                <div class="content">

                    <!-- Start Content-->
                    @include("jiny-admin::layouts.hyper.contents")
                    <!-- container -->

                </div>
                <!-- content -->

                <!-- Footer Start -->
                @include("jiny-admin::layouts.hyper.footer")
                <!-- end Footer -->

            </div>

            <!-- ============================================================== -->
            <!-- End Page content -->
            <!-- ============================================================== -->

        </div>
        <!-- END wrapper -->

        <!-- Theme Settings -->
        @include("jiny-admin::layouts.hyper.setting")

        <!-- Vendor js -->
        <script src="/assets/js/vendor.min.js"></script>

        <!-- App js -->
        <script src="/assets/js/app.min.js"></script>

        @livewireScripts
        @stack('scripts')

    </body>
</html>
