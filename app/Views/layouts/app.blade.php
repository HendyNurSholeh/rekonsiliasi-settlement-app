<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $title }} | Devisa Manager Tools</title>

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
    {{-- <link id="mytheme" rel="stylesheet" media="screen, print" href="{{ base_url('/css/themes/cust-theme-14.css') }}"> --}}
    <link id="myskin" rel="stylesheet" media="screen, print" href="{{ base_url('/css/skins/skin-master.css') }}">
    <!-- Place favicon.ico in the root directory -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ base_url('/img/favicon/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32  " href="{{ base_url('/img/favicon/favicon-32x32.png') }}">
    <link rel="mask-icon" href="{{ base_url('/img/favicon/safari-pinned-tab.svg') }}" color="#5bbad5">

    <link rel="stylesheet" media="screen, print" href="{{ base_url('/css/fa-solid.css') }}">

    <link rel="stylesheet" media="screen, print" href="{{ base_url('/css/notifications/toastr/toastr.css') }}">
    <link rel="stylesheet" media="screen, print"
        href="{{ base_url('/css/notifications/sweetalert2/sweetalert2.bundle.css') }}">

    <link rel="stylesheet" media="screen, print"
        href="{{ base_url('/css/datagrid/datatables/datatables.bundle.css') }}">
    <link rel="stylesheet" media="screen, print" href="{{ base_url('/css/formplugins/select2/select2.bundle.css') }}">
    
    <!-- Custom font size and styles -->
    <link rel="stylesheet" media="screen, print" href="{{ base_url('/css/custom-font-size.css') }}">

    @stack('styles')

    @php
        $permissions = session()->permissions;
    @endphp
</head>

<body class="mod-bg-1 mod-nav-link nav-function-top mod-clean-page-bg mod-skin-light" data-action="toggle" data-class="nav-function-top mod-clean-page-bg">
    <div class="page-wrapper">
        <div class="page-inner">
            <!-- BEGIN Left Aside -->
            <aside class="page-sidebar">
                <div class="page-logo">
                    <a href="{{ site_url('dashboard') }}"
                        class="page-logo-link press-scale-down d-flex align-items-center position-relative"
                        data-toggle="modal" data-target="#modal-shortcut">
                        <img src="{{ base_url('/img/logo.png') }}" alt="SmartAdmin WebApp" aria-roledescription="logo">
                        <span class="page-logo-text mr-1">BANK KALSEL</span>
                        <span class="position-absolute text-white opacity-50 small pos-top pos-right mr-2 mt-n2"></span>
                    </a>
                </div>
                <!-- BEGIN PRIMARY NAVIGATION -->
                <nav id="js-primary-nav" class="primary-nav text-center" role="navigation" style="height: 100vh;">
                    <ul id="js-nav-menu" class="nav-menu">
                        <li class="@if ($route == 'dashboard') active open @endif">
                            <a href="{{ site_url('dashboard') }}">
                                <i class="fal fa-home"></i>
                                <span class="nav-link-text">Dashboard</span>
                            </a>
                        </li>

                         @php
                            $user = in_array('view user', $permissions);
                            $unit_kerja = in_array('view unit kerja', $permissions);
                            $permission = in_array('view permission', $permissions);
                            $role = in_array('view role', $permissions);
                            $activity = in_array('view activity', $permissions);
                            $error = in_array('view error', $permissions);
                            
                            // Rekonsiliasi permissions
                            $rekon_process = in_array('view rekon process', $permissions ?? []) ?? true;
                            $upload_data = in_array('view upload data', $permissions ?? []) ?? true;
                        @endphp

                        {{-- Rekonsiliasi Settlement Menu --}}
                        @if ($rekon_process || $upload_data)
                        
                            @if ($rekon_process)
                                <li class="@if ($route == 'rekon') active open @endif">
                                    <a href="javascript:void(0);" title="User Management" data-filter-tags="user management">
                                        <i class="fal fa-calendar-alt"></i>
                                        <span class="nav-link-text">Persiapan</span>
                                    </a>
                                    <ul>
                                        <li class="@if ($route == 'rekon') active @endif">
                                            <a href="{{ site_url('rekon') }}">
                                                <span class="nav-link-text text-left">Pilih Tanggal</span>
                                            </a>
                                        </li>
                                        <li class="@if ($route == 'rekon/step2') active @endif">
                                            <a href="{{ site_url('/rekon/step2') }}">
                                                <span class="nav-link-text text-left">Review Data</span>
                                            </a> 
                                        </li>
                                    </ul>
                                </li>
                            @endif
                        @endif
                            
                            {{-- TAHAP 3 - PROSES REKONSILIASI --}}
                            <li class="@if (str_contains($route, 'rekon/process')) active open @endif">
                                <a href="javascript:void(0);" title="Proses Rekonsiliasi" data-filter-tags="tahap 3 proses rekonsiliasi">
                                    <i class="fal fa-tasks"></i>
                                    <span class="nav-link-text">Proses Rekonsiliasi</span>
                                </a>
                                <ul>
                                    <!-- 1. Laporan Detail vs Rekap -->
                                    <li class="@if ($route == 'rekon/process/detail-vs-rekap') active @endif">
                                        <a href="{{ site_url('rekon/process/detail-vs-rekap') }}">
                                            <span class="nav-link-text text-left">Laporan Detail vs Rekap</span>
                                        </a>
                                    </li>
                                    
                                    <!-- 2. Rekon Direct Jurnal -->
                                    <li class="@if (str_contains($route, 'rekon/process/direct-jurnal')) active open @endif">
                                        <a href="javascript:void(0);" title="Rekon Direct Jurnal">
                                            <span class="nav-link-text text-left">Rekon Direct Jurnal</span>
                                        </a>
                                        <ul>
                                            <li class="@if ($route == 'rekon/process/direct-jurnal-rekap') active @endif">
                                                <a href="{{ site_url('rekon/process/direct-jurnal-rekap') }}">
                                                    <span class="nav-link-text text-left">Rekap Tx Direct Jurnal</span>
                                                </a>
                                            </li>
                                            <li class="@if ($route == 'rekon/process/penyelesaian-dispute') active @endif">
                                                <a href="{{ site_url('rekon/process/penyelesaian-dispute') }}">
                                                    <span class="nav-link-text text-left">Penyelesaian Dispute</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                    
                                    <!-- 3. Rekon Indirect Jurnal -->
                                    <li class="@if (str_contains($route, 'rekon/process/indirect-jurnal')) active open @endif">
                                        <a href="javascript:void(0);" title="Rekon Indirect Jurnal">
                                            <span class="nav-link-text text-left">Rekon Indirect Jurnal</span>
                                        </a>
                                        <ul>
                                            <li class="@if ($route == 'rekon/process/indirect-jurnal-rekap') active @endif">
                                                <a href="{{ site_url('rekon/process/indirect-jurnal-rekap') }}">
                                                    <span class="nav-link-text text-left">Rekap Tx Indirect Jurnal</span>
                                                </a>
                                            </li>
                                            <li class="@if ($route == 'rekon/process/indirect-dispute') active @endif">
                                                <a href="{{ site_url('rekon/process/indirect-dispute') }}">
                                                    <span class="nav-link-text text-left">Penyelesaian Dispute</span>
                                                </a>
                                            </li>
                                            <li class="@if ($route == 'rekon/process/konfirmasi-saldo-ca') active @endif">
                                                <a href="{{ site_url('rekon/process/konfirmasi-saldo-ca') }}">
                                                    <span class="nav-link-text text-left">Konfirmasi Saldo CA</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                       
                        @if ($user || $unit_kerja || $permission || $role)
                            <li class="@if ($route == 'user' || $route == 'unit-kerja' || $route == 'permission' || $route == 'role') active open @endif">
                                <a href="javascript:void(0);" title="User Management" data-filter-tags="user management">
                                    <i class="fal fa-users-cog"></i>
                                    <span class="nav-link-text">User Management</span>
                                </a>
                                <ul>
                                    @if ($user)
                                        <li class="@if ($route == 'user') active open @endif">
                                            <a href="{{ site_url('user') }}">
                                                <span class="nav-link-text text-left">User</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if ($unit_kerja)
                                        <li class="@if ($route == 'unit-kerja') active open @endif">
                                            <a href="{{ site_url('unit-kerja') }}">
                                                <span class="nav-link-text text-left">Unit Kerja</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if ($permission)
                                        <li class="@if ($route == 'permission') active open @endif">
                                            <a href="{{ site_url('permission') }}">
                                                <span class="nav-link-text text-left">Permission</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if ($role)
                                        <li class="@if ($route == 'role') active open @endif">
                                            <a href="{{ site_url('role') }}">                         
                                                <span class="nav-link-text text-left">Role</span>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        @if ($activity || $error)
                            <li class="@if ($route == 'log/activity' || $route == 'log/error') active open @endif">
                                <a href="javascript:void(0);" title="Log" data-filter-tags="log">
                                    <i class="fal fa-shield-alt"></i>
                                    <span class="nav-link-text text-left">Log</span>
                                </a>
                                <ul>
                                    @if ($activity)
                                        <li class="@if ($route == 'log/activity') active open @endif">
                                            <a href="{{ site_url('log/activity') }}">
                                               
                                                <span class="nav-link-text text-left">Activity</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if ($error)
                                        <li class="@if ($route == 'log/error') active open @endif">
                                            <a href="{{ site_url('log/error') }}">
                                              
                                                <span class="nav-link-text text-left">Error</span>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        <li class="@if ($route == 'profile') active open @endif">
                            <a href="{{ site_url('profile') }}">
                                <i class="fal fa-user-circle"></i>
                                <span class="nav-link-text">Profil Saya</span>
                            </a>
                        </li>
                    </ul>
                </nav>
                <!-- END PRIMARY NAVIGATION -->
            </aside>
            <!-- END Left Aside -->
            <div class="page-content-wrapper">
                <!-- BEGIN Page Header -->
                <header class="page-header" role="banner">
                    <div class="hidden-lg-up">
                        <a href="#" class="header-btn btn press-scale-down" data-action="toggle"
                            data-class="mobile-nav-on">
                            <i class="ni ni-menu"></i>
                        </a>
                    </div>
                    <a href="{{ site_url('dashboard') }}"
                        class="press-scale-down d-flex align-items-center position-relative">
                        <img src="{{ base_url('/img/backgrounds/bankkalsel-logo-lg.png') }}" class="d-inline-block align-top mr-2"
                            alt="logo" width="115px">
                        {{-- <span class="mr-1 color-fusion-200"><span class="fw-500 color-primary-500">Devisa Manager Tools</span> Bank
                            Kalsel</span> --}}
                    </a>
                    <div class="ml-auto d-flex">
                        <div>
                            <a href="#" data-toggle="dropdown"
                                class="header-icon d-flex align-items-center justify-content-center ml-2">
                                <img src="{{ base_url('/img/demo/avatars/avatar-m.png') }}"
                                    class="profile-image rounded-circle">
                                <span class="mx-3 hidden-xs-down">
                                    {{ session()->username }}
                                </span>
                                <i class="ni ni-chevron-down hidden-xs-down"></i>
                            </a>

                            <div class="dropdown-menu dropdown-menu-animated dropdown-lg">
                                <div class="dropdown-header bg-trans-gradient d-flex flex-row py-4 rounded-top">
                                    <div class="d-flex flex-row align-items-center mt-1 mb-1 color-white">
                                        <span class="mr-2">
                                            <img src="{{ base_url('/img/demo/avatars/avatar-m.png') }}"
                                                class="rounded-circle profile-image">
                                        </span>
                                        <div class="info-card-text">
                                            <div class="fs-lg text-truncate text-truncate-lg">
                                                {{ session()->name ?? '-' }}
                                            </div>
                                            <span class="text-truncate text-truncate-md opacity-80">
                                                {{ session()->role }} <br>
												{{ session()->unit_kerja }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="dropdown-divider m-0"></div>
                                <a href="{{ site_url('profile') }}" class="dropdown-item">
                                    <i class="fal fa-user-circle mx-1"></i>
                                    <span>Profile</span>
                                </a>
                                <div class="dropdown-divider m-0"></div>
                                <a href="{{ site_url('logout') }}" class="dropdown-item color-danger-500">
                                    <i class="fal fa-sign-out mx-1"></i>
                                    <span>Logout</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </header>

                <main id="js-page-content" role="main" class="page-content">
                    <input type="hidden" id="txt_csrfname" name="{{ csrf_token() }}"
                        value="{{ csrf_hash() }}" />
                    @yield('content')
                </main>

                <div class="page-content-overlay" data-action="toggle" data-class="mobile-nav-on"></div>
                <footer class="page-footer" role="contentinfo">
                    <div class="d-flex align-items-center flex-1 text-muted">
                        <span class="fw-700">2025 © Devisa Manager Tools by&nbsp;<a href='https://www.bankkalsel.co.id/'
                                class='text-primary fw-500' title='bankkalsel.co.id'
                                target='_blank'>bankkalsel.co.id</a></span>
                    </div>
                </footer>
            </div>
        </div>
    </div>

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
    <script src="{{ base_url('/js/notifications/sweetalert2/sweetalert2.bundle.js') }}"></script>
    <script src="{{ base_url('/js/datagrid/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ base_url('/js/formplugins/select2/select2.bundle.js') }}"></script>
    
    <script>
        const swalr = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-danger mx-1",
                cancelButton: "btn btn-secondary mx-1"
            },
            buttonsStyling: false,
            confirmButtonText: "Hapus",
            cancelButtonText: "Batal",
            title: "Apakah anda yakin?",
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: "warning",
            showCancelButton: true,
        });

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
        layouts.horizontalNavigation('on') or ('off')
        layouts.cleanBackground('on') or ('off')
        setlayout.mode('light')
    </script>

    @stack('scripts')
</body>

</html>
