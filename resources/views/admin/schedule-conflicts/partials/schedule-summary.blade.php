@if($schedule)
    <div class="min-w-52 {{ $schedule->trashed() ? 'opacity-60' : '' }}">
        <strong class="block text-[var(--sabira-ink)]">{{ $schedule->subject?->nama_mapel ?? '-' }}</strong>
        <span class="block text-xs text-[var(--sabira-body)]">{{ $schedule->classGroup?->nama_kelas ?? '-' }} · {{ $schedule->hari }}</span>
        <span class="block text-xs text-[var(--sabira-muted)]">{{ substr($schedule->jam_mulai, 0, 5) }}–{{ substr($schedule->jam_selesai, 0, 5) }} · {{ ucfirst($schedule->semester) }}</span>
        @if($schedule->trashed())<span class="mt-1 inline-block text-xs font-semibold text-[var(--sabira-danger)]">Dinonaktifkan</span>@endif
    </div>
@else
    <span class="text-[var(--sabira-muted)]">Jadwal tidak tersedia</span>
@endif
