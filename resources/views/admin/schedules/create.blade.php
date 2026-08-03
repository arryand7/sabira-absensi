<x-app-shell>
<div class="flex">
        <div class="w-full sm:px-6 lg:px-8 space-y-6">
            <div class="mb-2">
                <a href="{{ url()->previous() }}"
                    class="inline-flex items-center gap-2 text-sm bg-gray-200 hover:bg-gray-300 text-[var(--sabira-ink)] px-3 py-1.5 rounded-md shadow-sm transition-all duration-150">
                    <i class="bi bi-arrow-left-circle-fill text-lg"></i> Kembali
                </a>
            </div>

            <div class="bg-[var(--sabira-neutral-strong)] shadow rounded-xl p-8 max-h-[calc(100vh-100px)] overflow-y-auto ring-1 ring-gray-300">
                <h2 class="text-2xl font-bold text-[var(--sabira-ink)] mb-6">Tambah Jadwal</h2>

                {{-- @if ($errors->any())
                    <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
                        <strong>Ups!</strong> Ada beberapa masalah dengan input kamu.
                        <ul class="list-disc ml-5 mt-2 text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif --}}

                <form action="{{ route('admin.schedules.store') }}" method="POST" class="space-y-6">
                    @csrf

                    {{-- Guru --}}
                    <div class="space-y-1">
                        <label for="user_id" class="block font-semibold text-[var(--sabira-ink)]">Guru</label>
                        <select name="user_id" id="user_id" class="form-select w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">-- Pilih Guru --</option>
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
                    <div class="space-y-1">
                        <label for="subject_id" class="block font-semibold text-[var(--sabira-ink)]">Mata Pelajaran</label>
                        <select name="subject_id" class="form-select w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->nama_mapel }}
                                </option>
                            @endforeach
                        </select>
                        @error('subject_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Jadwal --}}
                    <div class="space-y-2">
                        <label class="block font-semibold text-[var(--sabira-ink)]">Jadwal</label>
                        <div id="schedule-rows-container" class="space-y-4">
                            @php $oldDetails = old('details', [0 => []]); @endphp
                            @foreach ($oldDetails as $i => $detail)
                                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-6 gap-4 schedule-row">
                                    <div>
                                        <select name="details[{{ $i }}][education_program_id]" class="form-select w-full rounded-md border-gray-300 shadow-sm schedule-program" required>
                                            <option value="">-- Program --</option>
                                            @foreach($educationPrograms as $program)
                                                <option value="{{ $program->id }}" @selected((string) old("details.$i.education_program_id", $detail['education_program_id'] ?? request('program_id')) === (string) $program->id)>{{ $program->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <select name="details[{{ $i }}][hari]" class="form-select w-full rounded-md border-gray-300 shadow-sm schedule-day">
                                            <option value="">-- Hari --</option>
                                            @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Ahad'] as $hari)
                                                <option value="{{ $hari }}" {{ old("details.$i.hari", $detail['hari'] ?? '') == $hari ? 'selected' : '' }}>
                                                    {{ $hari }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <select name="details[{{ $i }}][jam_ke]" class="form-select w-full rounded-md border-gray-300 shadow-sm schedule-slot">
                                            <option value="">-- Jam ke --</option>
                                        </select>
                                    </div>
                                    <div>
                                        <input type="time" name="details[{{ $i }}][jam_mulai]"
                                            value="{{ old("details.$i.jam_mulai", $detail['jam_mulai'] ?? '') }}"
                                            class="form-input w-full rounded-md border-gray-300 shadow-sm schedule-start" />
                                    </div>
                                    <div>
                                        <input type="time" name="details[{{ $i }}][jam_selesai]"
                                            value="{{ old("details.$i.jam_selesai", $detail['jam_selesai'] ?? '') }}"
                                            class="form-input w-full rounded-md border-gray-300 shadow-sm schedule-end" />
                                    </div>
                                    <div class="flex gap-2 items-center">
                                        <select name="details[{{ $i }}][class_group_id]" class="form-select w-full rounded-md border-gray-300 shadow-sm schedule-class">
                                            <option value="">-- Kelas --</option>
                                            @foreach($classGroups as $group)
                                                <option value="{{ $group->id }}" data-program-id="{{ $group->education_program_id }}"
                                                    {{ old("details.$i.class_group_id", $detail['class_group_id'] ?? '') == $group->id ? 'selected' : '' }}>
                                                    {{ $group->nama_kelas }} ({{ ucfirst($group->jenis_kelas) }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="button" onclick="removeScheduleRow(this)" class="text-red-500 hover:text-red-700 transition">
                                            <i class="bi bi-x-circle-fill text-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <button type="button" onclick="addScheduleRow()" class="inline-flex items-center gap-2 bg-[var(--sabira-surface-strong)] hover:bg-[var(--sabira-surface-strong)] text-[var(--sabira-ink)] text-xs px-3 py-1.5 rounded-md shadow-sm transition">
                            <i class="bi bi-plus-circle-fill"></i> Tambah Jadwal
                        </button>
                    </div>

                    {{-- Tahun Ajaran --}}
                    <div class="space-y-1">
                        <label for="academic_year_id" class="block font-semibold text-[var(--sabira-ink)]">Tahun Ajaran</label>
                        <select name="academic_year_id" class="form-select w-full rounded-md border-gray-300 shadow-sm" required>
                            @foreach ($academicYears as $year)
                                <option value="{{ $year->id }}" {{ old('academic_year_id', $selectedYear ?? $tahunAktif?->id) == $year->id ? 'selected' : '' }}>
                                    {{ $year->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('academic_year_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-1">
                        <label for="semester" class="block font-semibold text-[var(--sabira-ink)]">Semester</label>
                        <select name="semester" id="semester" class="sabira-select" required>
                            <option value="ganjil" @selected(old('semester', \App\Models\AcademicYear::currentSemester()) === 'ganjil')>Ganjil</option>
                            <option value="genap" @selected(old('semester', \App\Models\AcademicYear::currentSemester()) === 'genap')>Genap</option>
                        </select>
                    </div>

                    {{-- Submit --}}
                    <div class="pt-2">
                        <button type="submit" class="inline-block bg-[var(--sabira-primary)] hover:bg-[var(--sabira-primary-active)] text-white text-sm px-5 py-2 rounded-md shadow-sm transition">
                            <i class="bi bi-save-fill mr-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let rowIndex = {{ count($oldDetails) }};

        function addScheduleRow() {
            const container = document.getElementById('schedule-rows-container');
            const newRow = document.createElement('div');
            newRow.className = 'grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-6 gap-4 schedule-row';
            newRow.innerHTML = `
                <div>
                    <select name="details[${rowIndex}][education_program_id]" class="form-select w-full rounded-md border-gray-300 shadow-sm mt-1 schedule-program" required>
                        <option value="">-- Program --</option>
                        @foreach($educationPrograms as $program)
                            <option value="{{ $program->id }}">{{ $program->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="details[${rowIndex}][hari]" class="form-select w-full rounded-md border-gray-300 shadow-sm mt-1 schedule-day">
                        <option value="">-- Hari --</option>
                        @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Ahad'] as $hari)
                            <option value="{{ $hari }}">{{ $hari }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="details[${rowIndex}][jam_ke]" class="form-select w-full rounded-md border-gray-300 shadow-sm mt-1 schedule-slot">
                        <option value="">-- Jam ke --</option>
                    </select>
                </div>
                <div>
                    <input type="time" name="details[${rowIndex}][jam_mulai]" class="form-input w-full rounded-md border-gray-300 shadow-sm mt-1 schedule-start" />
                </div>
                <div>
                    <input type="time" name="details[${rowIndex}][jam_selesai]" class="form-input w-full rounded-md border-gray-300 shadow-sm mt-1 schedule-end" />
                </div>
                <div class="flex gap-2 items-center">
                    <select name="details[${rowIndex}][class_group_id]" class="form-select w-full rounded-md border-gray-300 shadow-sm mt-1 schedule-class">
                        <option value="">-- Kelas --</option>
                        @foreach($classGroups as $group)
                            <option value="{{ $group->id }}" data-program-id="{{ $group->education_program_id }}">{{ $group->nama_kelas }} ({{ ucfirst($group->jenis_kelas) }})</option>
                        @endforeach
                    </select>
                    <button type="button" onclick="removeScheduleRow(this)" class="text-red-500 hover:text-red-700 transition">
                        <i class="bi bi-x-circle-fill text-lg"></i>
                    </button>
                </div>
            `;
            container.appendChild(newRow);
            initializeScheduleRow(newRow);
            rowIndex++;
        }

        function removeScheduleRow(button) {
            const row = button.closest('.schedule-row');
            row.remove();
        }

        const slotPolicies = @js($slotPolicies);

        function slotsForRow(row) {
            const programId = row.querySelector('.schedule-program')?.value;
            const day = row.querySelector('.schedule-day')?.value;

            return (slotPolicies[programId] || []).filter((slot) => day !== 'Jumat' || slot.friday_enabled);
        }

        function refreshSlotOptions(row) {
            const slotSelect = row.querySelector('.schedule-slot');
            const startInput = row.querySelector('.schedule-start');
            const endInput = row.querySelector('.schedule-end');
            const current = slotSelect.value;
            const slots = slotsForRow(row);
            slotSelect.innerHTML = '<option value="">-- Jam ke --</option>';
            slots.forEach((slot) => {
                const option = document.createElement('option');
                option.value = slot.id;
                option.textContent = `${slot.label} · ${slot.start}–${slot.end}`;
                slotSelect.appendChild(option);
            });
            slotSelect.value = current;
            const match = slots.find((slot) => slot.start === startInput.value && slot.end === endInput.value);
            if (match) slotSelect.value = String(match.id);
        }

        function updateTimesFromSlot(row) {
            const slotSelect = row.querySelector('.schedule-slot');
            const startInput = row.querySelector('.schedule-start');
            const endInput = row.querySelector('.schedule-end');
            if (!slotSelect || !startInput || !endInput) {
                return;
            }

            const selected = slotsForRow(row).find((slot) => String(slot.id) === slotSelect.value);
            if (!selected) {
                return;
            }

            startInput.value = selected.start;
            endInput.value = selected.end;
        }

        function updateSlotFromTimes(row) {
            const daySelect = row.querySelector('.schedule-day');
            const slotSelect = row.querySelector('.schedule-slot');
            const startInput = row.querySelector('.schedule-start');
            const endInput = row.querySelector('.schedule-end');
            if (!daySelect || !slotSelect || !startInput || !endInput) {
                return;
            }

            const match = slotsForRow(row).find((slot) => slot.start === startInput.value && slot.end === endInput.value);
            slotSelect.value = match ? String(match.id) : '';
        }

        function initializeScheduleRow(row) {
            const daySelect = row.querySelector('.schedule-day');
            const slotSelect = row.querySelector('.schedule-slot');
            const startInput = row.querySelector('.schedule-start');
            const endInput = row.querySelector('.schedule-end');
            const classSelect = row.querySelector('.schedule-class');
            const programSelect = row.querySelector('.schedule-program');

            if (!daySelect || !slotSelect || !startInput || !endInput) {
                return;
            }

            daySelect.addEventListener('change', () => {
                refreshSlotOptions(row);
            });

            classSelect?.addEventListener('change', () => refreshSlotOptions(row));
            programSelect?.addEventListener('change', () => refreshSlotOptions(row));

            slotSelect.addEventListener('change', () => {
                updateTimesFromSlot(row);
            });

            startInput.addEventListener('change', () => {
                updateSlotFromTimes(row);
            });

            endInput.addEventListener('change', () => {
                updateSlotFromTimes(row);
            });

            refreshSlotOptions(row);
        }

        document.querySelectorAll('.schedule-row').forEach((row) => {
            initializeScheduleRow(row);
        });
    </script>
    @endpush
    @if($errors->has('jadwal'))
        <x-alert type="danger" title="Jadwal bentrok">{{ $errors->first('jadwal') }}</x-alert>
    @endif
</x-app-shell>
