<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- Meta & Resource -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">

    <!-- Fonts & Icons -->
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />

    <!-- SweetAlert & DataTables -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />

    <style>
        /* Batasi semua elemen dalam Leaflet map agar tidak menindih navbar atau elemen lain */
        .leaflet-container,
        .leaflet-pane,
        .leaflet-tile,
        .leaflet-marker-icon,
        .leaflet-popup,
        .leaflet-shadow-pane,
        .leaflet-overlay-pane,
        .leaflet-marker-pane,
        .leaflet-popup-pane,
        .leaflet-control {
            z-index: 0 !important;
        }

        /* Pastikan navbar atau elemen lain tetap bisa berada di atas */
        nav,
        .fixed,
        .sticky,
        .z-40,
        .z-50 {
            z-index: 50 !important;
            position: relative;
        }
    </style>

    <!-- Vite & Livewire -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <!-- Navbar Atas (Selalu di atas) -->
    <div class="fixed top-0 left-0 right-0 z-40">
        @include('layouts.user-navigation') {{-- navbar atas --}}
    </div>

    <!-- Layout utama -->
    <div class="min-h-screen flex pt-16">
        {{-- Main content (navbar + page) --}}
        <div class="flex-1 flex flex-col bg-[#D6D8D2]"> <!-- Ubah bg konten utama di sini -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <main class="flex-1 px-4 py-6">
                {{ $slot }}
            </main>
        </div>
    </div>


    <!-- Scripts -->
    @stack('scripts')
    @livewireScripts

    <script>
        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Yakin ingin menghapus?',
                    text: "Data yang dihapus tidak bisa dikembalikan!",
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

    {{-- Notifikasi SweetAlert --}}
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
