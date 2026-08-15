<x-app-shell>
<div class="sm:px-6 lg:px-8"><x-page-title title="PROGRES KEHADIRAN SISWA" /></div>

    <div class="mt-6 space-y-6 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border bg-white p-5 shadow-sm">
            <div><h2 class="text-lg font-semibold">{{ $student->nama_lengkap }}</h2><p class="text-sm text-gray-500">NIS: {{ $student->nis ?: '-' }}</p></div>
            <form method="GET" class="flex items-end gap-2"><div><label class="block text-xs text-gray-500">Tahun Ajaran</label><select name="tahun_ajaran" class="rounded-md border-gray-300 text-sm">@foreach($academicYears as $year)<option value="{{ $year->id }}" @selected((int)$selectedYear === $year->id)>{{ $year->name }}</option>@endforeach</select></div><button class="rounded-md bg-[var(--sabira-primary)] px-3 py-2 text-sm text-white">Tampilkan</button></form>
        </div>

        <div class="grid grid-cols-2 gap-3 lg:grid-cols-6">
            <x-stat-card title="Pertemuan" :value="$summary['total_meetings']" color="slate" icon="fas fa-calendar-check" />
            <x-stat-card title="Hadir" :value="$summary['hadir']" color="emerald" icon="fas fa-check" />
            <x-stat-card title="Izin" :value="$summary['izin']" color="blue" icon="fas fa-file-circle-check" />
            <x-stat-card title="Sakit" :value="$summary['sakit']" color="amber" icon="fas fa-kit-medical" />
            <x-stat-card title="Alpa" :value="$summary['alpa']" color="rose" icon="fas fa-xmark" />
            <x-stat-card title="Kehadiran" value="{{ number_format($summary['attendance_rate'], 1) }}%" color="indigo" icon="fas fa-chart-line" />
        </div>

        <div class="rounded-xl border bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3"><div><h3 class="font-semibold">Status Risiko: <span class="uppercase {{ $summary['risk_level'] === 'high' ? 'text-red-700' : ($summary['risk_level'] === 'medium' ? 'text-amber-700' : 'text-green-700') }}">{{ $summary['risk_level'] }}</span></h3>@foreach($summary['risk_reasons'] as $reason)<p class="text-sm text-gray-600">{{ $reason }}</p>@endforeach</div><div class="flex gap-2"><a href="{{ route('laporan.murid.download', ['student' => $student, 'tahun_ajaran' => $selectedYear]) }}" class="rounded-md bg-green-700 px-3 py-2 text-sm text-white">PDF</a><a href="{{ route('laporan.murid.download.excel', ['student' => $student, 'tahun_ajaran' => $selectedYear]) }}" class="rounded-md bg-blue-700 px-3 py-2 text-sm text-white">Excel</a></div></div>
        </div>

        <div class="rounded-xl border bg-white p-5 shadow-sm">
            <h3 class="font-semibold">Keanggotaan Kelas</h3>
            <div class="mt-3 space-y-3">
                @forelse($memberships as $class)
                    <div class="rounded-xl border p-3 text-sm {{ $class->pivot->status === 'entered_in_error' ? 'border-rose-200 bg-rose-50' : 'border-slate-200 bg-slate-50' }}">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <strong>{{ $class->nama_kelas }}</strong> · {{ ucfirst($class->jenis_kelas) }} · {{ str_replace('_', ' ', $class->class_type ?? 'reguler') }}
                                <div class="mt-1 text-xs text-slate-600">Status: {{ $class->pivot->status === 'entered_in_error' ? 'Salah Input / Entered in Error' : ucfirst($class->pivot->status) }}</div>
                                @if($class->pivot->status === 'entered_in_error')
                                    <div class="mt-1 text-xs text-rose-700">Dibatalkan oleh: {{ $invalidators[$class->pivot->invalidated_by] ?? 'User tidak tersedia' }} · Tanggal: {{ $class->pivot->invalidated_at }} · Alasan: {{ $class->pivot->invalidation_reason }}</div>
                                @endif
                            </div>
                            @if($class->pivot->status === 'active')
                                <form method="POST" action="{{ route('promotion.promote') }}" onsubmit="return confirm('Batalkan keanggotaan ini sebagai salah input? Histori attendance tidak akan dihapus.')" class="flex items-end gap-2">
                                    @csrf
                                    <input type="hidden" name="to_class_id" value="{{ $class->id }}">
                                    <input type="hidden" name="action_mode" value="invalidate">
                                    <input type="hidden" name="student_ids[]" value="{{ $student->id }}">
                                    <label class="text-xs">Alasan<input required minlength="5" maxlength="1000" name="invalidation_reason" class="ml-2 rounded-md border-slate-300 text-xs" placeholder="Alasan pembatalan"></label>
                                    <button class="rounded-md bg-rose-700 px-3 py-2 text-xs font-semibold text-white">Batalkan Keanggotaan</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <span class="text-sm text-gray-500">Tidak ada keanggotaan pada tahun ini.</span>
                @endforelse
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl bg-white shadow-sm">
            <table class="min-w-full text-sm"><thead class="bg-[var(--sabira-primary)] text-white"><tr><th class="px-4 py-3 text-left">Tanggal</th><th class="px-4 py-3 text-left">Mata Pelajaran</th><th class="px-4 py-3 text-left">Kelas</th><th class="px-4 py-3 text-left">Pertemuan</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3 text-left">Materi</th></tr></thead><tbody class="divide-y">
                @forelse($timeline as $attendance)<tr><td class="px-4 py-3">{{ \Carbon\Carbon::parse($attendance->tanggal)->format('d M Y') }}</td><td class="px-4 py-3">{{ $attendance->schedule->subject->nama_mapel ?? '-' }}</td><td class="px-4 py-3">{{ $attendance->schedule->classGroup->nama_kelas ?? '-' }}</td><td class="px-4 py-3">{{ $attendance->session?->meeting_no ?? $attendance->pertemuan }}</td><td class="px-4 py-3 capitalize">{{ $attendance->status }}</td><td class="px-4 py-3">{{ $attendance->materi ?: '-' }}</td></tr>@empty<tr><td colspan="6" class="px-4 py-10 text-center text-gray-500">Belum ada data kehadiran.</td></tr>@endforelse
            </tbody></table>
        </div>
        {{ $timeline->links() }}
        <a href="{{ route('laporan.murid', ['tahun_ajaran' => $selectedYear]) }}" class="inline-block text-sm text-[#8E412E] hover:underline">← Kembali ke laporan siswa</a>
    </div>
</x-app-shell>
