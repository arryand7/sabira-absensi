<x-app-shell header-title="Kebijakan Jam Pelajaran" header-subtitle="Atur slot waktu dinamis untuk setiap program pendidikan">
    <div class="space-y-6">
        <section class="sabira-card">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-[var(--sabira-ink)]">Kebijakan waktu per program</h2>
                    <p class="mt-1 max-w-2xl text-sm text-[var(--sabira-muted)]">Kolom JAM pada jadwal guru dan pilihan Jam ke pada form admin memakai data ini. Perubahan tidak mengubah record jadwal lama.</p>
                </div>
                <form method="GET" class="w-full sm:w-80">
                    <x-form-field label="Program Pendidikan" name="program_id">
                        <x-select name="program_id" onchange="this.form.submit()">
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}" @selected($selectedProgram?->is($program))>{{ $program->name }}</option>
                            @endforeach
                        </x-select>
                    </x-form-field>
                </form>
            </div>
        </section>

        @if($selectedProgram)
            <section class="sabira-card">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[var(--sabira-border-soft)] pb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-[var(--sabira-ink)]">{{ $selectedProgram->name }}</h2>
                        <p class="text-sm text-[var(--sabira-muted)]">Rentang program {{ substr($selectedProgram->default_start_time, 0, 5) }}–{{ substr($selectedProgram->default_end_time, 0, 5) }}</p>
                    </div>
                    <x-status-badge :status="$selectedProgram->is_active ? 'Aktif' : 'Nonaktif'" size="sm" />
                </div>

                <form method="POST" action="{{ route('admin.schedule-time-slots.store') }}" class="mt-5 grid gap-3 rounded-[var(--radius-md)] border border-[var(--sabira-border)] bg-[var(--sabira-surface-soft)] p-4 sm:grid-cols-2 xl:grid-cols-8">
                    @csrf
                    <input type="hidden" name="education_program_id" value="{{ $selectedProgram->id }}">
                    <x-form-field label="Urutan" name="position"><x-input type="number" name="position" min="1" value="{{ old('position', ($selectedProgram->timeSlots->max('position') ?? 0) + 1) }}" required /></x-form-field>
                    <x-form-field label="Jam ke" name="slot_number"><x-input type="number" name="slot_number" min="1" value="{{ old('slot_number') }}" /></x-form-field>
                    <x-form-field label="Label" name="label"><x-input name="label" value="{{ old('label') }}" placeholder="Opsional" /></x-form-field>
                    <x-form-field label="Mulai" name="start_time"><x-input type="time" name="start_time" value="{{ old('start_time') }}" required /></x-form-field>
                    <x-form-field label="Selesai" name="end_time"><x-input type="time" name="end_time" value="{{ old('end_time') }}" required /></x-form-field>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2 pt-7 text-sm text-[var(--sabira-body)] xl:col-span-2">
                        <label class="flex items-center gap-2"><input type="checkbox" name="is_break" value="1" class="rounded border-[var(--sabira-border)]"> Istirahat</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="friday_enabled" value="1" checked class="rounded border-[var(--sabira-border)]"> Jumat</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" checked class="rounded border-[var(--sabira-border)]"> Aktif</label>
                    </div>
                    <div class="flex items-end"><x-button type="submit" class="w-full"><i class="fas fa-plus" aria-hidden="true"></i> Tambah slot</x-button></div>
                </form>
            </section>

            <section class="space-y-3">
                @forelse($selectedProgram->timeSlots as $slot)
                    <div class="sabira-card p-4">
                        <form method="POST" action="{{ route('admin.schedule-time-slots.update', $slot) }}" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-[80px_100px_minmax(140px,1fr)_130px_130px_minmax(220px,1fr)_auto] xl:items-end">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="education_program_id" value="{{ $selectedProgram->id }}">
                            <x-form-field label="Urutan" :name="'position_'.$slot->id"><x-input type="number" name="position" min="1" :value="$slot->position" required /></x-form-field>
                            <x-form-field label="Jam ke" :name="'slot_number_'.$slot->id"><x-input type="number" name="slot_number" min="1" :value="$slot->slot_number" :disabled="$slot->is_break" /></x-form-field>
                            <x-form-field label="Label" :name="'label_'.$slot->id"><x-input name="label" :value="$slot->label" placeholder="Jam pelajaran" /></x-form-field>
                            <x-form-field label="Mulai" :name="'start_'.$slot->id"><x-input type="time" name="start_time" :value="substr($slot->start_time, 0, 5)" required /></x-form-field>
                            <x-form-field label="Selesai" :name="'end_'.$slot->id"><x-input type="time" name="end_time" :value="substr($slot->end_time, 0, 5)" required /></x-form-field>
                            <div class="flex min-h-11 flex-wrap items-center gap-x-4 gap-y-2 text-sm text-[var(--sabira-body)]">
                                <label class="flex items-center gap-2"><input type="checkbox" name="is_break" value="1" @checked($slot->is_break) class="rounded"> Istirahat</label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="friday_enabled" value="1" @checked($slot->friday_enabled) class="rounded"> Jumat</label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" @checked($slot->is_active) class="rounded"> Aktif</label>
                            </div>
                            <div class="flex gap-2">
                                <x-button type="submit" variant="secondary"><i class="fas fa-floppy-disk" aria-hidden="true"></i> Simpan</x-button>
                                <button type="submit" form="delete-slot-{{ $slot->id }}" class="sabira-button sabira-button-danger min-h-11 px-3" aria-label="Hapus slot"><i class="fas fa-trash" aria-hidden="true"></i></button>
                            </div>
                        </form>
                        <form id="delete-slot-{{ $slot->id }}" method="POST" action="{{ route('admin.schedule-time-slots.destroy', $slot) }}" onsubmit="return confirm('Hapus slot ini? Jadwal lama tidak akan ikut terhapus.')">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                @empty
                    <x-empty-state title="Belum ada kebijakan jam" description="Tambahkan slot pertama untuk program ini." icon="far fa-clock" />
                @endforelse
            </section>
        @else
            <x-empty-state title="Belum ada program pendidikan" description="Buat Program Pendidikan sebelum mengatur jam pelajaran." icon="fas fa-graduation-cap" />
        @endif
    </div>
</x-app-shell>
