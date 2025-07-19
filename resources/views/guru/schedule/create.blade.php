<x-app-layout>
    <div class="flex">
        <div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
            <div class="mb-4">
                <a href="{{ route('guru.schedule.index') }}" class="inline-flex items-center text-sm text-[#1C1E17] hover:text-blue-600">
                    <i class="bi bi-arrow-left-circle-fill text-lg mr-1"></i> Kembali
                </a>
            </div>

            <div class="bg-[#8D9382] shadow rounded-xl p-6 max-h-[calc(100vh-100px)] overflow-y-auto">
                <h2 class="text-2xl font-bold text-[#1C1E17] mb-4">Tambah Jadwal</h2>

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
                        <label for="user_id" class="block font-semibold mb-1 text-[#1C1E17]">Guru</label>
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
                        <label for="subject_id" class="block font-semibold mb-1 text-[#1C1E17]">Mata Pelajaran</label>
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
                        <label class="block font-semibold mb-2 text-[#1C1E17]">Jadwal</label>
                        <div id="schedule-rows-container" class="space-y-4">
                            @php $oldDetails = old('details', [0 => []]); @endphp
                            @foreach ($oldDetails as $i => $detail)
                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 schedule-row">
                                    <div>
                                        <select name="details[{{ $i }}][hari]" class="form-select mt-1">
                                            <option value="">-- Hari --</option>
                                            @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Ahad'] as $hari)
                                                <option value="{{ $hari }}" {{ old("details.$i.hari", $detail['hari'] ?? '') == $hari ? 'selected' : '' }}>
                                                    {{ $hari }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <input type="time" name="details[{{ $i }}][jam_mulai]" class="form-input mt-1"
                                            value="{{ old("details.$i.jam_mulai", $detail['jam_mulai'] ?? '') }}" />
                                    </div>
                                    <div>
                                        <input type="time" name="details[{{ $i }}][jam_selesai]" class="form-input mt-1"
                                            value="{{ old("details.$i.jam_selesai", $detail['jam_selesai'] ?? '') }}" />
                                    </div>
                                    <div class="flex gap-2 items-center">
                                        <select name="details[{{ $i }}][class_group_id]" class="form-select mt-1 w-full">
                                            <option value="">-- Kelas --</option>
                                            @foreach($classGroups as $group)
                                                <option value="{{ $group->id }}"
                                                    {{ old("details.$i.class_group_id", $detail['class_group_id'] ?? '') == $group->id ? 'selected' : '' }}>
                                                    {{ $group->nama_kelas }} ({{ ucfirst($group->jenis_kelas) }})
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
                        <label for="academic_year_id" class="block font-semibold mb-1 text-[#1C1E17]">Tahun Ajaran</label>
                        <select name="academic_year_id" class="form-select" required>
                            @foreach ($academicYears as $year)
                                <option value="{{ $year->id }}" {{ old('academic_year_id', $tahunAktif?->id) == $year->id ? 'selected' : '' }}>
                                    {{ $year->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('academic_year_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Submit --}}
                    <div>
                        <button type="submit" class="bg-[#8E412E] hover:bg-[#BA6F4D] text-white px-6 py-2 rounded-md text-xs shadow">
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
            newRow.className = 'grid grid-cols-1 sm:grid-cols-4 gap-4 schedule-row';
            newRow.innerHTML = `
                <div>
                    <select name="details[${rowIndex}][hari]" class="form-select mt-1">
                        <option value="">-- Hari --</option>
                        @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Ahad'] as $hari)
                            <option value="{{ $hari }}">{{ $hari }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <input type="time" name="details[${rowIndex}][jam_mulai]" class="form-input mt-1" />
                </div>
                <div>
                    <input type="time" name="details[${rowIndex}][jam_selesai]" class="form-input mt-1" />
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
            rowIndex++;
        }

        function removeScheduleRow(button) {
            const row = button.closest('.schedule-row');
            row.remove();
        }
    </script>
    @endpush
</x-app-layout>
