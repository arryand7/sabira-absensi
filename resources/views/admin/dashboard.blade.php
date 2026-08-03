<x-app-shell headerTitle="Dashboard Operasional Manajemen & Executive Overview" headerSubtitle="Monitoring Presensi Siswa, Kepatuhan Guru, & Status Sistem">
    <div class="space-y-6">

        <!-- 5.1 Baris 1: Executive KPI Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stat-card title="Jadwal Hari Ini" value="{{ $todaySchedules->count() }}" subtitle="{{ $todayName }} · Tahun ajaran aktif" icon="fas fa-calendar-day" color="indigo" link="{{ route('admin.schedules.index', ['tahun_ajaran' => $activeYear?->id, 'hari' => $todayName]) }}" linkText="Buka Jadwal" />
            <x-stat-card title="Sesi Selesai Hari Ini" value="{{ $completedSessionsTodayCount }}" subtitle="Dari {{ $todaySchedules->count() }} jadwal" icon="fas fa-check-double" color="emerald" link="{{ route('laporan.pertemuan', ['start_date' => now()->toDateString(), 'end_date' => now()->toDateString()]) }}" linkText="Lihat Sesi" />
            <x-stat-card title="Belum Dilaporkan" value="{{ $unreportedSessionsCount }}" subtitle="Jadwal tanpa sesi selesai" icon="fas fa-book-open" color="amber" link="{{ route('admin.schedules.index', ['tahun_ajaran' => $activeYear?->id, 'hari' => $todayName]) }}" linkText="Periksa Jadwal" />
            <x-stat-card title="Koreksi Menunggu" value="{{ $pendingCorrectionsCount }}" subtitle="Memerlukan keputusan admin" icon="fas fa-pen-to-square" color="rose" link="{{ route('admin.attendance-corrections.index', ['status' => 'pending']) }}" linkText="Tinjau Koreksi" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stat-card title="Kehadiran Siswa" value="{{ number_format($studentAttendanceRate, 1) }}%" subtitle="Rekap status hari ini" icon="fas fa-user-graduate" color="blue" link="{{ route('laporan.murid') }}" linkText="Lihat Laporan" />
            <x-stat-card title="Konflik Jadwal" value="{{ $scheduleConflictsCount }}" subtitle="Memerlukan verifikasi admin" icon="fas fa-calendar-xmark" color="rose" link="{{ route('admin.schedule-conflicts.index') }}" linkText="Tinjau Konflik" />
            <x-stat-card title="Anomali Geofence" value="{{ $outsideGeofenceTodayCount }}" subtitle="Sesi di luar radius hari ini" icon="fas fa-location-crosshairs" color="amber" link="{{ route('laporan.pertemuan', ['start_date' => now()->toDateString(), 'end_date' => now()->toDateString()]) }}" linkText="Lihat Detail" />
            <x-stat-card title="Hadir Kerja" value="{{ $totalSudahAbsen }}/{{ $totalKaryawan }}" subtitle="Check-in pegawai hari ini" icon="fas fa-user-check" color="emerald" link="{{ route('laporan.karyawan', ['bulan' => now()->month, 'tahun' => now()->year]) }}" linkText="Lihat Presensi" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-stat-card title="Siswa Berisiko Tinggi" value="{{ $atRiskStudentsCount }}" subtitle="Total Alpa >= 3 Hari" icon="fas fa-user-shield" color="rose" link="{{ route('laporan.murid') }}" linkText="Lihat Laporan Siswa" />
            <x-stat-card title="Anomali Mengajar Guru" value="{{ $teachingAnomaliesCount }}" subtitle="Kepatuhan Lokasi & Sesi" icon="fas fa-exclamation-triangle" color="amber" link="{{ route('laporan.pertemuan') }}" linkText="Lihat Rekap Pertemuan" />
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                <div><h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white">Jadwal Operasional Hari Ini</h3><p class="text-xs text-slate-500">Data langsung dari jadwal tahun ajaran aktif.</p></div>
                <a href="{{ route('admin.schedules.index', ['tahun_ajaran' => $activeYear?->id, 'hari' => $todayName]) }}" class="text-xs font-semibold text-indigo-600">Kelola Jadwal →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead class="bg-slate-50 text-left uppercase text-slate-500 dark:bg-slate-800"><tr><th class="px-3 py-2">Jam</th><th class="px-3 py-2">Guru</th><th class="px-3 py-2">Mata Pelajaran</th><th class="px-3 py-2">Kelas</th><th class="px-3 py-2">Status Sesi</th></tr></thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($todaySchedules as $schedule)
                            @php($todaySession = $sessionsTodayBySchedule->get($schedule->id))
                            <tr><td class="px-3 py-2 font-mono">{{ substr($schedule->jam_mulai, 0, 5) }}–{{ substr($schedule->jam_selesai, 0, 5) }}</td><td class="px-3 py-2">{{ $schedule->user->name }}</td><td class="px-3 py-2">{{ $schedule->subject->nama_mapel }}</td><td class="px-3 py-2">{{ $schedule->classGroup->nama_kelas }}</td><td class="px-3 py-2 capitalize">{{ $todaySession?->status ?? 'belum dimulai' }}</td></tr>
                        @empty
                            <tr><td colspan="5" class="px-3 py-8 text-center text-slate-500">Tidak ada jadwal pada hari ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Executive Monitoring Grid: Siswa Berisiko & Anomali Guru -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Panel Siswa Berisiko (Alpa >= 3) -->
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm flex flex-col">
                <div class="flex items-center justify-between mb-4 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-exclamation-circle text-rose-500"></i>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Pemantauan Siswa Berisiko</h3>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 text-[11px] font-bold">
                        {{ $atRiskStudentsCount }} Siswa
                    </span>
                </div>

                <div class="overflow-y-auto max-h-64 space-y-2 flex-1">
                    @forelse(array_slice($atRiskStudents, 0, 5) as $studentRisk)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 text-xs">
                            <div>
                                <a href="{{ route('laporan.murid.show', $studentRisk['student']) }}" class="font-bold text-slate-900 hover:text-indigo-600 dark:text-white">{{ $studentRisk['student']->nama_lengkap ?? $studentRisk['student']->nama ?? 'Siswa' }}</a>
                                <p class="text-[10px] text-slate-400">NISN: {{ $studentRisk['student']->nisn ?? '-' }} • Kelas: {{ $studentRisk['student']->classGroups->first()->name ?? $studentRisk['student']->classGroups->first()->nama_kelas ?? '-' }}</p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 font-bold text-[10px]">
                                    Alpa: {{ $studentRisk['alpa'] ?? $studentRisk['total_alpa'] ?? 0 }} Kali
                                </span>
                            </div>
                        </div>
                    @empty
                        <x-empty-state title="Tidak Ada Siswa Berisiko" description="Semua siswa memiliki tingkat kehadiran yang memenuhi standar." icon="fas fa-check-circle" />
                    @endforelse
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 text-right">
                    <a href="{{ route('laporan.murid') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">
                        Lihat Seluruh Laporan Siswa <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>

            <!-- Panel Anomali Mengajar Guru -->
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm flex flex-col">
                <div class="flex items-center justify-between mb-4 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-user-clock text-amber-500"></i>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Anomali Mengajar Guru</h3>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 text-[11px] font-bold">
                        {{ $teachingAnomaliesCount }} Anomali
                    </span>
                </div>

                <div class="overflow-y-auto max-h-64 space-y-2 flex-1">
                    @forelse(array_slice($teachingAnomalies, 0, 5) as $anomaly)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 text-xs">
                            <div>
                                <a href="{{ route('laporan.pertemuan.teacher', $anomaly['teacher']) }}" class="font-bold text-slate-900 hover:text-indigo-600 dark:text-white">{{ $anomaly['teacher']->name ?? $anomaly['teacher_name'] ?? 'Guru' }}</a>
                                <p class="text-[10px] text-slate-400">Terjadwal: {{ $anomaly['total_scheduled'] ?? 0 }} Sesi • Terlaksana: {{ $anomaly['total_taught'] ?? 0 }} Sesi</p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 font-bold text-[10px]">
                                    {{ $anomaly['anomaly_reasons'][0] ?? $anomaly['reason'] ?? 'Persentase mengajar perlu perhatian' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <x-empty-state title="Tidak Ada Anomali Mengajar" description="Seluruh guru melaksanakan sesi pembelajaran sesuai lokasi dan radius geofence." icon="fas fa-check-circle" />
                    @endforelse
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 text-right">
                    <a href="{{ route('laporan.pertemuan') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">
                        Lihat Seluruh Laporan Mengajar <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- 5.1 Baris 2: Operasional Hari Ini & Presensi Kerja -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Tabel Absensi Kerja Hari Ini -->
            <div class="lg:col-span-2 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Kehadiran Kerja Hari Ini</h3>
                        <p class="text-xs text-slate-500">Log Check-in & Check-out Karyawan/Guru</p>
                    </div>
                    <span class="text-xs text-slate-400"><i class="far fa-calendar-alt mr-1"></i> {{ now()->format('d/m/Y') }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs text-left">
                        <thead class="bg-slate-50 dark:bg-slate-800 text-slate-500 font-bold uppercase border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="px-4 py-3">Nama Pegawai</th>
                                <th class="px-4 py-3">Waktu</th>
                                <th class="px-4 py-3">Check In</th>
                                <th class="px-4 py-3">Check Out</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse ($absensis as $absen)
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
                                    <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">{{ $absen->user->name ?? 'User' }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ \Carbon\Carbon::parse($absen->waktu_absen)->format('d M Y') }}</td>
                                    <td class="px-4 py-3 font-mono text-emerald-600 dark:text-emerald-400 font-bold">{{ $absen->check_in ?? '-' }}</td>
                                    <td class="px-4 py-3 font-mono text-slate-500">{{ $absen->check_out ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        <x-status-badge :status="$absen->status ?? 'hadir'" size="sm" />
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <button onclick="document.getElementById('modal-edit-{{ $absen->id }}').classList.remove('hidden')" class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-700">
                                            <i class="fas fa-edit text-[10px]"></i> Edit
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-slate-400">Belum ada catatan absensi kerja hari ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tabel Karyawan Belum Absen -->
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm flex flex-col">
                <div class="flex items-center justify-between mb-4 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Belum Presensi Kerja</h3>
                    <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-950 text-[11px] font-bold">
                        {{ count($karyawanBelumAbsen) }} Karyawan
                    </span>
                </div>

                <div class="overflow-y-auto max-h-80 flex-1 space-y-2">
                    @forelse ($karyawanBelumAbsen as $karyawan)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                            <div>
                                <p class="text-xs font-bold text-slate-900 dark:text-white">{{ $karyawan->user->name ?? 'Pegawai' }}</p>
                                <p class="text-[10px] text-slate-400">ID: {{ $karyawan->id }}</p>
                            </div>
                            <form action="{{ route('admin.absensi.manual.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $karyawan->user_id }}">
                                <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-2.5 py-1 text-[11px] font-bold text-white hover:bg-emerald-500 shadow-sm transition">
                                    <i class="fas fa-check"></i> Absenkan
                                </button>
                            </form>
                        </div>
                    @empty
                        <x-empty-state title="Sudah Absen Semua" description="Seluruh karyawan terdaftar telah melakukan check-in hari ini." icon="fas fa-user-check" />
                    @endforelse
                </div>
            </div>
        </div>

        @if(auth()->user()->isSuperAdmin())
        <!-- 5.1 Baris 3: Status Gate SSO & Operational Alert -->
        <div class="rounded-[var(--radius-md)] border border-[var(--sabira-border-soft)] bg-[var(--sabira-surface)] p-6 text-[var(--sabira-ink)]">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[var(--sabira-primary)]/30 text-indigo-400 border border-indigo-500/30">
                        <i class="fas fa-sync-alt text-xl"></i>
                    </div>
                    <div>
                        <h4 class="text-base font-bold text-white">Gate SSO Identity Provisioning Platform</h4>
                        <p class="text-xs text-indigo-200/80 mt-0.5">8-Category Reconciliation Engine & Pull-Based User Synchronization
                        @if($lastGateSync)
                            • Sinkronisasi Terakhir: {{ $lastGateSync->created_at->diffForHumans() }} (Status: {{ strtoupper($lastGateSync->status ?? 'COMPLETED') }})
                        @endif
                        </p>
                    </div>
                </div>

                <a href="{{ route('admin.sync.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-[var(--sabira-primary)] px-5 py-2.5 text-xs font-bold text-white  hover:bg-[var(--sabira-primary-active)] transition">
                    <i class="fas fa-play text-xs"></i> <span>Mulai Dry-Run Sinkronisasi</span>
                </a>
            </div>
        </div>
        @endif

        <!-- Modals Edit Absensi -->
        @foreach ($absensis as $absen)
            <div id="modal-edit-{{ $absen->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
                <div class="w-full max-w-md rounded-2xl bg-white dark:bg-slate-900 p-6 shadow-2xl border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white">Koreksi Data Absensi Karyawan</h4>
                        <button onclick="document.getElementById('modal-edit-{{ $absen->id }}').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form action="{{ route('admin.absensi.update', $absen->id) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Nama Pegawai</label>
                            <input type="text" value="{{ $absen->user->name }}" class="w-full rounded-xl border border-slate-200 bg-slate-100 p-2.5 text-xs font-bold" readonly>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Check In</label>
                            <input type="time" name="check_in" value="{{ old('check_in', $absen->check_in) }}" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2.5 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Check Out</label>
                            <input type="time" name="check_out" value="{{ old('check_out', $absen->check_out) }}" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2.5 text-xs">
                        </div>
                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" onclick="document.getElementById('modal-edit-{{ $absen->id }}').classList.add('hidden')" class="rounded-lg border border-slate-300 px-4 py-2 text-xs font-semibold">Batal</button>
                            <button type="submit" class="rounded-lg bg-[var(--sabira-primary)] text-white px-4 py-2 text-xs font-bold hover:bg-[var(--sabira-primary-active)]">Simpan Koreksi</button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach

    </div>
</x-app-shell>
