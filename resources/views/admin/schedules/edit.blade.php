<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidenav />
    </x-slot>

    <div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
        <div class="bg-[#8D9382] shadow-md rounded-xl p-6">
{{--
            <div class="mb-4">
                <a href="{{ route('admin.schedules.index') }}" class="inline-flex items-center text-sm text-[#1C1E17] hover:text-blue-600">
                    <i class="bi bi-arrow-left-circle-fill text-lg mr-1"></i>
                    Kembali ke Jadwal
                </a>
            </div> --}}

            <h2 class="text-2xl font-bold text-[#1C1E17] mb-6">
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
                    <label for="user_id" class="block font-semibold text-[#1C1E17] mb-1">Guru</label>
                    <select name="user_id" id="user_id" class="w-full rounded border border-gray-300 bg-[#EEF3E9] text-[#1C1E17] px-3 py-2 focus:ring-blue-500 focus:outline-none @error('user_id') border-red-500 @enderror">
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ old('user_id', $schedule->user_id) == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="subject_id" class="block font-semibold text-[#1C1E17] mb-1">Mata Pelajaran</label>
                    <select name="subject_id" id="subject_id" class="w-full rounded border border-gray-300 bg-[#EEF3E9] text-[#1C1E17] px-3 py-2 focus:ring-blue-500 focus:outline-none @error('subject_id') border-red-500 @enderror">
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
                    <label for="class_group_id" class="block font-semibold text-[#1C1E17] mb-1">Kelas</label>
                    <select name="class_group_id" id="class_group_id" class="w-full rounded border border-gray-300 bg-[#EEF3E9] text-[#1C1E17] px-3 py-2 focus:ring-blue-500 focus:outline-none @error('class_group_id') border-red-500 @enderror">
                        @foreach($classGroups as $group)
                            <option value="{{ $group->id }}" {{ old('class_group_id', $schedule->class_group_id) == $group->id ? 'selected' : '' }}>
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
                        <label for="hari" class="block font-semibold text-[#1C1E17] mb-1">Hari</label>
                        <select name="hari" id="hari" class="w-full rounded border border-gray-300 bg-[#EEF3E9] text-[#1C1E17] px-3 py-2 focus:ring-blue-500 focus:outline-none schedule-day @error('hari') border-red-500 @enderror">
                            @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Ahad'] as $hari)
                                <option value="{{ $hari }}" {{ old('hari', $schedule->hari) == $hari ? 'selected' : '' }}>{{ $hari }}</option>
                            @endforeach
                        </select>
                        @error('hari')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="jam_ke" class="block font-semibold text-[#1C1E17] mb-1">Jam ke</label>
                        <select name="jam_ke" id="jam_ke" class="w-full rounded border border-gray-300 bg-[#EEF3E9] text-[#1C1E17] px-3 py-2 focus:ring-blue-500 focus:outline-none schedule-slot">
                            <option value="">-- Jam ke --</option>
                            @foreach (range(1, 8) as $slot)
                                <option value="{{ $slot }}" data-slot-index="{{ $slot }}" {{ old('jam_ke') == $slot ? 'selected' : '' }}>
                                    {{ $slot }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="jam_mulai" class="block font-semibold text-[#1C1E17] mb-1">Jam Mulai</label>
                        <input type="time" name="jam_mulai" id="jam_mulai" value="{{ old('jam_mulai', $schedule->jam_mulai) }}" class="w-full rounded border border-gray-300 bg-[#EEF3E9] text-[#1C1E17] px-3 py-2 focus:ring-blue-500 focus:outline-none schedule-start @error('jam_mulai') border-red-500 @enderror">
                        @error('jam_mulai')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="jam_selesai" class="block font-semibold text-[#1C1E17] mb-1">Jam Selesai</label>
                        <input type="time" name="jam_selesai" id="jam_selesai" value="{{ old('jam_selesai', $schedule->jam_selesai) }}" class="w-full rounded border border-gray-300 bg-[#EEF3E9] text-[#1C1E17] px-3 py-2 focus:ring-blue-500 focus:outline-none schedule-end @error('jam_selesai') border-red-500 @enderror">
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

                <div class="flex gap-4 mt-6">
                    <button type="submit" class="bg-[#8E412E] hover:bg-[#BA6F4D] text-white px-4 py-2 rounded shadow text-sm">
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

            function updateFridayOptions(daySelect, slotSelect) {
                if (!daySelect || !slotSelect) {
                    return;
                }

                const isFriday = daySelect.value === 'Jumat';
                Array.from(slotSelect.options).forEach((option) => {
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

            function updateTimesFromSlot(slotSelect, startInput, endInput) {
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

            function updateSlotFromTimes(daySelect, slotSelect, startInput, endInput) {
                if (!daySelect || !slotSelect || !startInput || !endInput) {
                    return;
                }

                const match = getMatchingSlot(startInput.value, endInput.value, daySelect.value);
                slotSelect.value = match;
            }

            document.addEventListener('DOMContentLoaded', () => {
                const daySelect = document.getElementById('hari');
                const slotSelect = document.getElementById('jam_ke');
                const startInput = document.getElementById('jam_mulai');
                const endInput = document.getElementById('jam_selesai');

                if (!daySelect || !slotSelect || !startInput || !endInput) {
                    return;
                }

                daySelect.addEventListener('change', () => {
                    updateFridayOptions(daySelect, slotSelect);
                    updateSlotFromTimes(daySelect, slotSelect, startInput, endInput);
                });

                slotSelect.addEventListener('change', () => {
                    updateTimesFromSlot(slotSelect, startInput, endInput);
                });

                startInput.addEventListener('change', () => {
                    updateSlotFromTimes(daySelect, slotSelect, startInput, endInput);
                });

                endInput.addEventListener('change', () => {
                    updateSlotFromTimes(daySelect, slotSelect, startInput, endInput);
                });

                updateFridayOptions(daySelect, slotSelect);
                updateSlotFromTimes(daySelect, slotSelect, startInput, endInput);
            });
        </script>
    @endpush
</x-app-layout>
