@php
    $session = $schedule->sessions->first();
    $compact = $compact ?? false;
@endphp
<article class="flex h-full min-w-0 flex-col rounded-[var(--radius-md)] border border-[var(--sabira-border-soft)] bg-[var(--sabira-surface)] {{ $compact ? 'p-3' : 'p-4' }}">
    <div class="flex items-start justify-between gap-2">
        <div class="min-w-0">
            <p class="whitespace-nowrap text-[11px] font-semibold text-[var(--sabira-primary-active)]">{{ substr($schedule->jam_mulai, 0, 5) }}–{{ substr($schedule->jam_selesai, 0, 5) }}</p>
            <h3 class="mt-1 line-clamp-2 min-h-10 text-sm font-semibold leading-5 text-[var(--sabira-ink)]" title="{{ $schedule->subject->nama_mapel }}">{{ $schedule->subject->nama_mapel }}</h3>
        </div>
        @include('guru.schedule.partials.schedule-actions', ['schedule' => $schedule, 'iconOnly' => true])
    </div>
    <p class="mt-2 text-xs text-[var(--sabira-body)]">{{ $schedule->classGroup->nama_kelas }}</p>
    <p class="mt-0.5 truncate text-[11px] text-[var(--sabira-muted)]">{{ $schedule->educationProgram?->name ?? $schedule->classGroup->educationProgram?->name ?? ucfirst($schedule->classGroup->jenis_kelas) }}</p>
    <div class="mt-auto grid gap-2 pt-3">
        <div class="justify-self-start">@if($schedule->has_pending_conflict)<x-status-badge status="Bentrok · Perlu Verifikasi" size="sm" />@else<x-status-badge :status="$session?->status ?? 'Terjadwal'" size="sm" />@endif</div>
        @can('submitAttendance', $schedule)
            <a href="{{ route('guru.schedule.absen', $schedule) }}" class="inline-flex min-h-11 w-full items-center justify-center gap-1.5 rounded-[var(--radius-sm)] bg-[var(--sabira-primary)] px-3 text-xs font-medium text-white hover:bg-[var(--sabira-primary-active)]">
                <i class="fas fa-play text-[10px]" aria-hidden="true"></i> Mulai
            </a>
        @endcan
    </div>
    @if($session?->isSubstituted())
        <p class="mt-2 text-[11px] text-[var(--sabira-muted)]">Guru aktual: {{ $session->actualTeacher?->name }}</p>
    @endif
</article>
