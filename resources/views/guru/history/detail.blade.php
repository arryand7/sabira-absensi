<x-app-layout>
    <div class="py-6 max-w-4xl mx-auto">
        <h2 class="text-2xl font-bold mb-6 text-[#292D22]">Detail Absensi Siswa</h2>

        @if ($absensi->isNotEmpty())
            <div class="bg-[#EFF0ED] border border-[#D6D8D2] rounded-lg p-4 mb-6 text-sm text-[#1C1E17] space-y-2">
                <p>
                    <i class="bi bi-journal-text mr-2 text-[#5C644C]"></i>
                    <strong>Mapel:</strong> {{ $absensi[0]->schedule->subject->nama_mapel }}
                </p>
                <p>
                    <i class="bi bi-people-fill mr-2 text-[#5C644C]"></i>
                    <strong>Kelas:</strong> {{ $absensi[0]->schedule->classGroup->nama_kelas }}
                </p>
                <p>
                    <i class="bi bi-hash mr-2 text-[#5C644C]"></i>
                    <strong>Pertemuan Ke-{{ $absensi[0]->pertemuan }}</strong>
                </p>
                <p>
                    <i class="bi bi-calendar-event mr-2 text-[#5C644C]"></i>
                    <strong>{{ \Carbon\Carbon::parse($absensi[0]->tanggal)->format('d M Y') }}</strong>
                </p>
            </div>
        @endif

        <div class="bg-[#F7F7F6] shadow rounded-lg overflow-x-auto">
            <table class="min-w-full divide-y divide-[#D6D8D2] text-sm">
                <thead class="bg-[#5C644C] text-[#F7F7F6] uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nama Siswa</th>
                        <th class="px-4 py-3 text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7E2] text-[#1C1E17]">
                    @foreach ($absensi as $absen)
                        <tr class="hover:bg-[#EFF0ED] transition">
                            <td class="px-4 py-2">{{ $absen->student->nama_lengkap }}</td>
                            <td class="px-4 py-2 capitalize">
                                @php
                                    $color = match($absen->status) {
                                        'hadir' => 'text-green-600',
                                        'alpa' => 'text-red-600',
                                        'sakit' => 'text-yellow-600',
                                        'izin' => 'text-[#5C644C]',
                                        default => 'text-gray-600'
                                    };
                                @endphp
                                <span class="font-semibold {{ $color }}">
                                    {{ ucfirst($absen->status) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
