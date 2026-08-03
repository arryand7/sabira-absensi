<x-app-shell>
    
    <div class="px-2 py-2">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm text-gray-700 hover:text-blue-600">
            <i class="bi bi-arrow-left-circle me-1 text-lg"></i> Kembali
        </a>
    </div>

    <div class="p-4 max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-[var(--sabira-ink)]">
                Riwayat Absensi Murid
            </h2>

            <a href="{{ route('dashboard') }}"
            class="bg-[var(--sabira-primary)] text-white px-4 py-2 rounded-md text-sm sm:text-base hover:bg-[var(--sabira-primary-active)] transition">
                ← Kembali
            </a>
        </div>

        <div class="bg-[var(--sabira-primary)] p-4 rounded-xl shadow mb-6">
            <form method="GET" class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium text-[#F7F7F6]">Kelas</label>
                    <select name="kelas" class="mt-1 w-full border border-[var(--sabira-border)] rounded p-2 bg-white text-[var(--sabira-ink)]">
                        <option value="">Semua</option>
                        @foreach ($kelasList as $kelas)
                            <option value="{{ $kelas }}" {{ request('kelas') == $kelas ? 'selected' : '' }}>{{ $kelas }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#F7F7F6]">Mata Pelajaran</label>
                    <select name="mapel" class="mt-1 w-full border border-[var(--sabira-border)] rounded p-2 bg-white text-[var(--sabira-ink)]">
                        <option value="">Semua</option>
                        @foreach ($mapelList as $mapel)
                            <option value="{{ $mapel }}" {{ request('mapel') == $mapel ? 'selected' : '' }}>{{ $mapel }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="bg-[var(--sabira-primary)] hover:bg-[var(--sabira-primary)] text-white px-4 py-2 rounded w-full sm:w-auto">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto bg-[var(--sabira-surface-soft)] rounded-xl shadow">
            <table class="min-w-full text-sm">
                <thead class="bg-[var(--sabira-primary)] text-[#F7F7F6]">
                    <tr>
                        <th class="px-4 py-3 text-left">Mapel</th>
                        <th class="px-4 py-3 text-left">Kelas</th>
                        <th class="px-4 py-3 text-left">Pertemuan</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Materi</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-[var(--sabira-surface-soft)] text-[var(--sabira-ink)] divide-y divide-[#D6D8D2]">
                    @forelse ($sessions as $session)
                        @php
                            $materi = $session->attendances->first()?->materi;
                        @endphp
                        <tr class="hover:bg-[var(--sabira-surface-strong)] transition">
                            <td class="px-4 py-2">{{ $session->schedule->subject->nama_mapel }}</td>
                            <td class="px-4 py-2">{{ $session->schedule->classGroup->nama_kelas }}</td>
                            <td class="px-4 py-2">{{ $session->meeting_no ?? '-' }}</td>
                            <td class="px-4 py-2">{{ \Carbon\Carbon::parse($session->date)->format('d M Y') }}</td>
                            <td class="px-4 py-2">{{ $materi ?? '-' }}</td>
                            <td class="px-4 py-2 text-center space-x-2">
                                @if ($session->meeting_no)
                                    <a href="{{ route('guru.history.detail', $session) }}"
                                       class="text-[var(--sabira-muted)] hover:text-[var(--sabira-body)]" title="Lihat Absensi">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @php($latestCorrection = $session->corrections->first())
                                    @if($latestCorrection)
                                        <span class="ml-2 text-xs {{ $latestCorrection->status === 'pending' ? 'text-amber-700' : ($latestCorrection->status === 'approved' ? 'text-green-700' : 'text-red-700') }}">
                                            Koreksi {{ $latestCorrection->status }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-[var(--sabira-muted)] text-xs">Tidak tersedia</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-4 text-center text-[var(--sabira-muted)]">Belum ada riwayat mengajar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $sessions->links() }}</div>
    </div>
</x-app-shell>
