<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard Admin') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Ringkasan Statistik --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                    <h3 class="text-gray-600 dark:text-gray-300 text-sm font-medium">Total Karyawan</h3>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ \App\Models\Karyawan::count() }}</p>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                    <h3 class="text-gray-600 dark:text-gray-300 text-sm font-medium">Absensi Hari Ini</h3>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                        {{ \App\Models\Absensi::whereDate('created_at', now()->toDateString())->count() }}
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                    <h3 class="text-gray-600 dark:text-gray-300 text-sm font-medium">Perlu Dicek</h3>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">-</p>
                </div>
            </div>

            {{-- Navigasi --}}
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Navigasi</h3>
                <div class="space-y-2">
                    {{-- <a href="{{ route('karyawan.index') }}" class="block text-blue-600 hover:underline">
                        ➤ Manajemen Data Karyawan
                    </a> --}}
                    <a href="{{ route('absensi.index') }}" class="block text-blue-600 hover:underline">
                        ➤ Rekap Absensi
                    </a>
                    <a href="{{ route('laporan.karyawan') }}" class="block text-blue-600 hover:underline">
                        ➤ laporan Absensi
                    </a>
                    <a href="{{ route('users.index') }}" class="block text-blue-600 hover:underline">
                        ➤ Manajemen User
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
