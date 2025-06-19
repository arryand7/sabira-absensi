<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidenav />
    </x-slot>

    <div class="w-full mt-6 sm:px-6 lg:px-8 space-y-6">
        {{-- Ringkasan Statistik --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @php
                $stats = [
                    ['title' => 'Total Karyawan', 'value' => $totalKaryawan],
                    ['title' => 'Absensi Hari Ini', 'value' => $totalSudahAbsen],
                    ['title' => 'Belum Absen', 'value' => $totalBelumHadir],
                ];
            @endphp

            @foreach ($stats as $item)
                <div class="bg-[#EFF0ED] p-5 rounded-2xl shadow-md border border-[#D6D8D2]">
                    <h3 class="text-sm font-medium text-[#5C644C]">{{ $item['title'] }}</h3>
                    <p class="mt-2 text-4xl font-bold text-[#1C1E17]">{{ $item['value'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Laporan Absensi Hari Ini --}}
        <div class="bg-[#EEF3E9] p-6 rounded-2xl shadow-md border border-[#D6D8D2]">
            <h3 class="text-lg font-semibold text-[#292D22] mb-4">Absensi Hari Ini</h3>

            <div class="overflow-x-auto rounded-xl">
                <table class="min-w-full text-sm text-[#292D22]">
                    <thead class="bg-[#8D9382] text-white text-left text-xs font-semibold uppercase">
                        <tr>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Check In</th>
                            <th class="px-4 py-3">Check Out</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D6D8D2]">
                        @forelse ($absensis as $absen)
                            <tr class="hover:bg-[#BEC1B7] transition">
                                <td class="px-4 py-3">{{ $absen->user->name }}</td>
                                <td class="px-4 py-3">{{ \Carbon\Carbon::parse($absen->waktu_absen)->format('d M Y') }}</td>
                                <td class="px-4 py-3">{{ $absen->check_in ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $absen->check_out ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $statusColor = match($absen->status) {
                                            'Hadir' => 'bg-green-100 text-green-700',
                                            'Terlambat' => 'bg-yellow-100 text-yellow-700',
                                            // 'Izin' => 'bg-blue-100 text-blue-700',
                                            // 'Sakit' => 'bg-purple-100 text-purple-700',
                                            default => 'bg-red-100 text-red-700',
                                        };
                                    @endphp
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">
                                        {{ $absen->status ?? '-' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-6 text-[#8D9382] italic">
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
