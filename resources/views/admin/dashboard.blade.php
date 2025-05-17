<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard Admin') }}
        </h2>
    </x-slot>

    <div class="flex">
        {{-- sidebar --}}
        <x-admin-sidenav />

        <div class="w-full mt-6 sm:px-6 lg:px-8 space-y-6">
            {{-- Ringkasan Statistik --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                    <h3 class="text-gray-600 dark:text-gray-300 text-sm font-medium">Total Karyawan</h3>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $totalKaryawan }}</p>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                    <h3 class="text-gray-600 dark:text-gray-300 text-sm font-medium">Absensi Hari Ini</h3>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $totalSudahAbsen }}</p>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                    <h3 class="text-gray-600 dark:text-gray-300 text-sm font-medium">Belum Absen</h3>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $totalBelumHadir }}</p>
                </div>
            </div>


            {{-- laporan absen --}}
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Absensi Hari Ini</h3>
                <table class="w-full table-auto text-left text-sm text-gray-500 dark:text-gray-400">
                    <thead>
                        <tr>
                            <th class="px-4 py-2">Nama</th>
                            <th class="px-4 py-2">Tanggal</th>
                            <th class="px-4 py-2">Check In</th>
                            <th class="px-4 py-2">Check Out</th>
                            <th class="px-4 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($absensis as $absen)
                            <tr class="border-b">
                                <td class="px-4 py-2">{{ $absen->user->name }}</td>
                                <td class="px-4 py-2">{{ \Carbon\Carbon::parse($absen->waktu_absen)->format('d M Y') }}</td>
                                <td class="px-4 py-2">{{ $absen->check_in ?? '-' }}</td>
                                <td class="px-4 py-2">{{ $absen->check_out ?? '-' }}</td>
                                <td class="px-4 py-2">{{ $absen->status ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-gray-600 dark:text-gray-300">
                                    Belum ada yang absen hari ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
