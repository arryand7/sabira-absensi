<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidenav />
    </x-slot>

    <div class="flex">
        <div class="w-full sm:px-6 lg:px-8 space-y-6">
            <div class="mb-2">
                <a href="{{ url()->previous() }}"
                    class="inline-flex items-center gap-2 text-sm bg-gray-200 hover:bg-gray-300 text-[#1C1E17] px-3 py-1.5 rounded-md shadow-sm transition-all duration-150">
                    <i class="bi bi-arrow-left-circle-fill text-lg"></i> Kembali
                </a>
            </div>

            <div class="bg-[#8D9382] shadow rounded-xl p-8 max-h-[calc(100vh-100px)] overflow-y-auto ring-1 ring-gray-300">
                <h2 class="text-2xl font-bold text-[#1C1E17] mb-6">Tambah Jadwal</h2>

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
                        <label for="user_id" class="block font-semibold text-[#1C1E17]">Guru</label>
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
                        <label for="subject_id" class="block font-semibold text-[#1C1E17]">Mata Pelajaran</label>
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
                        <label class="block font-semibold text-[#1C1E17]">Jadwal</label>
                        <div id="schedule-rows-container" class="space-y-4">
                            @php $oldDetails = old('details', [0 => []]); @endphp
                            @foreach ($oldDetails as $i => $detail)
                                <div class="grid grid-cols-1 sm:grid-cols-5 gap-4 schedule-row">
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
                                            @foreach (range(1, 8) as $slot)
                                                <option value="{{ $slot }}" data-slot-index="{{ $slot }}" {{ old("details.$i.jam_ke") == $slot ? 'selected' : '' }}>
                                                    {{ $slot }}
                                                </option>
                                            @endforeach
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
                                        <select name="details[{{ $i }}][class_group_id]" class="form-select w-full rounded-md border-gray-300 shadow-sm">
                                            <option value="">-- Kelas --</option>
                                            @foreach($classGroups as $group)
                                                <option value="{{ $group->id }}"
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

                        <button type="button" onclick="addScheduleRow()" class="inline-flex items-center gap-2 bg-[#E8EAD8] hover:bg-[#D3D7C3] text-[#1C1E17] text-xs px-3 py-1.5 rounded-md shadow-sm transition">
                            <i class="bi bi-plus-circle-fill"></i> Tambah Jadwal
                        </button>
                    </div>

                    {{-- Tahun Ajaran --}}
                    <div class="space-y-1">
                        <label for="academic_year_id" class="block font-semibold text-[#1C1E17]">Tahun Ajaran</label>
                        <select name="academic_year_id" class="form-select w-full rounded-md border-gray-300 shadow-sm" required>
                            @foreach ($academicYears as $year)
                                <option value="{{ $year->id }}" {{ old('academic_year_id', $selectedYear ?? $tahunAktif?->id) == $year->id ? 'selected' : '' }}>
                                    {{ $year->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('academic_year_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Submit --}}
                    <div class="pt-2">
                        <button type="submit" class="inline-block bg-[#8E412E] hover:bg-[#BA6F4D] text-white text-sm px-5 py-2 rounded-md shadow-sm transition">
                            <i class="bi bi-save-fill mr-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    @if($errors->has('jadwal'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Jadwal Bentrok!',
                text: '{{ $errors->first('jadwal') }}',
            });
        </script>
    @endif
    <script>
        let rowIndex = {{ count($oldDetails) }};

        function addScheduleRow() {
            const container = document.getElementById('schedule-rows-container');
            const newRow = document.createElement('div');
            newRow.className = 'grid grid-cols-1 sm:grid-cols-5 gap-4 schedule-row';
            newRow.innerHTML = `
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
                        @foreach (range(1, 8) as $slot)
                            <option value="{{ $slot }}" data-slot-index="{{ $slot }}">{{ $slot }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <input type="time" name="details[${rowIndex}][jam_mulai]" class="form-input w-full rounded-md border-gray-300 shadow-sm mt-1 schedule-start" />
                </div>
                <div>
                    <input type="time" name="details[${rowIndex}][jam_selesai]" class="form-input w-full rounded-md border-gray-300 shadow-sm mt-1 schedule-end" />
                </div>
                <div class="flex gap-2 items-center">
                    <select name="details[${rowIndex}][class_group_id]" class="form-select w-full rounded-md border-gray-300 shadow-sm mt-1">
                        <option value="">-- Kelas --</option>
                        @foreach($classGroups as $group)
                            <option value="{{ $group->id }}">{{ $group->nama_kelas }} ({{ ucfirst($group->jenis_kelas) }})</option>
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

        const slotTimes = {
            1: { start: '07:15', end: '07:55' },
            2: { start: '07:55', end: '08:35' },
            3: { start: '08:35', end: '09:15' },
            4: { start: '09:15', end: '09:55' },
            5: { start: '10:25', end: '11:05' },
            6: { start: '11:05', end: '11:45' },
            7: { start: '11:45', end: '12:25' },
            8: { start: '12:25', end: '13:05' },
        };

        function getMatchingSlot(start, end, day) {
            for (const [index, slot] of Object.entries(slotTimes)) {
                const slotIndex = parseInt(index, 10);
                if (day === 'Jumat' && slotIndex > 5) {
                    continue;
                }
                if (slot.start === start && slot.end === end) {
                    return index;
                }
            }
            return '';
        }

        function updateFridaySlots(row) {
            const daySelect = row.querySelector('.schedule-day');
            const slotSelect = row.querySelector('.schedule-slot');
            if (!daySelect || !slotSelect) {
                return;
            }

            const isFriday = daySelect.value === 'Jumat';
            const options = Array.from(slotSelect.options);
            options.forEach((option) => {
                const slotIndex = parseInt(option.value, 10);
                if (!Number.isNaN(slotIndex) && slotIndex > 5) {
                    option.disabled = isFriday;
                    option.hidden = isFriday;
                }
            });

            const selectedIndex = parseInt(slotSelect.value, 10);
            if (isFriday && !Number.isNaN(selectedIndex) && selectedIndex > 5) {
                slotSelect.value = '';
            }
        }

        function updateTimesFromSlot(row) {
            const slotSelect = row.querySelector('.schedule-slot');
            const startInput = row.querySelector('.schedule-start');
            const endInput = row.querySelector('.schedule-end');
            if (!slotSelect || !startInput || !endInput) {
                return;
            }

            const selected = slotSelect.value;
            if (!selected || !slotTimes[selected]) {
                return;
            }

            startInput.value = slotTimes[selected].start;
            endInput.value = slotTimes[selected].end;
        }

        function updateSlotFromTimes(row) {
            const daySelect = row.querySelector('.schedule-day');
            const slotSelect = row.querySelector('.schedule-slot');
            const startInput = row.querySelector('.schedule-start');
            const endInput = row.querySelector('.schedule-end');
            if (!daySelect || !slotSelect || !startInput || !endInput) {
                return;
            }

            const match = getMatchingSlot(startInput.value, endInput.value, daySelect.value);
            slotSelect.value = match;
        }

        function initializeScheduleRow(row) {
            const daySelect = row.querySelector('.schedule-day');
            const slotSelect = row.querySelector('.schedule-slot');
            const startInput = row.querySelector('.schedule-start');
            const endInput = row.querySelector('.schedule-end');

            if (!daySelect || !slotSelect || !startInput || !endInput) {
                return;
            }

            daySelect.addEventListener('change', () => {
                updateFridaySlots(row);
                updateSlotFromTimes(row);
            });

            slotSelect.addEventListener('change', () => {
                updateTimesFromSlot(row);
            });

            startInput.addEventListener('change', () => {
                updateSlotFromTimes(row);
            });

            endInput.addEventListener('change', () => {
                updateSlotFromTimes(row);
            });

            updateFridaySlots(row);
            updateSlotFromTimes(row);
        }

        document.querySelectorAll('.schedule-row').forEach((row) => {
            initializeScheduleRow(row);
        });
    </script>
    @endpush
</x-app-layout>
