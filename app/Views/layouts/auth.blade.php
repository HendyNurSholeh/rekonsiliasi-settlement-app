<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $title }} | Sistem Rekonsiliasi dan Pelimpahan Dana (SIRELA)</title>

    <meta name="description" content="Login">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, user-scalable=no, minimal-ui">
    <!-- Call App Mode on ios devices -->
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <!-- Remove Tap Highlight on Windows Phone IE -->
    <meta name="msapplication-tap-highlight" content="no">
    <!-- base css -->
    <link id="vendorsbundle" rel="stylesheet" media="screen, print" href="{{ base_url('/css/vendors.bundle.css') }}">
    <link id="appbundle" rel="stylesheet" media="screen, print" href="{{ base_url('/css/app.bundle.css') }}">
    <link id="mytheme" rel="stylesheet" media="screen, print" href="{{ base_url('/css/themes/cust-theme-4.css') }}">
    <link id="myskin" rel="stylesheet" media="screen, print" href="{{ base_url('/css/skins/skin-master.css') }}">
    <!-- Place favicon.ico in the root directory -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ base_url('/img/favicon/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ base_url('/img/favicon/favicon-32x32.png') }}">
    <link rel="mask-icon" href="{{ base_url('/img/favicon/safari-pinned-tab.svg') }}" color="#5bbad5">

    <link rel="stylesheet" media="screen, print" href="{{ base_url('/css/notifications/toastr/toastr.css') }}">

    <link rel="stylesheet" media="screen, print" href="{{ base_url('/css/page-login-alt.css') }}">
</head>

<body class="mod-main-boxed mod-bg-1">
    @yield('content')

    <p id="js-color-profile" class="d-none">
        <span class="color-primary-50"></span>
        <span class="color-primary-100"></span>
        <span class="color-primary-200"></span>
        <span class="color-primary-300"></span>
        <span class="color-primary-400"></span>
        <span class="color-primary-500"></span>
        <span class="color-primary-600"></span>
        <span class="color-primary-700"></span>
        <span class="color-primary-800"></span>
        <span class="color-primary-900"></span>
        <span class="color-info-50"></span>
        <span class="color-info-100"></span>
        <span class="color-info-200"></span>
        <span class="color-info-300"></span>
        <span class="color-info-400"></span>
        <span class="color-info-500"></span>
        <span class="color-info-600"></span>
        <span class="color-info-700"></span>
        <span class="color-info-800"></span>
        <span class="color-info-900"></span>
        <span class="color-danger-50"></span>
        <span class="color-danger-100"></span>
        <span class="color-danger-200"></span>
        <span class="color-danger-300"></span>
        <span class="color-danger-400"></span>
        <span class="color-danger-500"></span>
        <span class="color-danger-600"></span>
        <span class="color-danger-700"></span>
        <span class="color-danger-800"></span>
        <span class="color-danger-900"></span>
        <span class="color-warning-50"></span>
        <span class="color-warning-100"></span>
        <span class="color-warning-200"></span>
        <span class="color-warning-300"></span>
        <span class="color-warning-400"></span>
        <span class="color-warning-500"></span>
        <span class="color-warning-600"></span>
        <span class="color-warning-700"></span>
        <span class="color-warning-800"></span>
        <span class="color-warning-900"></span>
        <span class="color-success-50"></span>
        <span class="color-success-100"></span>
        <span class="color-success-200"></span>
        <span class="color-success-300"></span>
        <span class="color-success-400"></span>
        <span class="color-success-500"></span>
        <span class="color-success-600"></span>
        <span class="color-success-700"></span>
        <span class="color-success-800"></span>
        <span class="color-success-900"></span>
        <span class="color-fusion-50"></span>
        <span class="color-fusion-100"></span>
        <span class="color-fusion-200"></span>
        <span class="color-fusion-300"></span>
        <span class="color-fusion-400"></span>
        <span class="color-fusion-500"></span>
        <span class="color-fusion-600"></span>
        <span class="color-fusion-700"></span>
        <span class="color-fusion-800"></span>
        <span class="color-fusion-900"></span>
    </p>

    <script src="{{ base_url('/js/vendors.bundle.js') }}"></script>
    <script src="{{ base_url('/js/app.bundle.js') }}"></script>
    <script src="{{ base_url('/js/notifications/toastr/toastr.js') }}"></script>

    <script>
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": true,
            "onclick": null,
            "showDuration": 300,
            "hideDuration": 100,
            "timeOut": 5000,
            "extendedTimeOut": 1000,
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        }

        @if (session()->has('error'))
            toastr["error"](`{!! session('error') !!}`)
            @php
                session()->remove('error');
            @endphp
        @endif

        @if (session()->has('warning'))
            toastr["warning"](`{!! session('warning') !!}`)
            @php
                session()->remove('warning');
            @endphp
        @endif

        @if (session()->has('success'))
            toastr["success"](`{!! session('success') !!}`)
            @php
                session()->remove('success');
            @endphp
        @endif
    </script>

    @stack('scripts')
</body>

</html>
