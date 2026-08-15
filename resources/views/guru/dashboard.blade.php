<x-app-shell headerTitle="Dashboard Operasional Guru" headerSubtitle="Jadwal & Presensi Mengajar Hari Ini">
    <div class="space-y-6">

        <!-- Welcome & User Identity Card -->
        <div class="relative overflow-hidden rounded-[var(--radius-md)] bg-[var(--sabira-surface)] p-6 border border-[var(--sabira-border-soft)]">
            <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="flex items-center gap-5">
                    <img src="{{ asset('storage/' . Auth::user()->karyawan?->foto) }}" onerror="this.onerror=null; this.src='{{ asset('images/default-photo.jpg') }}'" alt="Foto Profil" class="h-16 w-16 object-cover rounded-2xl ring-4 ring-indigo-500/30 shadow-md">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-xl font-semibold tracking-tight text-[var(--sabira-ink)]">Selamat Datang, {{ Auth::user()->name }}</h2>
                            <x-status-badge status="aktif" size="sm" />
                        </div>
                        <p class="mt-1 text-xs text-[var(--sabira-muted)]">
                            <i class="far fa-calendar-alt mr-1"></i> {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }} — <i class="far fa-clock ml-2 mr-1"></i> Pukul {{ \Carbon\Carbon::now()->format('H:i') }} WIB
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 w-full md:w-auto">
                    <a href="{{ route('guru.schedule') }}" class="flex-1 md:flex-initial inline-flex items-center justify-center gap-2 rounded-xl bg-[var(--sabira-primary)] px-5 py-3 text-xs font-bold text-white  hover:bg-[var(--sabira-primary-active)] transition">
                        <i class="fas fa-calendar-week"></i> <span>Lihat Jadwal Lengkap</span>
                    </a>
                </div>
            </div>
        </div>

        <x-work-attendance-card
            :lokasi="$lokasiKehadiran"
            :attendance="$kehadiranKerjaHariIni"
            instance="guru-dashboard"
        />

        <!-- 5.3 Bagian 1: Jadwal Berikutnya (Hero Card) -->
        <div class="rounded-[var(--radius-md)] border border-[var(--sabira-border-soft)] bg-[var(--sabira-surface)] p-6">
            <div class="flex items-center justify-between border-b border-indigo-100 dark:border-slate-800 pb-4 mb-4">
                <div class="flex items-center gap-2">
                    <span class="flex h-3 w-3 relative">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-3 w-3 bg-[var(--sabira-primary)]"></span>
                    </span>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-indigo-950 dark:text-indigo-300">Jadwal Pembelajaran Berikutnya</h3>
                </div>
                <span class="px-2.5 py-1 rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300 text-[11px] font-bold">
                    Hari Ini
                </span>
            </div>

            @if($todaySessions->count() > 0)
                @php $firstSchedule = $todaySessions->first(); @endphp
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                    <div class="md:col-span-2 space-y-3">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-md bg-[var(--sabira-primary)] text-white text-[11px] font-bold uppercase">
                                {{ $firstSchedule->classGroup->educationProgram->name ?? 'FORMAL' }}
                            </span>
                            <span class="text-xs font-semibold text-slate-500">Kelas {{ $firstSchedule->classGroup->name ?? $firstSchedule->classGroup->nama_kelas }}</span>
                        </div>
                        <h2 class="text-xl font-extrabold text-slate-900 dark:text-white">{{ $firstSchedule->subject->nama_mapel ?? $firstSchedule->subject->name }}</h2>
                        <div class="flex flex-wrap items-center gap-4 text-xs font-medium text-slate-600 dark:text-slate-400">
                            <span><i class="far fa-clock text-indigo-500 mr-1"></i> Pertemuan Ke-{{ $firstSchedule->meeting_no }}</span>
                            <span><i class="fas fa-map-marker-alt text-rose-500 mr-1"></i> Area Kampus</span>
                            <span><i class="fas fa-users text-emerald-500 mr-1"></i> Terdaftar di Kelas</span>
                        </div>
                    </div>
                    <div class="flex flex-col items-stretch md:items-end justify-center">
                        <a href="{{ route('guru.schedule.absen', $firstSchedule->schedule_id ?? $firstSchedule->class_group_id) }}" class="sabira-button sabira-button-primary px-6">
                            <i class="fas fa-play text-xs"></i> <span>Mulai Sesi Pembelajaran</span>
                        </a>
                        <p class="mt-2 text-[11px] text-slate-400 text-center md:text-right">Buka Wizard Jurnal & Presensi Siswa</p>
                    </div>
                </div>
            @elseif($rutinSchedules->count() > 0)
                @php $firstRutin = $rutinSchedules->first(); @endphp
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                    <div class="md:col-span-2 space-y-3">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-md bg-[var(--sabira-primary)] text-white text-[11px] font-bold uppercase">
                                {{ $firstRutin->classGroup->educationProgram->name ?? 'FORMAL' }}
                            </span>
                            <span class="text-xs font-semibold text-slate-500">Kelas {{ $firstRutin->classGroup->name ?? $firstRutin->classGroup->nama_kelas }}</span>
                        </div>
                        <h2 class="text-xl font-extrabold text-slate-900 dark:text-white">{{ $firstRutin->subject->nama_mapel ?? $firstRutin->subject->name }}</h2>
                        <div class="flex flex-wrap items-center gap-4 text-xs font-medium text-slate-600 dark:text-slate-400">
                            <span><i class="far fa-clock text-indigo-500 mr-1"></i> Jam {{ substr($firstRutin->jam_mulai, 0, 5) }} - {{ substr($firstRutin->jam_selesai, 0, 5) }} WIB</span>
                            <span><i class="fas fa-calendar-day text-emerald-500 mr-1"></i> Terjadwal Hari Ini</span>
                        </div>
                    </div>
                    <div class="flex flex-col items-stretch md:items-end justify-center">
                        <a href="{{ route('guru.schedule.absen', $firstRutin->id) }}" class="sabira-button sabira-button-primary px-6">
                            <i class="fas fa-play text-xs"></i> <span>Mulai Sesi Pembelajaran</span>
                        </a>
                        <p class="mt-2 text-[11px] text-slate-400 text-center md:text-right">Buka Wizard Jurnal & Presensi Siswa</p>
                    </div>
                </div>
            @else
                <x-empty-state title="Belum Ada Sesi Aktif Saat Ini" description="Anda tidak memiliki jadwal pembelajaran yang perlu dilaksanakan hari ini." icon="fas fa-calendar-day" />
            @endif
        </div>

        <!-- KPI Grid Summary -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stat-card title="Jadwal Hari Ini" value="{{ $todaySessions->count() ?: $rutinSchedules->count() }}" subtitle="Sesi Terjadwal" icon="fas fa-calendar-day" color="indigo" />
            <x-stat-card title="Sesi Selesai" value="{{ $completedSessionsCount }}" subtitle="Jurnal & Absensi Terisi" icon="fas fa-check-double" color="emerald" />
            <x-stat-card title="Sesi Pending / Draft" value="{{ $pendingSessionsCount }}" subtitle="Perlu Diselesaikan" icon="fas fa-hourglass-half" color="amber" />
            <x-stat-card title="Progres Bulan Ini" value="{{ $completedThisMonth->count() }}" subtitle="Sesi Selesai" icon="fas fa-list-check" color="blue" />
            <x-stat-card title="Kepatuhan Geofence" value="{{ $geofenceComplianceRate === null ? '-' : $geofenceComplianceRate.'%' }}" subtitle="Berdasarkan sesi bulan ini" icon="fas fa-shield-alt" color="blue" />
        </div>

        <!-- 5.3 Bagian 2: Aktivitas Hari Ini (Timeline Sesi) -->
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4 border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Aktivitas Mengajar Hari Ini</h3>
                <span class="text-xs text-slate-500">{{ \Carbon\Carbon::now()->format('d/m/Y') }}</span>
            </div>

            @if($todaySessions->count() > 0)
                <div class="space-y-3">
                    @foreach($todaySessions as $session)
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 hover:bg-slate-100/60 dark:hover:bg-slate-800/80 transition gap-4">
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 font-bold text-xs">
                                    P-{{ $session->meeting_no }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-sm font-bold text-slate-900 dark:text-white">{{ $session->subject->nama_mapel ?? $session->subject->name }}</h4>
                                        <x-status-badge :status="$session->status ?? 'draft'" size="sm" />
                                    </div>
                                    <p class="text-xs text-slate-500 mt-0.5">Kelas {{ $session->classGroup->name ?? $session->classGroup->nama_kelas }} • {{ $session->classGroup->educationProgram->name ?? 'Formal' }}</p>
                                </div>
                            </div>

                            <a href="{{ route('guru.schedule.absen', $session->schedule_id ?? $session->class_group_id) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 px-3.5 py-2 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900 transition">
                                <span>Input / Edit Sesi</span> <i class="fas fa-chevron-right text-[10px]"></i>
                            </a>
                        </div>
                    @endforeach
                </div>
            @elseif($rutinSchedules->count() > 0)
                <div class="space-y-3">
                    @foreach($rutinSchedules as $scheduleItem)
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 hover:bg-slate-100/60 dark:hover:bg-slate-800/80 transition gap-4">
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 font-bold text-xs">
                                    <i class="far fa-clock"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-sm font-bold text-slate-900 dark:text-white">{{ $scheduleItem->subject->nama_mapel ?? $scheduleItem->subject->name }}</h4>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">Terjadwal</span>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-0.5">Kelas {{ $scheduleItem->classGroup->name ?? $scheduleItem->classGroup->nama_kelas }} • Jam {{ substr($scheduleItem->jam_mulai, 0, 5) }} - {{ substr($scheduleItem->jam_selesai, 0, 5) }}</p>
                                </div>
                            </div>

                            <a href="{{ route('guru.schedule.absen', $scheduleItem->id) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-[var(--sabira-primary)] px-3.5 py-2 text-xs font-bold text-white hover:bg-[var(--sabira-primary-active)] transition">
                                <span>Mulai Sesi</span> <i class="fas fa-play text-[10px]"></i>
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <x-empty-state title="Tidak Ada Aktivitas" description="Tidak ada aktivitas mengajar yang tercatat hari ini." icon="fas fa-clock" />
            @endif
        </div>
    </div>
</x-app-shell>
