<x-app-shell header-title="Jadwal Mengajar" header-subtitle="Jadwal mingguan dan sesi pembelajaran Anda">
    @php
        $todayName = now()->locale('id')->isoFormat('dddd');
        $schedulesByDay = $schedules->groupBy('hari');
    @endphp

    <div class="space-y-6" x-data="{
        mode: localStorage.getItem('sabira-schedule-view') || 'weekly',
        selectedDay: @js(in_array($todayName, $days, true) ? $todayName : ($days[0] ?? 'Senin')),
        setMode(value) { this.mode = value; localStorage.setItem('sabira-schedule-view', value); }
    }">
        <section class="sabira-card">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-xl font-semibold text-[var(--sabira-ink)]">Jadwal {{ $academicYears->firstWhere('id', $selectedYear)?->name ?? '-' }}</h2>
                        <x-status-badge :status="ucfirst($selectedSemester)" size="sm" />
                    </div>
                    <p class="mt-1 text-sm text-[var(--sabira-muted)]">{{ $schedules->count() }} jadwal aktif · {{ $guru->name }}</p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="inline-flex rounded-full border border-[var(--sabira-border)] bg-[var(--sabira-surface-soft)] p-1" role="group" aria-label="Mode tampilan jadwal">
                        <button type="button" class="sabira-button min-h-11 flex-1 rounded-full px-4" :class="mode === 'weekly' ? 'bg-[var(--sabira-surface)] text-[var(--sabira-ink)]' : 'text-[var(--sabira-muted)]'" @click="setMode('weekly')" :aria-pressed="mode === 'weekly'"><i class="far fa-calendar" aria-hidden="true"></i> Mingguan</button>
                        <button type="button" class="sabira-button min-h-11 flex-1 rounded-full px-4" :class="mode === 'list' ? 'bg-[var(--sabira-surface)] text-[var(--sabira-ink)]' : 'text-[var(--sabira-muted)]'" @click="setMode('list')" :aria-pressed="mode === 'list'"><i class="fas fa-list" aria-hidden="true"></i> Daftar</button>
                    </div>
                    <x-button :href="route('guru.schedule.create', ['guru_id' => $guru->id])"><i class="fas fa-plus" aria-hidden="true"></i> Tambah Jadwal</x-button>
                </div>
            </div>
        </section>

        <form method="GET" class="sabira-filter-bar grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
            <x-form-field label="Guru" name="teacher_display"><input id="teacher_display" class="sabira-input" value="{{ $guru->name }}" readonly aria-readonly="true"></x-form-field>
            <x-form-field label="Program Pendidikan" name="program_id">
                <x-select name="program_id"><option value="">Semua program</option>@foreach($educationPrograms as $program)<option value="{{ $program->id }}" @selected((string) request('program_id') === (string) $program->id)>{{ $program->name }}</option>@endforeach</x-select>
            </x-form-field>
            <x-form-field label="Kelas" name="class_group_id">
                <x-select name="class_group_id"><option value="">Semua kelas</option>@foreach($classGroups as $classGroup)<option value="{{ $classGroup->id }}" @selected((string) request('class_group_id') === (string) $classGroup->id)>{{ $classGroup->nama_kelas }}</option>@endforeach</x-select>
            </x-form-field>
            <x-form-field label="Tahun Ajaran" name="academic_year_id">
                <x-select name="academic_year_id">@foreach($academicYears as $year)<option value="{{ $year->id }}" @selected((string) $selectedYear === (string) $year->id)>{{ $year->name }}</option>@endforeach</x-select>
            </x-form-field>
            <x-form-field label="Semester" name="semester"><x-select name="semester"><option value="ganjil" @selected($selectedSemester === 'ganjil')>Ganjil</option><option value="genap" @selected($selectedSemester === 'genap')>Genap</option></x-select></x-form-field>
            <div class="flex items-end gap-2"><x-button type="submit" class="flex-1"><i class="fas fa-filter"></i> Terapkan</x-button><x-button variant="secondary" :href="route('guru.schedule')" aria-label="Reset filter"><i class="fas fa-rotate-left"></i></x-button></div>
        </form>

        <section x-show="mode === 'weekly'" class="space-y-4">
            <div class="lg:hidden -mx-4 overflow-x-auto px-4 pb-1" aria-label="Pilih hari">
                <div class="flex min-w-max gap-2">
                    @foreach($days as $day)
                        <button type="button" class="min-h-11 rounded-full border px-4 text-sm font-medium" :class="selectedDay === '{{ $day }}' ? 'border-[var(--sabira-primary)] bg-[var(--sabira-primary)] text-white' : 'border-[var(--sabira-border)] bg-[var(--sabira-surface)] text-[var(--sabira-body)]'" @click="selectedDay = '{{ $day }}">
                            {{ $day }} <span class="ml-1 text-xs opacity-75">{{ collect($schedulesByDay->get($day, []))->count() }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="lg:hidden">
                @foreach($days as $day)
                    <div x-show="selectedDay === '{{ $day }}'" class="space-y-3">
                        <div class="flex items-center justify-between"><h2 class="text-lg font-semibold text-[var(--sabira-ink)]">{{ $day }}</h2>@if($day === $todayName)<x-status-badge status="Hari ini" size="sm" />@endif</div>
                        @forelse($schedulesByDay->get($day, collect())->unique('id')->sortBy('jam_mulai') as $schedule)
                            @include('guru.schedule.partials.schedule-card', ['schedule' => $schedule])
                        @empty
                            <x-empty-state title="Tidak ada jadwal" description="Belum ada jadwal mengajar pada hari ini." icon="far fa-calendar">
                                <x-button class="mt-4" :href="route('guru.schedule.create', ['guru_id' => $guru->id, 'hari' => $day])">Tambah Jadwal</x-button>
                            </x-empty-state>
                        @endforelse
                    </div>
                @endforeach
            </div>

            <div class="hidden space-y-5 lg:block">
                @forelse($programGrids as $grid)
                    <div class="overflow-hidden rounded-[var(--radius-md)] border border-[var(--sabira-border)] bg-[var(--sabira-surface)]">
                        <div class="flex items-center justify-between border-b border-[var(--sabira-border)] px-4 py-3">
                            <div>
                                <h2 class="text-base font-semibold text-[var(--sabira-ink)]">{{ $grid['program']->name }}</h2>
                                <p class="text-xs text-[var(--sabira-muted)]">Kebijakan jam {{ substr($grid['program']->default_start_time, 0, 5) }}–{{ substr($grid['program']->default_end_time, 0, 5) }}</p>
                            </div>
                            <span class="rounded-full bg-[var(--sabira-surface-soft)] px-3 py-1 text-xs font-medium text-[var(--sabira-muted)]">{{ $grid['rows']->where('is_break', false)->count() }} slot</span>
                        </div>
                        @if($grid['rows']->isEmpty())
                            <div class="p-8 text-center text-sm text-[var(--sabira-muted)]">Kebijakan jam program ini belum diatur oleh admin.</div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[1120px] table-fixed border-collapse" aria-label="Jadwal mingguan berdasarkan jam pelajaran {{ $grid['program']->name }}">
                                    <thead>
                                        <tr class="bg-[var(--sabira-surface-soft)]">
                                            <th class="w-24 border-b border-r border-[var(--sabira-border)] px-3 py-4 text-center text-xs font-semibold text-[var(--sabira-muted)]">JAM</th>
                                            @foreach($days as $day)
                                                <th class="border-b border-r border-[var(--sabira-border)] px-3 py-4 text-center text-sm font-semibold text-[var(--sabira-ink)] last:border-r-0">{{ $day }} @if($day === $todayName)<span class="ml-1 inline-block h-2 w-2 rounded-full bg-[var(--sabira-primary)]" aria-label="Hari ini"></span>@endif</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($grid['rows'] as $slot)
                                            @if($slot->is_break)
                                                <tr>
                                                    <th class="border-b border-r border-[var(--sabira-border-soft)] bg-[var(--sabira-surface-soft)] px-2 py-2 text-[11px] text-[var(--sabira-muted)]">{{ $slot->label ?: 'ISTIRAHAT' }}</th>
                                                    <td colspan="6" class="border-b border-[var(--sabira-border-soft)] bg-[var(--sabira-surface-soft)] px-4 py-2 text-center text-xs font-medium text-[var(--sabira-muted)]">{{ substr($slot->start_time, 0, 5) }}–{{ substr($slot->end_time, 0, 5) }} · {{ $slot->label ?: 'Istirahat' }}</td>
                                                </tr>
                                            @else
                                                <tr>
                                                    <th scope="row" class="border-b border-r border-[var(--sabira-border-soft)] bg-[var(--sabira-surface-soft)] px-2 py-3 text-center align-top">
                                                        <strong class="block text-xs text-[var(--sabira-ink)]">{{ $slot->label ?: 'Jam '.$slot->slot_number }}</strong>
                                                        <span class="mt-1 block whitespace-nowrap text-[10px] font-normal text-[var(--sabira-muted)]">{{ substr($slot->start_time, 0, 5) }}–{{ substr($slot->end_time, 0, 5) }}</span>
                                                    </th>
                                                    @foreach($days as $day)
                                                        @php($bucket = collect(data_get($grid['buckets'], $day.'.'.$slot->id, [])))
                                                        <td class="h-28 border-b border-r border-[var(--sabira-border-soft)] p-1.5 align-top last:border-r-0 {{ $day === 'Jumat' && ! $slot->friday_enabled ? 'bg-[var(--sabira-surface-soft)]' : '' }}">
                                                            @if($day === 'Jumat' && ! $slot->friday_enabled)
                                                                <span class="sr-only">Slot tidak berlaku pada Jumat</span>
                                                            @elseif($bucket->isNotEmpty())
                                                                <div class="space-y-1.5">@foreach($bucket as $schedule)@include('guru.schedule.partials.schedule-slot-card', ['schedule' => $schedule])@endforeach</div>
                                                            @else
                                                                <a href="{{ route('guru.schedule.create', ['guru_id' => $guru->id, 'program_id' => $grid['program']->id, 'hari' => $day, 'jam_ke' => $slot->slot_number, 'jam_mulai' => substr($slot->start_time, 0, 5), 'jam_selesai' => substr($slot->end_time, 0, 5)]) }}" class="group flex h-full min-h-24 items-center justify-center rounded-[var(--radius-sm)] border border-dashed border-transparent text-[var(--sabira-muted-soft)] transition hover:border-[var(--sabira-border)] hover:bg-[var(--sabira-surface-soft)] hover:text-[var(--sabira-primary)]" aria-label="Tambah jadwal {{ $day }} jam {{ $slot->slot_number }}"><span class="flex h-8 w-8 items-center justify-center rounded-full bg-[var(--sabira-surface-strong)] text-xs group-hover:bg-[var(--sabira-surface)]"><i class="fas fa-plus" aria-hidden="true"></i></span></a>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    @if($grid['outside_schedules']->isNotEmpty())
                        <x-alert type="warning" title="{{ $grid['outside_schedules']->count() }} jadwal di luar kebijakan {{ $grid['program']->name }}">
                            Jadwal lama tetap tersedia pada mode Daftar. Admin perlu menyesuaikan waktunya atau memperbarui kebijakan slot.
                        </x-alert>
                    @endif
                @empty
                    <x-empty-state title="Kebijakan jam belum tersedia" description="Admin perlu mengatur slot jam untuk Program Pendidikan." icon="far fa-clock" />
                @endforelse
            </div>
        </section>

        <section x-show="mode === 'list'" class="sabira-card overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="sabira-data-table min-w-[820px]">
                    <thead><tr><th>Hari dan waktu</th><th>Mata pelajaran</th><th>Kelas / program</th><th>Semester</th><th>Status</th><th class="text-right"><span class="sr-only">Aksi</span></th></tr></thead>
                    <tbody>
                        @forelse($schedules as $schedule)
                            @php($session = $schedule->sessions->first())
                            <tr>
                                <td><strong class="block text-[var(--sabira-ink)]">{{ $schedule->hari }}</strong><span class="text-xs text-[var(--sabira-muted)]">{{ substr($schedule->jam_mulai, 0, 5) }}–{{ substr($schedule->jam_selesai, 0, 5) }}</span></td>
                                <td><strong class="font-semibold text-[var(--sabira-ink)]">{{ $schedule->subject->nama_mapel }}</strong><span class="block text-xs text-[var(--sabira-muted)]">{{ $schedule->subject->kode_mapel }}</span></td>
                                <td>{{ $schedule->classGroup->nama_kelas }}<span class="block text-xs text-[var(--sabira-muted)]">{{ $schedule->educationProgram?->name ?? $schedule->classGroup->educationProgram?->name ?? ucfirst($schedule->classGroup->jenis_kelas) }}</span></td>
                                <td>{{ ucfirst($schedule->semester) }}</td>
                                <td class="space-y-1">@if($schedule->has_pending_conflict)<x-status-badge status="Bentrok · Perlu Verifikasi" size="sm" />@else<x-status-badge :status="$session?->status ?? 'Terjadwal'" size="sm" />@endif</td>
                                <td class="text-right">@include('guru.schedule.partials.schedule-actions', ['schedule' => $schedule])</td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-empty-state title="Belum ada jadwal" description="Tambahkan jadwal mengajar pertama Anda." icon="far fa-calendar" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if(!empty($outsideSchedules))
            <section class="sabira-card"><h2 class="text-lg font-semibold text-[var(--sabira-ink)]">Jadwal di luar slot reguler</h2><p class="mt-1 text-sm text-[var(--sabira-muted)]">Jadwal berikut tetap aktif, tetapi waktunya tidak mengikuti slot pelajaran standar.</p><div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">@foreach($outsideSchedules as $items)@foreach($items as $schedule)@include('guru.schedule.partials.schedule-card', ['schedule' => $schedule])@endforeach @endforeach</div></section>
        @endif
    </div>
</x-app-shell>
