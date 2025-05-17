<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite('resources/css/app.css') <!-- Pastikan Tailwind terpasang -->

</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-white font-sans antialiased">
    <div class="min-h-screen flex flex-col justify-center items-center px-4">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-6">Welcome to SMART</h1>
            <p class="text-lg md:text-xl text-gray-600 dark:text-gray-300 mb-8">Please log in to continue</p>

            @if (Route::has('login'))
                <a href="{{ route('login') }}"
                   class="inline-block px-6 py-3 bg-red-500 text-white text-lg font-semibold rounded-lg hover:bg-red-600 transition focus:outline-none focus:ring-4 focus:ring-red-300">
                    Login
                </a>
            @endif
        </div>
    </div>
</body>
</html>
