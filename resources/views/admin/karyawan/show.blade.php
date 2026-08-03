<x-app-shell headerTitle="Detail Pegawai & Guru" headerSubtitle="Profil, program, jadwal, dan kehadiran nyata">
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div><h2 class="text-xl font-bold text-slate-900">{{ $karyawan->user->name }}</h2><p class="text-sm text-slate-500">{{ $karyawan->user->email }} · {{ strtoupper($karyawan->user->role) }}</p><p class="mt-1 text-xs text-slate-500">{{ $karyawan->divisi?->nama ?? 'Tanpa divisi' }} · {{ $karyawan->no_hp ?? 'Nomor HP belum diisi' }}</p></div>
                <div class="flex gap-2"><a href="{{ route('karyawan.edit', $karyawan) }}" class="rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-white">Edit Profil</a><a href="{{ route('laporan.karyawan.detail', $karyawan->user_id) }}" class="rounded-lg bg-[var(--sabira-primary)] px-4 py-2 text-xs font-bold text-white">Laporan Kehadiran</a></div>
            </div>
            @if($karyawan->user->guru)
                <div class="mt-4 border-t pt-4 text-xs"><span class="font-bold">Program diajar:</span> {{ $karyawan->user->guru->educationPrograms->pluck('name')->join(', ') ?: ucfirst($karyawan->user->guru->jenis) }}</div>
            @endif
        </div>
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><h3 class="mb-4 text-sm font-bold">Jadwal Mengajar</h3><div class="space-y-2">@forelse($schedules as $schedule)<div class="rounded-lg bg-slate-50 p-3 text-xs"><b>{{ $schedule->hari }}, {{ substr($schedule->jam_mulai, 0, 5) }}–{{ substr($schedule->jam_selesai, 0, 5) }}</b><br>{{ $schedule->subject->nama_mapel }} · {{ $schedule->classGroup->nama_kelas }}</div>@empty<x-empty-state title="Tidak Ada Jadwal" description="Pegawai ini belum memiliki jadwal mengajar." icon="fas fa-calendar" />@endforelse</div></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><h3 class="mb-4 text-sm font-bold">Kehadiran Kerja Terbaru</h3><div class="space-y-2">@forelse($absensi as $item)<div class="flex justify-between rounded-lg bg-slate-50 p-3 text-xs"><span>{{ \Carbon\Carbon::parse($item->waktu_absen)->format('d M Y') }}</span><span><b>{{ $item->check_in ?? '-' }}</b> / {{ $item->check_out ?? '-' }} · {{ $item->status }}</span></div>@empty<x-empty-state title="Belum Ada Kehadiran" description="Belum ada catatan presensi kerja." icon="fas fa-fingerprint" />@endforelse</div><div class="mt-4">{{ $absensi->links() }}</div></div>
        </div>
    </div>
</x-app-shell>
