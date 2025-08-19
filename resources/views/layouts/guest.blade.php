<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#D6D8D2]">

    <!-- Background blur overlay -->
    <div class="min-h-screen flex items-center justify-center relative">
        <div class="absolute inset-0 bg-[#D6D8D2] backdrop-blur-md"></div>

        <!-- Login Form -->
        <div class="relative z-10 w-full max-w-md bg-white border border-gray-300 shadow-2xl rounded-2xl p-6 mx-4">
            <!-- Logo -->
            <div class="flex justify-center mb-4">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-16 object-contain">
            </div>
            {{ $slot }}
            <footer class="border-t border-gray-600 text-center text-xs text-gray-600 mt-2 py-2">
                © {{ date('Y') }} TelkomUniversitySurabaya.
            </footer>
        </div>

    </div>

</body>
</html>
