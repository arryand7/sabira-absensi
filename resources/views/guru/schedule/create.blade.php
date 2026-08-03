<x-app-shell>
    <div class="flex">
        <div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
            <div class="mb-4">
                <a href="{{ route('guru.schedule') }}" class="inline-flex items-center text-sm text-[var(--sabira-ink)] hover:text-blue-600">
                    <i class="bi bi-arrow-left-circle-fill text-lg mr-1"></i> Kembali
                </a>
            </div>

            <div class="bg-[var(--sabira-neutral-strong)] shadow rounded-xl p-6 max-h-[calc(100vh-100px)] overflow-y-auto">
                <h2 class="text-2xl font-bold text-[var(--sabira-ink)] mb-4">Tambah Jadwal</h2>

                @if ($errors->any())
                    <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
                        <strong>Ups!</strong> Ada beberapa masalah dengan input kamu.
                        <ul class="list-disc ml-5 mt-2 text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('guru.schedule.store') }}" method="POST" class="space-y-6">
                    @csrf

                    {{-- Guru --}}
                    <div>
                        <label for="user_id" class="block font-semibold mb-1 text-[var(--sabira-ink)]">Guru</label>
                        <select name="user_id" id="user_id" class="form-select">
                            <option value="" data-jenis-guru="">-- Pilih Guru --</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}"
                                {{ old('user_id', $selectedGuruId) == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('user_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Mapel --}}
                    <div>
                        <label for="subject_id" class="block font-semibold mb-1 text-[var(--sabira-ink)]">Mata Pelajaran</label>
                        <select name="subject_id" class="form-select">
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->nama_mapel }}
                                </option>
                            @endforeach
                        </select>
                        @error('subject_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Dynamic Rows --}}
                    <div>
                        <label class="block font-semibold mb-2 text-[var(--sabira-ink)]">Jadwal</label>
                        <div id="schedule-rows-container" class="space-y-4">
                            @php $oldDetails = old('details', [0 => []]); @endphp
                            @foreach ($oldDetails as $i => $detail)
                                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-6 gap-4 schedule-row">
                                    <div>
                                        <select name="details[{{ $i }}][education_program_id]" class="form-select mt-1 schedule-program" required>
                                            <option value="">-- Program --</option>
                                            @foreach($educationPrograms as $program)
                                                <option value="{{ $program->id }}" @selected((string) old("details.$i.education_program_id", $detail['education_program_id'] ?? request('program_id')) === (string) $program->id)>{{ $program->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <select name="details[{{ $i }}][hari]" class="form-select mt-1 schedule-day">
                                            <option value="">-- Hari --</option>
                                            @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Ahad'] as $hari)
                                                <option value="{{ $hari }}" {{ old("details.$i.hari", $detail['hari'] ?? request('hari')) == $hari ? 'selected' : '' }}>
                                                    {{ $hari }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <select name="details[{{ $i }}][jam_ke]" class="form-select mt-1 schedule-slot">
                                            <option value="">-- Jam ke --</option>
                                        </select>
                                    </div>
                                    <div>
                                        <input type="time" name="details[{{ $i }}][jam_mulai]" class="form-input mt-1 schedule-start"
                                            value="{{ old("details.$i.jam_mulai", $detail['jam_mulai'] ?? request('jam_mulai')) }}" />
                                    </div>
                                    <div>
                                        <input type="time" name="details[{{ $i }}][jam_selesai]" class="form-input mt-1 schedule-end"
                                            value="{{ old("details.$i.jam_selesai", $detail['jam_selesai'] ?? request('jam_selesai')) }}" />
                                    </div>
                                    <div class="flex gap-2 items-center">
                                        <select name="details[{{ $i }}][class_group_id]" class="form-select mt-1 w-full">
                                            <option value="">-- Kelas --</option>
                                            @foreach($classGroups as $group)
                                                <option value="{{ $group->id }}"
                                                    {{ old("details.$i.class_group_id", $detail['class_group_id'] ?? '') == $group->id ? 'selected' : '' }}>
                                                    {{ $group->nama_kelas }} ({{ ($group->jenis_kelas == 'formal' ? 'Reguler' : ($group->jenis_kelas == 'muadalah' ? 'Non Reguler' : $group->jenis_kelas)) }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="button" onclick="removeScheduleRow(this)" class="text-red-500 hover:text-red-700">
                                            <i class="bi bi-x-circle-fill"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <button type="button" onclick="addScheduleRow()" class="mt-4 bg-gray-200 hover:bg-gray-300 text-xs px-4 py-2 rounded shadow">
                            + Tambah Jadwal
                        </button>
                    </div>

                    {{-- Tahun Ajaran --}}
                    <div>
                        <label for="academic_year_id" class="block font-semibold mb-1 text-[var(--sabira-ink)]">Tahun Ajaran</label>
                        <select name="academic_year_id" class="form-select" required>
                            @foreach ($academicYears as $year)
                                <option value="{{ $year->id }}" {{ old('academic_year_id', $tahunAktif?->id) == $year->id ? 'selected' : '' }}>
                                    {{ $year->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('academic_year_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="semester" class="block font-semibold mb-1 text-[var(--sabira-ink)]">Semester</label>
                        <select name="semester" id="semester" class="sabira-select" required>
                            <option value="ganjil" @selected(old('semester', \App\Models\AcademicYear::currentSemester()) === 'ganjil')>Ganjil</option>
                            <option value="genap" @selected(old('semester', \App\Models\AcademicYear::currentSemester()) === 'genap')>Genap</option>
                        </select>
                    </div>

                    {{-- Submit --}}
                    <div>
                        <button type="submit" class="bg-[var(--sabira-primary)] hover:bg-[var(--sabira-primary-active)] text-white px-6 py-2 rounded-md text-xs shadow">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let rowIndex = 1;

        function addScheduleRow() {
            const container = document.getElementById('schedule-rows-container');
            const newRow = document.createElement('div');
            newRow.className = 'grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-6 gap-4 schedule-row';
            newRow.innerHTML = `
                <div>
                    <select name="details[${rowIndex}][education_program_id]" class="form-select mt-1 schedule-program" required>
                        <option value="">-- Program --</option>
                        @foreach($educationPrograms as $program)
                            <option value="{{ $program->id }}">{{ $program->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="details[${rowIndex}][hari]" class="form-select mt-1 schedule-day">
                        <option value="">-- Hari --</option>
                        @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Ahad'] as $hari)
                            <option value="{{ $hari }}">{{ $hari }}</option>
                        @endforeach
                    </select>
                </div>
                <div><select name="details[${rowIndex}][jam_ke]" class="form-select mt-1 schedule-slot"><option value="">-- Jam ke --</option></select></div>
                <div>
                    <input type="time" name="details[${rowIndex}][jam_mulai]" class="form-input mt-1 schedule-start" />
                </div>
                <div>
                    <input type="time" name="details[${rowIndex}][jam_selesai]" class="form-input mt-1 schedule-end" />
                </div>
                <div class="flex gap-2 items-center">
                    <select name="details[${rowIndex}][class_group_id]" class="form-select mt-1 w-full">
                        <option value="">-- Kelas --</option>
                        @foreach($classGroups as $group)
                            <option value="{{ $group->id }}">{{ $group->nama_kelas }} ({{ ucfirst($group->jenis_kelas) }})</option>
                        @endforeach
                    </select>
                    <button type="button" onclick="removeScheduleRow(this)" class="text-red-500 hover:text-red-700">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </div>
            `;
            container.appendChild(newRow);
            initializeRow(newRow);
            rowIndex++;
        }

        function removeScheduleRow(button) {
            const row = button.closest('.schedule-row');
            row.remove();
        }

        const slotPolicies = @js($educationPrograms->mapWithKeys(fn ($program) => [$program->id => $program->activeTimeSlots->where('is_break', false)->map(fn ($slot) => ['id' => $slot->id, 'label' => $slot->label ?: 'Jam '.$slot->slot_number, 'start' => substr($slot->start_time, 0, 5), 'end' => substr($slot->end_time, 0, 5), 'friday_enabled' => $slot->friday_enabled])->values()]));

        function availableSlots(row) {
            const programId = row.querySelector('.schedule-program')?.value;
            const day = row.querySelector('.schedule-day')?.value;
            return (slotPolicies[programId] || []).filter((slot) => day !== 'Jumat' || slot.friday_enabled);
        }

        function refreshSlots(row) {
            const select = row.querySelector('.schedule-slot');
            const start = row.querySelector('.schedule-start');
            const end = row.querySelector('.schedule-end');
            const slots = availableSlots(row);
            select.innerHTML = '<option value="">-- Jam ke --</option>';
            slots.forEach((slot) => select.add(new Option(`${slot.label} · ${slot.start}–${slot.end}`, slot.id)));
            const match = slots.find((slot) => slot.start === start.value && slot.end === end.value);
            if (match) select.value = String(match.id);
        }

        function initializeRow(row) {
            const slot = row.querySelector('.schedule-slot');
            const start = row.querySelector('.schedule-start');
            const end = row.querySelector('.schedule-end');
            row.querySelector('.schedule-program')?.addEventListener('change', () => refreshSlots(row));
            row.querySelector('.schedule-day')?.addEventListener('change', () => refreshSlots(row));
            slot?.addEventListener('change', () => {
                const selected = availableSlots(row).find((item) => String(item.id) === slot.value);
                if (selected) {
                    start.value = selected.start;
                    end.value = selected.end;
                }
            });
            refreshSlots(row);
        }

        document.querySelectorAll('.schedule-row').forEach(initializeRow);
    </script>
    @endpush
</x-app-shell>
