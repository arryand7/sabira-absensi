<x-app-shell headerTitle="Wizard Sesi Pembelajaran & Absensi" headerSubtitle="{{ $classGroup->name ?? $classGroup->nama_kelas }}">
    <div class="max-w-4xl mx-auto space-y-6" x-data="{
        step: {{ $errors->has('attendance') ? 3 : ($errors->any() ? 2 : 1) }},
        lat: '',
        lng: '',
        accuracy: '',
        geoStatus: 'Lokasi Belum Diambil',
        geoSuccess: false,
        studentFilter: '',
        validationMessage: '',
        submitting: false,

        counts: { hadir: 0, sakit: 0, izin: 0, alpa: 0, total: {{ $classGroup->students->count() }} },

        init() {
            this.updateCounts();
            this.getGeoLocation();
        },

        getGeoLocation() {
            if (navigator.geolocation) {
                this.geoStatus = 'Mendapatkan Koordinat GPS...';
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        this.lat = pos.coords.latitude;
                        this.lng = pos.coords.longitude;
                        this.accuracy = pos.coords.accuracy;
                        this.geoStatus = 'Di Dalam Area (Akurasi Tinggi)';
                        this.geoSuccess = true;
                    },
                    (err) => {
                        this.geoStatus = 'Lokasi Tidak Tersedia / Di Luar Radius';
                        this.geoSuccess = false;
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            }
        },

        markAllHadir() {
            document.querySelectorAll('input[type=radio][value=hadir]').forEach(r => {
                r.checked = true;
            });
            this.updateCounts();
        },

        updateCounts() {
            let h = 0, s = 0, i = 0, a = 0;
            document.querySelectorAll('.attendance-radio:checked').forEach(r => {
                if (r.value === 'hadir') h++;
                if (r.value === 'sakit') s++;
                if (r.value === 'izin') i++;
                if (r.value === 'alpa') a++;
            });
            this.counts.hadir = h;
            this.counts.sakit = s;
            this.counts.izin = i;
            this.counts.alpa = a;
        },

        showInvalidControl(control) {
            const panel = control.closest('[data-step]');
            this.step = panel ? Number(panel.dataset.step) : 1;
            this.validationMessage = control.validationMessage || 'Lengkapi data yang wajib diisi sebelum melanjutkan.';
            this.$nextTick(() => {
                control.focus();
                control.reportValidity();
                control.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        },

        nextStep(target) {
            this.validationMessage = '';
            const panel = document.querySelector(`[data-step='${this.step}']`);
            const invalid = panel
                ? [...panel.querySelectorAll('input, textarea, select')].find(control => !control.checkValidity())
                : null;

            if (invalid) {
                this.showInvalidControl(invalid);
                return;
            }

            this.step = target;
        },

        handleSubmit(event) {
            if (this.submitting) {
                event.preventDefault();
                return;
            }

            this.updateCounts();
            this.validationMessage = '';

            const invalid = [...event.currentTarget.elements].find(control => !control.checkValidity());
            if (invalid) {
                event.preventDefault();
                this.showInvalidControl(invalid);
                return;
            }

            const attendanceTotal = this.counts.hadir + this.counts.sakit + this.counts.izin + this.counts.alpa;
            if (attendanceTotal !== this.counts.total) {
                event.preventDefault();
                this.step = 3;
                this.validationMessage = 'Status kehadiran seluruh siswa wajib diisi sebelum sesi diselesaikan.';
                return;
            }

            this.submitting = true;
        }
    }">

        <!-- Stepper Header Component -->
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 sm:p-6 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">
                <div class="flex items-center gap-3">
                    <a href="{{ route('guru.schedule') }}" class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 transition">
                        <i class="fas fa-arrow-left text-sm"></i>
                    </a>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Alur Pembelajaran & Presensi</h2>
                        <p class="text-xs text-slate-500">Kelas {{ $classGroup->name ?? $classGroup->nama_kelas }} • {{ $schedule->subject->nama_mapel ?? $schedule->subject->name }}</p>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300 text-xs font-bold">
                    Langkah <span x-text="step"></span> dari 5
                </span>
            </div>

            <x-stepper current-step-expression="step" />
        </div>

        <!-- Form Submission -->
        <form id="teaching-session-form" action="{{ route('guru.schedule.absen.submit', $classGroup->id) }}" method="POST" data-draft-url="{{ route('guru.schedule.draft', $schedule) }}" novalidate @submit="handleSubmit($event)">
            @csrf
            <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
            <input type="hidden" name="mata_pelajaran" value="{{ $schedule->subject->nama_mapel ?? $schedule->subject->name }}">
            <input type="hidden" name="kode_mapel" value="{{ $schedule->subject->kode_mapel ?? $schedule->subject->code ?? 'MAPEL' }}">
            <input type="hidden" name="tanggal" value="{{ now()->format('Y-m-d') }}">
            <input type="hidden" name="jam_mulai" value="{{ $schedule->jam_mulai }}">
            <input type="hidden" name="jam_selesai" value="{{ $schedule->jam_selesai }}">
            <input type="hidden" name="latitude" :value="lat">
            <input type="hidden" name="longitude" :value="lng">
            <input type="hidden" name="location_accuracy" :value="accuracy">

            @if($errors->any())
                <div class="mb-6 rounded-xl border border-rose-200 dark:border-rose-800 bg-rose-50 dark:bg-rose-950/60 p-4 text-xs font-medium text-rose-800 dark:text-rose-300" role="alert">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-exclamation-circle mt-0.5"></i>
                        <div>
                            <p class="font-bold">Pertemuan belum dapat diselesaikan.</p>
                            <p class="mt-1">{{ $errors->first() }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div x-show="validationMessage" x-transition class="mb-6 rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-950/60 p-4 text-xs font-medium text-amber-800 dark:text-amber-300" role="alert" aria-live="assertive">
                <i class="fas fa-exclamation-triangle mr-1.5"></i>
                <span x-text="validationMessage"></span>
            </div>

            <!-- STEP 1: VALIDASI JADWAL -->
            <div x-show="step === 1" x-transition data-step="1" class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm space-y-6">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 dark:bg-indigo-950 text-indigo-600 font-bold">
                        1
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Step 1 — Validasi Informasi Jadwal</h3>
                        <p class="text-xs text-slate-500">Pastikan data mata pelajaran dan sesi kelas sudah sesuai sebelum memulai.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                        <label class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Mata Pelajaran</label>
                        <p class="text-base font-bold text-slate-900 dark:text-white mt-1">{{ $schedule->subject->nama_mapel ?? $schedule->subject->name }}</p>
                    </div>
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                        <label class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Kelompok Kelas</label>
                        <p class="text-base font-bold text-slate-900 dark:text-white mt-1">{{ $classGroup->name ?? $classGroup->nama_kelas }}</p>
                    </div>
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                        <label class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Waktu Pembelajaran</label>
                        <p class="text-base font-bold text-slate-900 dark:text-white mt-1">{{ $schedule->jam_mulai }} - {{ $schedule->jam_selesai }} WIB</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 rounded-xl border border-indigo-100 dark:border-indigo-950 bg-indigo-50/50 dark:bg-indigo-950/30">
                        <label class="text-[11px] font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Pertemuan Ke-</label>
                        <input type="number" name="pertemuan" value="{{ old('pertemuan', $draft['pertemuan'] ?? $nextMeeting) }}" min="1" required class="mt-1 w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2.5 text-sm font-bold text-slate-900 dark:text-white">
                    </div>
                    <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50">
                        <label class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Jumlah Siswa Terdaftar</label>
                        <p class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ $classGroup->students->count() }} Siswa</p>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="button" @click="nextStep(2)" class="inline-flex items-center gap-2 rounded-xl bg-[var(--sabira-primary)] px-6 py-3 text-xs font-bold text-white  hover:bg-[var(--sabira-primary-active)] transition">
                        <span>Lanjut ke Jurnal Pembelajaran</span> <i class="fas fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </div>

            <!-- STEP 2: INFORMASI PEMBELAJARAN & JURNAL -->
            <div x-show="step === 2" x-transition data-step="2" class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm space-y-6">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 dark:bg-indigo-950 text-indigo-600 font-bold">
                        2
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Step 2 — Jurnal Pembelajaran</h3>
                        <p class="text-xs text-slate-500">Tulis ringkasan materi dan catatan kondisi pelaksanaan kelas.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Materi Pembelajaran / Pokok Bahasan <span class="text-rose-500">*</span></label>
                    <textarea name="materi" rows="4" placeholder="Jelaskan pokok bahasan materi pembelajaran yang disampaikan pada pertemuan ini..." required class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 p-3.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:bg-white">{{ old('materi', $draft['materi'] ?? '') }}</textarea>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div><label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Kondisi Kelas</label><textarea name="classroom_condition" rows="3" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 p-3 text-sm">{{ old('classroom_condition', $draft['classroom_condition'] ?? '') }}</textarea></div>
                    <div><label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Catatan Guru</label><textarea name="teacher_notes" rows="3" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 p-3 text-sm">{{ old('teacher_notes', $draft['teacher_notes'] ?? '') }}</textarea></div>
                </div>

                <div class="flex justify-between items-center pt-4">
                    <button type="button" @click="step = 1" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-5 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 transition">
                        <i class="fas fa-arrow-left text-xs"></i> <span>Kembali</span>
                    </button>
                    <button type="button" @click="nextStep(3)" class="inline-flex items-center gap-2 rounded-xl bg-[var(--sabira-primary)] px-6 py-3 text-xs font-bold text-white  hover:bg-[var(--sabira-primary-active)] transition">
                        <span>Lanjut ke Absensi Siswa</span> <i class="fas fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </div>

            <!-- STEP 3: ABSENSI SISWA -->
            <div x-show="step === 3" x-transition data-step="3" class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm space-y-6">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 dark:bg-indigo-950 text-indigo-600 font-bold">
                            3
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Step 3 — Absensi Siswa</h3>
                            <p class="text-xs text-slate-500">Tandai status kehadiran seluruh siswa terdaftar.</p>
                        </div>
                    </div>
                    <button type="button" @click="markAllHadir()" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 px-3 py-1.5 text-xs font-bold text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 transition">
                        <i class="fas fa-check-double text-xs"></i> <span>Tandai Semua Hadir</span>
                    </button>
                </div>

                <!-- Live Counter Bar -->
                <div class="grid grid-cols-4 gap-2 p-3 rounded-xl bg-slate-100 dark:bg-slate-800 text-center text-xs font-bold">
                    <div class="text-emerald-600 dark:text-emerald-400">Hadir: <span x-text="counts.hadir"></span></div>
                    <div class="text-amber-600 dark:text-amber-400">Sakit: <span x-text="counts.sakit"></span></div>
                    <div class="text-blue-600 dark:text-blue-400">Izin: <span x-text="counts.izin"></span></div>
                    <div class="text-rose-600 dark:text-rose-400">Alpa: <span x-text="counts.alpa"></span></div>
                </div>

                <!-- Search Input -->
                <div class="relative">
                    <i class="fas fa-search absolute left-3.5 top-3.5 text-slate-400 text-xs"></i>
                    <input type="search" x-model="studentFilter" placeholder="Cari nama atau NIS siswa..." class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 pl-9 py-2.5 pr-4 text-xs text-slate-900 dark:text-white">
                </div>

                <!-- Student List Mobile-First Cards / Rows -->
                <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
                    @foreach ($classGroup->students as $index => $student)
                        <div x-show="!studentFilter || '{{ strtolower($student->nama_lengkap ?? $student->name) }}'.includes(studentFilter.toLowerCase()) || '{{ $student->nis }}'.includes(studentFilter)" class="p-4 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 font-bold text-xs">
                                    {{ $index + 1 }}
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-900 dark:text-white">{{ $student->nama_lengkap ?? $student->name }}</h4>
                                    <p class="text-[11px] text-slate-400">NIS: {{ $student->nis }}</p>
                                </div>
                            </div>

                            <!-- Mobile Touch Segmented Control -->
                            <div class="w-full sm:w-auto">
                                <div class="grid grid-cols-4 gap-1 rounded-xl bg-slate-200/70 dark:bg-slate-900 p-1">
                                    @foreach (['hadir' => 'Hadir', 'sakit' => 'Sakit', 'izin' => 'Izin', 'alpa' => 'Alpa'] as $stKey => $stLabel)
                                        <label class="cursor-pointer">
                                            <input type="radio" name="attendance[{{ $student->id }}]" value="{{ $stKey }}" @change="updateCounts()" class="peer sr-only attendance-radio" @checked(old('attendance.'.$student->id, $draft['attendance'][$student->id] ?? 'hadir') === $stKey)>
                                            <div class="py-2 px-3 text-center text-xs font-bold text-slate-600 dark:text-slate-400 rounded-lg transition-all peer-checked:bg-[var(--sabira-primary)] peer-checked:text-white shadow-sm min-h-[36px] flex items-center justify-center">
                                                {{ $stLabel }}
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-between items-center pt-4">
                    <button type="button" @click="step = 2" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-5 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 transition">
                        <i class="fas fa-arrow-left text-xs"></i> <span>Kembali</span>
                    </button>
                    <button type="button" @click="nextStep(4)" class="inline-flex items-center gap-2 rounded-xl bg-[var(--sabira-primary)] px-6 py-3 text-xs font-bold text-white  hover:bg-[var(--sabira-primary-active)] transition">
                        <span>Lanjut ke Lokasi & Review</span> <i class="fas fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </div>

            <!-- STEP 4: LOKASI GEOFENCE & REVIEW -->
            <div x-show="step === 4" x-transition data-step="4" class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm space-y-6">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 dark:bg-indigo-950 text-indigo-600 font-bold">
                        4
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Step 4 — Geofence & Review Akhir</h3>
                        <p class="text-xs text-slate-500">Verifikasi status lokasi GPS dan konfirmasi pengiriman sesi.</p>
                    </div>
                </div>

                <!-- Geofence Status Card -->
                <div class="p-4 rounded-xl border" :class="geoSuccess ? 'border-emerald-200 dark:border-emerald-800 bg-emerald-50/60 dark:bg-emerald-950/40' : 'border-amber-200 dark:border-amber-800 bg-amber-50/60 dark:bg-amber-950/40'">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <i class="fas text-xl" :class="geoSuccess ? 'fa-map-marker-alt text-emerald-600' : 'fa-exclamation-triangle text-amber-600'"></i>
                            <div>
                                <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Status Geofence Mengajar</h4>
                                <p class="text-xs font-semibold mt-0.5" :class="geoSuccess ? 'text-emerald-700 dark:text-emerald-300' : 'text-amber-700 dark:text-amber-300'" x-text="geoStatus"></p>
                            </div>
                        </div>
                        <button type="button" @click="getGeoLocation()" class="text-xs font-bold text-indigo-600 hover:underline">
                            <i class="fas fa-sync-alt mr-1"></i> Ambil Ulang GPS
                        </button>
                    </div>
                </div>

                <!-- Summary Grid -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 text-xs">
                    <div>
                        <span class="text-slate-400 font-medium">Hadir</span>
                        <p class="text-base font-bold text-emerald-600" x-text="counts.hadir"></p>
                    </div>
                    <div>
                        <span class="text-slate-400 font-medium">Sakit</span>
                        <p class="text-base font-bold text-amber-600" x-text="counts.sakit"></p>
                    </div>
                    <div>
                        <span class="text-slate-400 font-medium">Izin</span>
                        <p class="text-base font-bold text-blue-600" x-text="counts.izin"></p>
                    </div>
                    <div>
                        <span class="text-slate-400 font-medium">Alpa</span>
                        <p class="text-base font-bold text-rose-600" x-text="counts.alpa"></p>
                    </div>
                </div>

                <div class="p-4 rounded-xl border border-indigo-100 dark:border-indigo-950 bg-indigo-50/30 text-xs text-indigo-900 dark:text-indigo-300 flex items-start gap-3">
                    <i class="fas fa-info-circle text-indigo-500 mt-0.5"></i>
                    <span>Setelah diselesaikan, perubahan absensi atau jurnal pembelajaran harus melalui proses pengajuan koreksi admin.</span>
                </div>

                <div class="flex justify-between items-center pt-4">
                    <button type="button" @click="step = 3" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-5 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 transition">
                        <i class="fas fa-arrow-left text-xs"></i> <span>Kembali</span>
                    </button>
                    <div class="flex flex-col items-end gap-1">
                    <span id="draft-status" class="text-[11px] text-slate-400">{{ $draftSession ? 'Draft terakhir dimuat' : 'Perubahan akan tersimpan otomatis' }}</span>
                    <button type="submit" :disabled="submitting" :class="submitting ? 'cursor-wait opacity-70' : ''" class="sabira-button sabira-button-primary px-7">
                        <i class="fas" :class="submitting ? 'fa-spinner fa-spin' : 'fa-paper-plane'"></i>
                        <span x-text="submitting ? 'Menyelesaikan Pertemuan...' : 'Kirim dan Selesaikan Pertemuan'"></span>
                    </button>
                    </div>
                </div>
            </div>
        </form>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const form = document.getElementById('teaching-session-form');
                const status = document.getElementById('draft-status');
                let timer;
                const saveDraft = () => {
                    status.textContent = 'Menyimpan draft…';
                    fetch(form.dataset.draftUrl, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: new FormData(form),
                    }).then(async response => {
                        const data = await response.json();
                        if (!response.ok) throw new Error(data.message || 'Draft gagal disimpan');
                        status.textContent = `Draft tersimpan ${data.saved_at}`;
                    }).catch(error => { status.textContent = error.message; });
                };
                form.addEventListener('input', event => {
                    if (event.target.type === 'search') return;
                    clearTimeout(timer);
                    timer = setTimeout(saveDraft, 800);
                });
                form.addEventListener('change', event => {
                    if (event.target.type === 'file') return;
                    clearTimeout(timer);
                    timer = setTimeout(saveDraft, 300);
                });
            });
        </script>
    </div>
</x-app-shell>
