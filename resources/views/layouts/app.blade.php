@php
    $user = auth()->user();
    $role = $user->role ?? null;
    $hasSidebar = isset($sidebar);
    $bodyClasses = 'sidebar-mini layout-fixed layout-footer-fixed layout-navbar-fixed';
    if (!$hasSidebar) {
        $bodyClasses .= ' sidebar-collapse';
    }
    $appSetting = \App\AppSettingManager::current();
    $appName = $appSetting->app_name ?? config('app.name', 'Sabira Absensi');
    $appLogo = $appSetting->app_logo
        ? asset('storage/' . $appSetting->app_logo)
        : asset('images/logo.png');
    $appFavicon = $appSetting->app_favicon
        ? asset('storage/' . $appSetting->app_favicon)
        : $appLogo;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $appName }}</title>
    <link rel="icon" href="{{ $appFavicon }}" type="image/png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@1.13.1/css/OverlayScrollbars.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')

    <style>
        .nav-sidebar .nav-link {
            border-radius: 0.6rem;
            margin-bottom: 0.35rem;
            transition: all 0.2s ease;
        }
        .nav-sidebar .nav-link.active {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            box-shadow: 0 6px 14px rgba(37, 99, 235, 0.25);
        }
        .nav-sidebar .nav-link:hover {
            background-color: rgba(59, 130, 246, 0.18);
        }
        .lookup-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 50;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            margin-top: 0.25rem;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.12);
            max-height: 14rem;
            overflow-y: auto;
        }
        .lookup-suggestions button {
            width: 100%;
            padding: 0.5rem 0.75rem;
            text-align: left;
            background: transparent;
            border: none;
            font-size: 0.85rem;
            transition: background 0.15s ease;
        }
        .lookup-suggestions button:hover {
            background-color: rgba(59, 130, 246, 0.08);
        }
        .lookup-suggestions button.active,
        .lookup-suggestions button:focus {
            background-color: rgba(59, 130, 246, 0.12);
            outline: none;
        }
        .lookup-suggestions .meta {
            font-size: 0.75rem;
            color: #64748b;
        }
    </style>
</head>
<body class="{{ $bodyClasses }}">
<div class="wrapper">
    @include('layouts.navigation')

    @if($hasSidebar)
        {{ $sidebar }}
    @endif

    <div class="content-wrapper">
        @isset($header)
            <div class="content-header">
                <div class="container-fluid">
                    {{ $header }}
                </div>
            </div>
        @endisset

        <section class="content pt-3">
            <div class="container-fluid">
                @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('status') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Terjadi kesalahan:</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                {{ $slot }}
            </div>
        </section>
    </div>

    <footer class="main-footer text-sm">
        <strong>Copyright {{ now()->year }} {{ $appName }}.</strong>
        <span class="ml-1">Created by Ryand Arifriantoni in collaboration with TelkomUniversity.</span>
        <div class="float-right d-none d-sm-inline-block">
            <b>Laravel</b> {{ app()->version() }}
        </div>
    </footer>

    <x-control-sidebar />
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@1.13.1/js/jquery.overlayScrollbars.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>

@stack('scripts')
@livewireScripts

<script>
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Yakin ingin menghapus?',
                text: 'Data yang dihapus tidak bisa dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>

@if((session('success') || session('error'))
    && !request()->routeIs('admin.schedules.index')
    && !request()->routeIs('promotion.*'))
    <script>
        Swal.fire({
            icon: '{{ session('success') ? 'success' : 'error' }}',
            title: '{{ session('success') ? 'Berhasil' : 'Gagal' }}',
            text: '{{ session('success') ?? session('error') }}',
            timer: 2500,
            timerProgressBar: true,
            showConfirmButton: false,
        });
    </script>
@endif
</body>
</html>
