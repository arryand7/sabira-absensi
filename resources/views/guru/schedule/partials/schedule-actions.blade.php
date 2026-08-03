@props(['schedule', 'iconOnly' => false])

<div x-data="{ open: false }" class="relative inline-block shrink-0 text-left">
    <button type="button" class="sabira-icon-button h-11 w-11" @click="open = !open" @keydown.escape.window="open = false" aria-label="Aksi jadwal {{ $schedule->subject->nama_mapel }}" :aria-expanded="open" aria-haspopup="menu">
        <i class="fas fa-ellipsis" aria-hidden="true"></i>
    </button>
    <div x-show="open" x-cloak x-transition @click.outside="open = false" class="sabira-user-menu top-full right-0 min-w-44" role="menu">
        @can('submitAttendance', $schedule)
            <a href="{{ route('guru.schedule.absen', $schedule) }}" class="sabira-user-menu-item" role="menuitem"><i class="fas fa-play"></i> Mulai Sesi</a>
        @endcan
        <a href="{{ route('guru.schedule.edit', $schedule) }}" class="sabira-user-menu-item" role="menuitem"><i class="far fa-pen-to-square"></i> Edit Jadwal</a>
        <form action="{{ route('guru.schedule.destroy', $schedule) }}" method="POST" onsubmit="return confirm('Hapus jadwal {{ addslashes($schedule->subject->nama_mapel) }}? Tindakan ini tidak dapat dibatalkan.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="sabira-user-menu-item w-full text-[var(--sabira-danger)]" role="menuitem"><i class="far fa-trash-can"></i> Hapus Jadwal</button>
        </form>
    </div>
</div>
