<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Laporan Absensi Karyawan') }}
        </h2>
    </x-slot>

    <div class="flex">

        <x-admin-sidenav />

        <div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-xl p-6">

                {{-- Filter --}}

                <form action="{{ route('laporan.karyawan') }}" method="GET" class="mb-6 flex flex-wrap gap-4 items-end" id="filterForm">
                    {{-- Divisi --}}
                    <div>
                        <label for="divisi" class="text-sm text-gray-700 dark:text-gray-300">Divisi</label>
                        <select name="divisi" id="divisi" class="w-full rounded border-gray-300 dark:bg-gray-700 dark:text-white">
                            <option value="">Semua</option>
                            @foreach($divisis as $d)
                                <option value="{{ $d->nama }}" {{ request('divisi') == $d->nama ? 'selected' : '' }}>
                                    {{ $d->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Rentang Tanggal --}}
                    <div>
                        <label for="start_date" class="text-sm text-gray-700 dark:text-gray-300">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full rounded border-gray-300 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label for="end_date" class="text-sm text-gray-700 dark:text-gray-300">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full rounded border-gray-300 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Filter</button>
                        <a href="{{ route('laporan.karyawan.export', [
                            'divisi' => request('divisi'),
                            'start_date' => request('start_date'),
                            'end_date' => request('end_date'),
                        ]) }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                            Download Excel
                        </a>
                    </div>
                </form>
                {{-- Table --}}
                <table class="w-full table-auto text-left text-sm text-gray-500 dark:text-gray-400">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2">Nama</th>
                            <th class="px-4 py-2">Email</th>
                            <th class="px-4 py-2">Divisi</th>
                            <th class="px-4 py-2">Total Hadir</th>
                            <th class="px-4 py-2">Total Absen</th>
                            <th class="px-4 py-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($laporan as $row)
                            <tr class="border-b">
                                <td class="px-4 py-2">{{ $row['user']->name }}</td>
                                <td class="px-4 py-2">{{ $row['user']->email }}</td>
                                <td class="px-4 py-2">{{ $row['user']->karyawan->divisi->nama ?? '-' }}</td>
                                <td class="px-4 py-2">{{ $row['hadir'] }}</td>
                                <td class="px-4 py-2">{{ $row['absen'] }}</td>
                                <td class="px-4 py-2">
                                    <a href="{{ route('laporan.karyawan.detail', $row['user']->id) }}"
                                    class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                                        Lihat Absensi
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Script auto-submit --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('divisi').addEventListener('change', function () {
                document.getElementById('filterForm').submit();
            });
        });
    </script>
    @endpush
</x-app-layout>
