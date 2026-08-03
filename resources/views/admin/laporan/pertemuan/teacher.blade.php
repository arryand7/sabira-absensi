<x-app-shell>
<div class="sm:px-6 lg:px-8"><x-page-title title="LAPORAN PELAKSANAAN MENGAJAR" /></div>

    <div class="mt-6 space-y-6 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border bg-white p-5 shadow-sm">
            <div><h2 class="text-lg font-semibold">{{ $teacher->name }}</h2><p class="text-sm text-gray-500">Kehadiran mengajar dan kepatuhan geofence</p></div>
            <form method="GET" class="flex flex-wrap items-end gap-2"><div><label class="block text-xs text-gray-500">Dari</label><input type="date" name="start_date" value="{{ $startDate }}" class="rounded-md border-gray-300 text-sm"></div><div><label class="block text-xs text-gray-500">Sampai</label><input type="date" name="end_date" value="{{ $endDate }}" class="rounded-md border-gray-300 text-sm"></div><button class="rounded-md bg-[var(--sabira-primary)] px-3 py-2 text-sm text-white">Tampilkan</button></form>
        </div>

        <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
            <x-stat-card title="Terjadwal" :value="$summary['total_scheduled']" color="slate" icon="fas fa-calendar" />
            <x-stat-card title="Mengajar" :value="$summary['total_taught']" color="emerald" icon="fas fa-chalkboard-user" />
            <x-stat-card title="Sebagai Pengganti" :value="$summary['substitute_taught']" color="blue" icon="fas fa-user-clock" />
            <x-stat-card title="Di Luar Radius" :value="$summary['outside_geofence']" color="rose" icon="fas fa-location-crosshairs" />
            <x-stat-card title="Kepatuhan Lokasi" value="{{ number_format($summary['geofence_compliance_rate'], 1) }}%" color="indigo" icon="fas fa-shield" />
        </div>

        @if($summary['has_anomaly'])<div class="rounded-xl border border-amber-300 bg-amber-50 p-5 text-sm text-amber-900"><strong>Anomali terdeteksi</strong><ul class="mt-1 list-disc pl-5">@foreach($summary['anomaly_reasons'] as $reason)<li>{{ $reason }}</li>@endforeach</ul></div>@endif

        <div class="overflow-x-auto rounded-xl bg-white shadow-sm"><table class="min-w-full text-sm"><thead class="bg-[var(--sabira-primary)] text-white"><tr><th class="px-4 py-3 text-left">Tanggal</th><th class="px-4 py-3 text-left">Mapel</th><th class="px-4 py-3 text-left">Kelas</th><th class="px-4 py-3 text-left">Peran</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3 text-left">Geofence</th></tr></thead><tbody class="divide-y">
            @forelse($sessions as $session)<tr><td class="px-4 py-3">{{ \Carbon\Carbon::parse($session->date)->format('d M Y') }}</td><td class="px-4 py-3">{{ $session->schedule->subject->nama_mapel }}</td><td class="px-4 py-3">{{ $session->schedule->classGroup->nama_kelas }}</td><td class="px-4 py-3">{{ $session->actual_teacher_id === $teacher->id && $session->scheduled_teacher_id !== $teacher->id ? 'Guru pengganti' : 'Guru terjadwal' }}</td><td class="px-4 py-3 capitalize">{{ $session->status }}</td><td class="px-4 py-3">{{ str_replace('_', ' ', $session->location_validation_status) }}</td></tr>@empty<tr><td colspan="6" class="px-4 py-10 text-center text-gray-500">Belum ada sesi pada periode ini.</td></tr>@endforelse
        </tbody></table></div>
        {{ $sessions->links() }}
        <a href="{{ route('laporan.pertemuan', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="inline-block text-sm text-[#8E412E] hover:underline">← Kembali ke rekap pertemuan</a>
    </div>
</x-app-shell>
