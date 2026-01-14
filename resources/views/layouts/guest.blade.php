@php
    $appSetting = \App\AppSettingManager::current();
    $appName = $appSetting->app_name ?? config('app.name', 'Sabira Absensi');
    $appLogo = $appSetting->app_logo
        ? asset('storage/' . $appSetting->app_logo)
        : asset('images/logo.png');
    $appFavicon = $appSetting->app_favicon
        ? asset('storage/' . $appSetting->app_favicon)
        : $appLogo;
    $appDescription = $appSetting->app_description ?: 'Silakan masuk untuk melanjutkan';
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header text-center">
            <img src="{{ $appLogo }}" alt="Logo" class="mb-3" style="height: 52px;">
            <div class="h5 mb-0 font-weight-semibold">{{ $appName }}</div>
            <p class="text-sm text-muted mb-0">{{ $appDescription }}</p>
        </div>
        <div class="card-body">
            {{ $slot }}
        </div>
        <div class="card-footer text-center text-xs text-muted">
            Copyright {{ now()->year }} {{ $appName }}. Created by Ryand Arifriantoni in collaboration with TelkomUniversitySurabaya.
        </div>
    </div>
</div>
</body>
</html>
