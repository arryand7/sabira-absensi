<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased" style="background-color:#dcecf8">
    <div class="min-h-screen flex flex-col justify-center sm:flex-row items-center">

        <!-- Left Image (Only on desktop) -->
        <div class="hidden sm:flex sm:w-1/2 h-screen items-center justify-center">
            <img src="{{ asset('images/gambar.png') }}" alt="Login Image" class="w-3/4 max-w-xs object-contain">
        </div>

        <!-- Right Form -->
        <div class="w-full sm:w-1/2 flex justify-center items-center p-6">
            <div class="w-full max-w-md shadow-md rounded-lg p-6">
                <!-- Logo di atas form -->
                <div class="flex justify-center mb-4">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-16 object-contain">
                </div>

                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>
