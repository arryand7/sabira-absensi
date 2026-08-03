@php
    $session = $schedule->sessions->first();
    $target = $session?->status === 'completed'
        ? route('guru.history.detail', $session)
        : route('guru.schedule.absen', $schedule);
@endphp
<a href="{{ $target }}" class="block rounded-[var(--radius-sm)] border p-2 text-center no-underline transition hover:border-[var(--sabira-primary)] hover:bg-[var(--sabira-surface-soft)] {{ $schedule->has_pending_conflict ? 'border-[var(--sabira-warning)] bg-[color-mix(in_srgb,var(--sabira-warning)_7%,var(--sabira-surface))]' : 'border-[var(--sabira-border-soft)] bg-[var(--sabira-surface)]' }}" title="Buka {{ $schedule->subject->nama_mapel }} {{ $schedule->classGroup->nama_kelas }}">
    <span class="block text-[9px] text-[var(--sabira-muted)]">{{ substr($schedule->jam_mulai, 0, 5) }}–{{ substr($schedule->jam_selesai, 0, 5) }}</span>
    <strong class="mt-0.5 line-clamp-2 block text-[11px] font-semibold leading-4 text-[var(--sabira-ink)]">{{ $schedule->subject->nama_mapel }}</strong>
    <span class="mt-0.5 block truncate text-[10px] text-[var(--sabira-body)]">{{ $schedule->classGroup->nama_kelas }}</span>
    <span class="block truncate text-[9px] text-[var(--sabira-muted)]">{{ $schedule->educationProgram?->name ?? $schedule->classGroup->educationProgram?->name ?? ucfirst($schedule->classGroup->jenis_kelas) }}</span>
    @if($schedule->has_pending_conflict)
        <span class="mt-1 inline-flex items-center gap-1 rounded-full px-1.5 py-0.5 text-[9px] font-semibold text-[var(--sabira-warning)]"><i class="fas fa-triangle-exclamation"></i> Bentrok</span>
    @elseif($session)
        <span class="mt-1 block text-[9px] font-medium text-[var(--sabira-muted)]">{{ ucfirst($session->status) }}</span>
    @endif
</a>
