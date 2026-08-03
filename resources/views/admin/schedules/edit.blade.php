<x-app-shell>
<div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
        <div class="bg-[var(--sabira-neutral-strong)] shadow-md rounded-xl p-6">
{{--
            <div class="mb-4">
                <a href="{{ route('admin.schedules.index') }}" class="inline-flex items-center text-sm text-[var(--sabira-ink)] hover:text-blue-600">
                    <i class="bi bi-arrow-left-circle-fill text-lg mr-1"></i>
                    Kembali ke Jadwal
                </a>
            </div> --}}

            <h2 class="text-2xl font-bold text-[var(--sabira-ink)] mb-6">
                {{ __('Edit Jadwal Guru') }}
            </h2>

            {{-- @if ($errors->any())
                <div class="mb-4 px-4 py-3 bg-red-100 text-red-700 border border-red-300 rounded">
                    <strong>Ups!</strong> Ada beberapa masalah dengan input kamu:
                    <ul class="mt-2 list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif --}}

            <form action="{{ route('admin.schedules.update', $schedule->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="user_id" class="block font-semibold text-[var(--sabira-ink)] mb-1">Guru</label>
                    <select name="user_id" id="user_id" class="w-full rounded border border-gray-300 bg-[var(--sabira-surface)] text-[var(--sabira-ink)] px-3 py-2 focus:ring-blue-500 focus:outline-none">
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ old('user_id', $schedule->user_id) == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="subject_id" class="block font-semibold text-[var(--sabira-ink)] mb-1">Mata Pelajaran</label>
                    <select name="subject_id" id="subject_id" class="w-full rounded border border-gray-300 bg-[var(--sabira-surface)] text-[var(--sabira-ink)] px-3 py-2 focus:ring-blue-500 focus:outline-none">
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id', $schedule->subject_id) == $subject->id ? 'selected' : '' }}>
                                {{ $subject->nama_mapel }}
                            </option>
                        @endforeach
                    </select>
                    @error('subject_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="education_program_id" class="block font-semibold text-[var(--sabira-ink)] mb-1">Program Pendidikan</label>
                    <select name="education_program_id" id="education_program_id" class="sabira-select" required>
                        @foreach($educationPrograms as $program)
                            <option value="{{ $program->id }}" @selected((string) old('education_program_id', $schedule->education_program_id ?: $schedule->classGroup?->education_program_id) === (string) $program->id)>{{ $program->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="class_group_id" class="block font-semibold text-[var(--sabira-ink)] mb-1">Kelas</label>
                    <select name="class_group_id" id="class_group_id" class="w-full rounded border border-gray-300 bg-[var(--sabira-surface)] text-[var(--sabira-ink)] px-3 py-2 focus:ring-blue-500 focus:outline-none">
                        @foreach($classGroups as $group)
                            <option value="{{ $group->id }}" data-program-id="{{ $group->education_program_id }}" {{ old('class_group_id', $schedule->class_group_id) == $group->id ? 'selected' : '' }}>
                                {{ $group->nama_kelas }} ({{ ucfirst($group->jenis_kelas) }})
                            </option>
                        @endforeach
                    </select>
                    @error('class_group_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="hari" class="block font-semibold text-[var(--sabira-ink)] mb-1">Hari</label>
                        <select name="hari" id="hari" class="w-full rounded border border-gray-300 bg-[var(--sabira-surface)] text-[var(--sabira-ink)] px-3 py-2 focus:ring-blue-500 focus:outline-none schedule-day">
                            @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Ahad'] as $hari)
                                <option value="{{ $hari }}" {{ old('hari', $schedule->hari) == $hari ? 'selected' : '' }}>{{ $hari }}</option>
                            @endforeach
                        </select>
                        @error('hari')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="jam_ke" class="block font-semibold text-[var(--sabira-ink)] mb-1">Jam ke</label>
                        <select name="jam_ke" id="jam_ke" class="w-full rounded border border-gray-300 bg-[var(--sabira-surface)] text-[var(--sabira-ink)] px-3 py-2 focus:ring-blue-500 focus:outline-none schedule-slot">
                            <option value="">-- Jam ke --</option>
                        </select>
                    </div>

                    <div>
                        <label for="jam_mulai" class="block font-semibold text-[var(--sabira-ink)] mb-1">Jam Mulai</label>
                        <input type="time" name="jam_mulai" id="jam_mulai" value="{{ old('jam_mulai', $schedule->jam_mulai) }}" class="w-full rounded border border-gray-300 bg-[var(--sabira-surface)] text-[var(--sabira-ink)] px-3 py-2 focus:ring-blue-500 focus:outline-none schedule-start">
                        @error('jam_mulai')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="jam_selesai" class="block font-semibold text-[var(--sabira-ink)] mb-1">Jam Selesai</label>
                        <input type="time" name="jam_selesai" id="jam_selesai" value="{{ old('jam_selesai', $schedule->jam_selesai) }}" class="w-full rounded border border-gray-300 bg-[var(--sabira-surface)] text-[var(--sabira-ink)] px-3 py-2 focus:ring-blue-500 focus:outline-none schedule-end">
                        @error('jam_selesai')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="academic_year_id">Tahun Ajaran</label>
                    <select name="academic_year_id" class="form-control" required>
                        @foreach ($academicYears as $year)
                            <option value="{{ $year->id }}"
                                {{ old('academic_year_id', $schedule->academic_year_id ?? $tahunAktif?->id) == $year->id ? 'selected' : '' }}>
                                {{ $year->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="semester">Semester</label>
                    <select name="semester" id="semester" class="sabira-select" required>
                        <option value="ganjil" @selected(old('semester', $schedule->semester) === 'ganjil')>Ganjil</option>
                        <option value="genap" @selected(old('semester', $schedule->semester) === 'genap')>Genap</option>
                    </select>
                </div>

                <div class="flex gap-4 mt-6">
                    <button type="submit" class="bg-[var(--sabira-primary)] hover:bg-[var(--sabira-primary-active)] text-white px-4 py-2 rounded shadow text-sm">
                        <i class="bi bi-save-fill mr-1"></i> Simpan Perubahan
                    </button>
                    <a href="{{ url()->previous() }}"
                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md shadow flex items-center gap-2">
                        <i class="bi bi-arrow-left-circle-fill"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            const slotPolicies = @js($slotPolicies);

            function availableSlots(daySelect, programSelect) {
                const programId = programSelect.value;
                return (slotPolicies[programId] || []).filter((slot) => daySelect.value !== 'Jumat' || slot.friday_enabled);
            }

            function refreshOptions(daySelect, classSelect, slotSelect, startInput, endInput) {
                const slots = availableSlots(daySelect, classSelect);
                slotSelect.innerHTML = '<option value="">-- Jam ke --</option>';
                slots.forEach((slot) => {
                    const option = document.createElement('option');
                    option.value = slot.id;
                    option.textContent = `${slot.label} · ${slot.start}–${slot.end}`;
                    slotSelect.appendChild(option);
                });
                const match = slots.find((slot) => slot.start === startInput.value && slot.end === endInput.value);
                slotSelect.value = match ? String(match.id) : '';
            }

            function updateTimesFromSlot(slotSelect, startInput, endInput) {
                if (!slotSelect || !startInput || !endInput) {
                    return;
                }

                const programSelect = document.getElementById('education_program_id');
                const selected = availableSlots(document.getElementById('hari'), programSelect)
                    .find((slot) => String(slot.id) === slotSelect.value);
                if (!selected) {
                    return;
                }

                startInput.value = selected.start;
                endInput.value = selected.end;
            }

            function updateSlotFromTimes(daySelect, slotSelect, startInput, endInput) {
                if (!daySelect || !slotSelect || !startInput || !endInput) {
                    return;
                }

                const programSelect = document.getElementById('education_program_id');
                const match = availableSlots(daySelect, programSelect).find((slot) => slot.start === startInput.value && slot.end === endInput.value);
                slotSelect.value = match ? String(match.id) : '';
            }

            document.addEventListener('DOMContentLoaded', () => {
                const daySelect = document.getElementById('hari');
                const slotSelect = document.getElementById('jam_ke');
                const startInput = document.getElementById('jam_mulai');
                const endInput = document.getElementById('jam_selesai');
                const classSelect = document.getElementById('class_group_id');
                const programSelect = document.getElementById('education_program_id');

                if (!daySelect || !slotSelect || !startInput || !endInput) {
                    return;
                }

                daySelect.addEventListener('change', () => {
                    refreshOptions(daySelect, programSelect, slotSelect, startInput, endInput);
                });
                classSelect.addEventListener('change', () => refreshOptions(daySelect, programSelect, slotSelect, startInput, endInput));
                programSelect.addEventListener('change', () => refreshOptions(daySelect, programSelect, slotSelect, startInput, endInput));

                slotSelect.addEventListener('change', () => {
                    updateTimesFromSlot(slotSelect, startInput, endInput);
                });

                startInput.addEventListener('change', () => {
                    updateSlotFromTimes(daySelect, slotSelect, startInput, endInput);
                });

                endInput.addEventListener('change', () => {
                    updateSlotFromTimes(daySelect, slotSelect, startInput, endInput);
                });

                refreshOptions(daySelect, programSelect, slotSelect, startInput, endInput);
            });
        </script>
    @endpush
    @if($errors->has('jadwal'))
        <x-alert type="danger" title="Jadwal bentrok">{{ $errors->first('jadwal') }}</x-alert>
    @endif
</x-app-shell>
