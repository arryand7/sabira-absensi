<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Detail Absensi - ' . $user->name) }}
        </h2>
    </x-slot>

    <div class="flex">
        <x-admin-sidenav />
        <div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-xl p-6">
                <div class="mb-4">
                    <p class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                        Data Absensi: {{ $user->name }}
                    </p>
                </div>
                <form method="GET" action="{{ route('laporan.karyawan.detail', $user->id) }}" class="mb-4 flex flex-wrap gap-4 items-end">
                    <div>
                        <label for="bulan" class="text-sm text-gray-700 dark:text-gray-300">Bulan</label>
                        <select name="bulan" id="bulan" class="rounded border-gray-300 dark:bg-gray-700 dark:text-white">
                            @foreach(range(1, 12) as $b)
                                <option value="{{ $b }}" {{ request('bulan') == $b ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($b)->locale('id')->monthName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="tahun" class="text-sm text-gray-700 dark:text-gray-300">Tahun</label>
                        <select name="tahun" id="tahun" class="rounded border-gray-300 dark:bg-gray-700 dark:text-white">
                            @for($y = 2023; $y <= now()->year + 1; $y++)
                                <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            Tampilkan
                        </button>
                        <a href="{{ route('laporan.karyawan.detail.export', ['id' => $user->id, 'bulan' => request('bulan'), 'tahun' => request('tahun')]) }}"
                        class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                            Download Excel
                        </a>
                    </div>
                </form>
                <table class="w-full table-auto text-left text-sm text-gray-500 dark:text-gray-400">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2">Tanggal</th>
                            <th class="px-4 py-2">Jam Hadir</th>
                            <th class="px-4 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($absensi as $a)
                            <tr class="border-b">
                                <td class="px-4 py-2">{{ $a->tanggal }}</td>
                                <td class="px-4 py-2">{{ $a->jam }}</td>
                                <td class="px-4 py-2">{{ ucfirst($a->status) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-4 py-2" colspan="3">Belum ada data absensi</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
