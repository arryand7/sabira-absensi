<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Riwayat Absensi Saya</h2>
    </x-slot>

    <div class="px-4 py-2">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm text-gray-700 hover:text-blue-600">
            <i class="bi bi-arrow-left-circle-fill text-lg mr-1"></i>
        </a>
    </div>

    <div class="py-2 max-w-5xl mx-auto sm:px-6 lg:px-4">
        <div class="bg-white dark:bg-gray-800 shadow rounded-xl p-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 uppercase text-sm">
                        <th class="px-4 py-2">Tanggal</th>
                        <th class="px-4 py-2">Check-In</th>
                        <th class="px-4 py-2">Check-Out</th>
                        <th class="px-4 py-2">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-800 dark:text-gray-200">
                    @foreach ($absensis as $absen)
                        <tr>
                            <td class="px-4 py-2">{{ \Carbon\Carbon::parse($absen->created_at)->format('d M Y') }}</td>
                            <td class="px-4 py-2">{{ $absen->check_in ?? '-' }}</td>
                            <td class="px-4 py-2">{{ $absen->check_out ?? '-' }}</td>
                            <td class="px-4 py-2">
                                <span class="
                                    @if($absen->status == 'Hadir') text-green-600
                                    @elseif($absen->status == 'Terlambat') text-yellow-500
                                    @elseif($absen->status == 'Tidak Hadir') text-red-600
                                    @else text-gray-500
                                    @endif
                                    font-semibold">
                                    {{ $absen->status ?? '-' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
