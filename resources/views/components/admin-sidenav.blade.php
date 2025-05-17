<div x-data="{ sidebarOpen: false }" class="flex h-screen bg-gray-100 dark:bg-gray-900">

    <!-- Sidebar -->
    <div :class="sidebarOpen ? 'block' : 'hidden'" class="fixed inset-0 z-20 transition-opacity bg-black opacity-50 md:hidden" @click="sidebarOpen = false"></div>

    <aside :class="sidebarOpen ? 'translate-x-0 ease-out' : '-translate-x-full ease-in'"
           class="fixed z-30 inset-y-0 left-0 w-64 transition duration-300 transform bg-white dark:bg-gray-800 overflow-y-auto md:translate-x-0 md:static md:inset-0 shadow">
        <div class="flex items-center justify-between px-4 py-2">
            <button class="md:hidden text-gray-800 dark:text-white focus:outline-none" @click="sidebarOpen = false">
                ✕
            </button>
        </div>

        <nav class="px-4 py-6 space-y-2">
            <a href="{{ route('dashboard') }}"
               class="block px-4 py-2 rounded-md flex items-center {{ request()->routeIs('dashboard') ? 'bg-blue-100 dark:bg-blue-600 text-blue-700 dark:text-white font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                <i class="bi bi-house-door-fill text-lg mr-2"></i> Dashboard
            </a>

            <a href="{{ route('laporan.karyawan') }}"
               class="block px-4 py-2 rounded-md flex items-center {{ request()->routeIs('laporan.karyawan') ? 'bg-blue-100 dark:bg-blue-600 text-blue-700 dark:text-white font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                <i class="bi bi-bar-chart-fill text-lg mr-2"></i> Laporan Absensi
            </a>

            <a href="{{ route('users.index') }}"
               class="block px-4 py-2 rounded-md flex items-center {{ request()->routeIs('users.index') ? 'bg-blue-100 dark:bg-blue-600 text-blue-700 dark:text-white font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                <i class="bi bi-people-fill text-lg mr-2"></i> Manajemen User
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full text-left px-4 py-2 rounded-md text-red-600 hover:bg-red-100 dark:hover:bg-red-800 flex items-center">
                    <i class="bi bi-box-arrow-right text-lg mr-2"></i> Logout
                </button>
            </form>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Topbar -->
        <header class="flex items-center justify-between px-4 py-4 bg-white dark:bg-gray-800 shadow md:hidden">
            <button @click="sidebarOpen = true" class="text-gray-800 dark:text-white focus:outline-none">
                ☰
            </button>
        </header>

        <!-- Page Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-6">
            {{ $slot }}
        </main>
    </div>
</div>
