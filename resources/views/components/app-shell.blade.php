@props([
    'headerTitle' => 'Sabira Absensi',
    'headerSubtitle' => 'Monitoring Kehadiran dan Pembelajaran',
    'guest' => false,
    'actions' => null,
])

@php
    $appSetting = \App\AppSettingManager::current();
    $appName = $appSetting->app_name ?: 'SABIRA ABSENSI';
    if (!$guest && $headerTitle === 'Sabira Absensi') {
        $activeNavigation = collect(config('navigation', []))
            ->flatMap(fn ($group) => $group['items'] ?? [])
            ->first(fn ($item) => collect($item['active'] ?? [$item['route']])->contains(fn ($pattern) => request()->routeIs($pattern)));
        $headerTitle = $activeNavigation['label'] ?? $headerTitle;
    }
    $appFavicon = $appSetting->app_favicon
        ? asset('storage/'.$appSetting->app_favicon)
        : asset('images/logo.png');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light" data-theme-mode="system" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light dark">

    <title>{{ $headerTitle }} — {{ $appName }}</title>
    <link rel="icon" href="{{ $appFavicon }}">

    <script>
        (() => {
            const storageKey = 'sabira-theme';
            const media = window.matchMedia('(prefers-color-scheme: dark)');
            const validModes = ['system', 'light', 'dark'];
            const root = document.documentElement;
            const storedMode = localStorage.getItem(storageKey);
            const initialMode = validModes.includes(storedMode) ? storedMode : 'system';
            const resolvedTheme = mode => mode === 'system' ? (media.matches ? 'dark' : 'light') : mode;
            const apply = mode => {
                const safeMode = validModes.includes(mode) ? mode : 'system';
                root.dataset.themeMode = safeMode;
                root.dataset.theme = resolvedTheme(safeMode);
                root.classList.toggle('dark', root.dataset.theme === 'dark');
                window.dispatchEvent(new CustomEvent('sabira-theme-changed', { detail: { mode: safeMode, theme: root.dataset.theme } }));
            };

            window.SabiraTheme = {
                apply,
                set(mode) {
                    localStorage.setItem(storageKey, mode);
                    apply(mode);
                },
                getMode: () => root.dataset.themeMode,
            };

            apply(initialMode);
            media.addEventListener('change', () => {
                if (root.dataset.themeMode === 'system') apply('system');
            });
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="sabira-body" x-data="{ mobileNavigationOpen: false }" @keydown.escape.window="mobileNavigationOpen = false">
    @if($guest)
        <div class="sabira-guest-shell">
            <header class="sabira-guest-header">
                <a href="{{ url('/') }}" class="sabira-brand-link">
                    <span class="sabira-brand-mark"><i class="fas fa-graduation-cap"></i></span>
                    <span><strong>SABIRA ABSENSI</strong><small>Monitoring Kehadiran dan Pembelajaran</small></span>
                </a>
                <x-theme-switcher />
            </header>
            <main class="sabira-guest-content">{{ $slot }}</main>
            <x-footer />
        </div>
    @else
        <div class="sabira-app-shell">
            <x-sidebar />
            <x-mobile-drawer />
            <div class="sabira-main-column">
                <x-topbar :title="$headerTitle" :subtitle="$headerSubtitle" :actions="$actions ?? null" />
                <main id="main-content" class="sabira-main-content" tabindex="-1">
                    @if(session('success'))<x-alert type="success" :message="session('success')" />@endif
                    @if(session('warning'))<x-alert type="warning" :message="session('warning')" />@endif
                    @if(session('error'))<x-alert type="danger" :message="session('error')" />@endif
                    {{ $slot }}
                </main>
                <x-footer />
            </div>
        </div>
    @endif

    @livewireScripts
</body>
</html>
