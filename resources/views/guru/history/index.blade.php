<x-user-layout>
    
    <div class="px-2 py-2">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm text-gray-700 hover:text-blue-600">
            <i class="bi bi-arrow-left-circle me-1 text-lg"></i> Kembali
        </a>
    </div>

    <div class="p-4 max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-[#292D22]">
                Riwayat Absensi Murid
            </h2>

            <a href="{{ route('dashboard') }}"
            class="bg-[#8E412E] text-white px-4 py-2 rounded-md text-sm sm:text-base hover:bg-[#7A3827] transition">
                ← Kembali
            </a>
        </div>

        <div class="bg-[#5C644C] p-4 rounded-xl shadow mb-6">
            <form method="GET" class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium text-[#F7F7F6]">Kelas</label>
                    <select name="kelas" class="mt-1 w-full border border-[#D6D8D2] rounded p-2 bg-white text-[#1C1E17]">
                        <option value="">Semua</option>
                        @foreach ($kelasList as $kelas)
                            <option value="{{ $kelas }}" {{ request('kelas') == $kelas ? 'selected' : '' }}>{{ $kelas }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#F7F7F6]">Mata Pelajaran</label>
                    <select name="mapel" class="mt-1 w-full border border-[#D6D8D2] rounded p-2 bg-white text-[#1C1E17]">
                        <option value="">Semua</option>
                        @foreach ($mapelList as $mapel)
                            <option value="{{ $mapel }}" {{ request('mapel') == $mapel ? 'selected' : '' }}>{{ $mapel }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="bg-[#292D22] hover:bg-[#292D22] text-white px-4 py-2 rounded w-full sm:w-auto">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto bg-[#F7F7F6] rounded-xl shadow">
            <table class="min-w-full text-sm">
                <thead class="bg-[#5C644C] text-[#F7F7F6]">
                    <tr>
                        <th class="px-4 py-3 text-left">Mapel</th>
                        <th class="px-4 py-3 text-left">Kelas</th>
                        <th class="px-4 py-3 text-left">Pertemuan</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Materi</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-[#EFF0ED] text-[#1C1E17] divide-y divide-[#D6D8D2]">
                    @forelse ($sessions as $session)
                        @php
                            $materi = $session->attendances->first()?->materi;
                        @endphp
                        <tr class="hover:bg-[#D6D8D2] transition">
                            <td class="px-4 py-2">{{ $session->schedule->subject->nama_mapel }}</td>
                            <td class="px-4 py-2">{{ $session->schedule->classGroup->nama_kelas }}</td>
                            <td class="px-4 py-2">{{ $session->meeting_no ?? '-' }}</td>
                            <td class="px-4 py-2">{{ \Carbon\Carbon::parse($session->date)->format('d M Y') }}</td>
                            <td class="px-4 py-2">{{ $materi ?? '-' }}</td>
                            <td class="px-4 py-2 text-center space-x-2">
                                @if ($session->meeting_no)
                                    <a href="{{ route('guru.history.detail', [$session->schedule_id, $session->meeting_no]) }}"
                                       class="text-[#5C644C] hover:text-[#373C2E]" title="Lihat Absensi">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('guru.history.edit', [$session->schedule_id, $session->meeting_no]) }}"
                                       class="text-[#8D9382] hover:text-[#5C644C]" title="Edit Absensi">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                @else
                                    <span class="text-[#8D9382] text-xs">Tidak tersedia</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-4 text-center text-[#8D9382]">Belum ada riwayat mengajar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-user-layout>
