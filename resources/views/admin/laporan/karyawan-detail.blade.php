<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidenav />
    </x-slot>

    <div class="w-full sm:px-6 lg:px-8 mt-6 space-y-6">
        <h2 class="font-semibold text-xl text-[#292D22]">Detail Absensi - {{ $user->name }}</h2>

        <div class="bg-[#EEF3E9] border border-[#D6D8D2] shadow-md rounded-2xl p-6">
            <p class="text-lg font-semibold text-[#292D22] mb-4">Data Absensi: {{ $user->name }}</p>

            <form method="GET" action="{{ route('laporan.karyawan.detail', $user->id) }}" class="mb-6 flex flex-wrap gap-4 items-end">
                {{-- Bulan --}}
                <div>
                    <label for="bulan" class="block text-sm font-medium text-gray-700">Bulan</label>
                    <select name="bulan" id="bulan" class="rounded-md border-gray-300 shadow-sm focus:ring focus:ring-orange-200">
                        @foreach(range(1, 12) as $b)
                            <option value="{{ $b }}" {{ request('bulan') == $b ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($b)->locale('id')->monthName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tahun --}}
                <div>
                    <label for="tahun" class="block text-sm font-medium text-gray-700">Tahun</label>
                    <select name="tahun" id="tahun" class="rounded-md border-gray-300 shadow-sm focus:ring focus:ring-orange-200">
                        @for($y = 2023; $y <= now()->year + 1; $y++)
                            <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                {{-- Tombol --}}
                <div class="flex gap-2 mt-1">
                    <button type="submit"
                            class="bg-[#8E412E] text-white px-4 py-2 rounded-md hover:bg-[#BA6F4D] shadow flex items-center gap-2">
                        <i class="bi bi-funnel-fill"></i> Tampilkan
                    </button>

                    <a href="{{ route('laporan.karyawan.detail.export', ['id' => $user->id, 'bulan' => request('bulan'), 'tahun' => request('tahun')]) }}"
                       class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 shadow flex items-center gap-2">
                        <i class="bi bi-file-earmark-excel-fill"></i> Download Excel
                    </a>
                </div>
            </form>

            <div class="overflow-x-auto rounded-lg">
                <table class="min-w-full text-sm text-left text-[#292D22]">
                    <thead class="bg-[#8D9382] text-white uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-4 py-2">Tanggal</th>
                            <th class="px-4 py-2">Jam Hadir</th>
                            <th class="px-4 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D6D8D2]">
                        @forelse($absensi as $a)
                            <tr class="hover:bg-[#BEC1B7] transition">
                                <td class="px-4 py-2">{{ $a->tanggal }}</td>
                                <td class="px-4 py-2">{{ $a->jam }}</td>
                                <td class="px-4 py-2">
                                    @php
                                        $statusColor = match($a->status) {
                                            'Hadir' => 'bg-green-100 text-green-700',
                                            'Terlambat' => 'bg-yellow-100 text-yellow-700',
                                            // 'Izin' => 'bg-blue-100 text-blue-700',
                                            // 'Sakit' => 'bg-purple-100 text-purple-700',
                                            default => 'bg-red-100 text-red-700',
                                        };
                                    @endphp
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">
                                        {{ $a->status ?? '-' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-4 text-center text-[#6B7280] italic">Belum ada data absensi</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
