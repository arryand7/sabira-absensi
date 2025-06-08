<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">
            Riwayat Absensi Saya
        </h2>
    </x-slot>

    <div class="px-4 py-2">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm text-gray-700 hover:text-blue-600">
            <i class="bi bi-arrow-left-circle-fill text-lg mr-1"></i>
            Kembali
        </a>
    </div>

    <div class="py-2 max-w-5xl mx-auto sm:px-6 lg:px-4">
        <div class="bg-white dark:bg-gray-800 shadow rounded-xl p-6 overflow-x-auto">
            <!-- Filter Form -->
            <form method="GET" action="{{ route('karyawan.history') }}" class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="start_date" class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label for="end_date" class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 dark:bg-gray-700 dark:text-white">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded w-full">
                        Filter
                    </button>
                </div>
            </form>

            <!-- Table -->
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 uppercase text-sm">
                        <th class="px-4 py-2 text-left">Tanggal</th>
                        <th class="px-4 py-2 text-left">Check-In</th>
                        <th class="px-4 py-2 text-left">Check-Out</th>
                        <th class="px-4 py-2 text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-800 dark:text-gray-200">
                    @forelse ($absensis as $absen)
                        <tr>
                            <td class="px-4 py-2">{{ \Carbon\Carbon::parse($absen->created_at)->format('d M Y') }}</td>
                            <td class="px-4 py-2">{{ $absen->check_in ?? '-' }}</td>
                            <td class="px-4 py-2">{{ $absen->check_out ?? '-' }}</td>
                            <td class="px-4 py-2 font-semibold">
                                <span class="
                                    @if($absen->status == 'Hadir') text-green-600
                                    @elseif($absen->status == 'Terlambat') text-yellow-500
                                    @elseif($absen->status == 'Tidak Hadir') text-red-600
                                    @else text-gray-500
                                    @endif">
                                    {{ $absen->status ?? '-' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-gray-500 dark:text-gray-400">
                                Tidak ada data absensi pada rentang waktu ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
