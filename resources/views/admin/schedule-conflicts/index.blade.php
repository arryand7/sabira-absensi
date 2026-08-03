<x-app-shell header-title="Benturan Jadwal Guru" header-subtitle="Tinjau dan selesaikan jadwal yang bertumpang tindih">
    <div class="space-y-6">
        <section class="grid gap-4 sm:grid-cols-3">
            <x-stat-card title="Menunggu Verifikasi" :value="$pendingCount" subtitle="Benturan aktif" icon="fas fa-triangle-exclamation" color="rose" />
            <x-stat-card title="Ditampilkan" :value="$conflicts->total()" subtitle="Sesuai filter" icon="fas fa-filter" color="amber" />
            <x-stat-card title="Status Filter" :value="request('status', 'pending_review') === 'pending_review' ? 'Menunggu' : 'Semua/Riwayat'" subtitle="Ubah melalui filter" icon="fas fa-list-check" color="blue" />
        </section>

        <form method="GET" class="sabira-filter-bar grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
            <x-form-field label="Status" name="status">
                <x-select name="status">
                    <option value="pending_review" @selected(request('status', 'pending_review') === 'pending_review')>Menunggu verifikasi</option>
                    <option value="" @selected(request()->has('status') && request('status') === '')>Semua status</option>
                    <option value="confirmed" @selected(request('status') === 'confirmed')>Diverifikasi, keduanya aktif</option>
                    <option value="resolved_keep_current" @selected(request('status') === 'resolved_keep_current')>Pertahankan jadwal baru</option>
                    <option value="resolved_keep_existing" @selected(request('status') === 'resolved_keep_existing')>Pertahankan jadwal lama</option>
                    <option value="dismissed" @selected(request('status') === 'dismissed')>Dibatalkan</option>
                </x-select>
            </x-form-field>
            <x-form-field label="Guru" name="teacher_id">
                <x-select name="teacher_id">
                    <option value="">Semua guru</option>
                    @foreach($teachers as $teacher)<option value="{{ $teacher->id }}" @selected((string) request('teacher_id') === (string) $teacher->id)>{{ $teacher->name }}</option>@endforeach
                </x-select>
            </x-form-field>
            <x-form-field label="Hari" name="hari">
                <x-select name="hari"><option value="">Semua hari</option>@foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Ahad'] as $day)<option value="{{ $day }}" @selected(request('hari') === $day)>{{ $day }}</option>@endforeach</x-select>
            </x-form-field>
            <x-form-field label="Tahun Ajaran" name="academic_year_id">
                <x-select name="academic_year_id"><option value="">Semua tahun</option>@foreach($academicYears as $year)<option value="{{ $year->id }}" @selected((string) request('academic_year_id') === (string) $year->id)>{{ $year->name }}</option>@endforeach</x-select>
            </x-form-field>
            <x-form-field label="Semester" name="semester">
                <x-select name="semester"><option value="">Semua</option><option value="ganjil" @selected(request('semester') === 'ganjil')>Ganjil</option><option value="genap" @selected(request('semester') === 'genap')>Genap</option></x-select>
            </x-form-field>
            <div class="flex items-end gap-2"><x-button type="submit" class="flex-1"><i class="fas fa-filter"></i> Terapkan</x-button><x-button variant="secondary" :href="route('admin.schedule-conflicts.index')" aria-label="Reset filter"><i class="fas fa-rotate-left"></i></x-button></div>
        </form>

        <section class="sabira-card overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="sabira-data-table min-w-[980px]">
                    <thead><tr><th>Guru</th><th>Jadwal terdeteksi</th><th>Bentrok dengan</th><th>Jenis</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($conflicts as $conflict)
                            <tr>
                                <td><strong class="text-[var(--sabira-ink)]">{{ $conflict->teacher?->name ?? '-' }}</strong><span class="block text-xs text-[var(--sabira-muted)]">{{ $conflict->detected_at?->format('d/m/Y H:i') }}</span></td>
                                <td>@include('admin.schedule-conflicts.partials.schedule-summary', ['schedule' => $conflict->schedule])</td>
                                <td>@include('admin.schedule-conflicts.partials.schedule-summary', ['schedule' => $conflict->conflictingSchedule])</td>
                                <td><x-status-badge :status="$conflict->conflict_type === 'teacher_overlap' ? 'Guru bentrok' : 'Kelas bentrok'" size="sm" /></td>
                                <td><x-status-badge :status="str_replace('_', ' ', $conflict->status)" size="sm" /></td>
                                <td class="text-right"><x-button variant="secondary" :href="route('admin.schedule-conflicts.show', $conflict)">Tinjau</x-button></td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-empty-state title="Tidak ada benturan" description="Tidak ada record benturan yang sesuai dengan filter ini." icon="fas fa-calendar-check" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{ $conflicts->links() }}
    </div>
</x-app-shell>
