<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Rekap Absensi') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
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
                    @foreach($absensis as $absen)
                        <tr class="border-b">
                            <td class="px-4 py-2">{{ $absen->user->name }}</td>
                            <td class="px-4 py-2">{{ \Carbon\Carbon::parse($absen->created_at)->format('d M Y') }}</td>
                            <td class="px-4 py-2">{{ $absen->check_in ?? '-' }}</td>
                            <td class="px-4 py-2">{{ $absen->check_out ?? '-' }}</td>
                            <td class="px-4 py-2">{{ $absen->status ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
