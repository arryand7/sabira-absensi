<x-app-layout>
    <div class="flex min-h-screen">
        <x-admin-sidenav />

        <div class="flex-1 p-6">
            <h1 class="text-2xl font-bold  mb-6">
                Dashboard Laporan Murid
            </h1>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <a href="{{ route('laporan.murid') }}"
                   class="bg-white dark:bg-gray-800 shadow-md hover:shadow-lg transition duration-300 rounded-xl p-8 text-center text-lg font-semibold text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700">
                    📚 Absen Kelas
                </a>

                <a href="{{ route('laporan.murid.mapel') }}"
                   class="bg-white dark:bg-gray-800 shadow-md hover:shadow-lg transition duration-300 rounded-xl p-8 text-center text-lg font-semibold text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700">
                    📝 Absen Mata Pelajaran
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
