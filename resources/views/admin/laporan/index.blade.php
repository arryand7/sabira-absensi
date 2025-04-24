<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Laporan Absensi Karyawan') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 bg-white dark:bg-gray-800 p-6 rounded-lg shadow">

            {{-- Filter --}}
            <form action="{{ route('laporan.karyawan.export') }}" method="GET" class="flex gap-4 mb-6 items-end">
                <div>
                    <label for="divisi" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Divisi</label>
                    <select name="divisi" id="divisi" class="w-full rounded border-gray-300 dark:bg-gray-700 dark:text-white">
                        <option value="">Semua</option>
                        @foreach($divisis as $d)
                            <option value="{{ $d->nama }}" {{ request('divisi') == $d->nama ? 'selected' : '' }}>
                                {{ $d->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="bulan" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bulan</label>
                    <select name="bulan" id="bulan" class="w-full rounded border-gray-300 dark:bg-gray-700 dark:text-white">
                        @foreach(range(1, 12) as $bulan)
                            <option value="{{ $bulan }}" {{ request('bulan') == $bulan ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($bulan)->locale('id')->monthName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="tahun" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tahun</label>
                    <select name="tahun" id="tahun" class="w-full rounded border-gray-300 dark:bg-gray-700 dark:text-white">
                        @for($th = 2023; $th <= now()->year + 1; $th++)
                            <option value="{{ $th }}" {{ request('tahun') == $th ? 'selected' : '' }}>{{ $th }}</option>
                        @endfor
                    </select>
                </div>

                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                    Generate Sheet
                </button>
            </form>

            {{-- Table --}}
            <table class="w-full table-auto text-left text-sm text-gray-500 dark:text-gray-400">
                <thead>
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
                            <td class="px-4 py-2">
                                {{ $row['user']->karyawan->divisi->nama ?? '-' }}
                            </td>
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
</x-app-layout>
