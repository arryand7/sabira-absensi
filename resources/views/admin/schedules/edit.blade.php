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

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="hari" class="block font-semibold text-[#1C1E17] mb-1">Hari</label>
                        <select name="hari" id="hari" class="w-full rounded border border-gray-300 bg-[#EEF3E9] text-[#1C1E17] px-3 py-2 focus:ring-blue-500 focus:outline-none @error('hari') border-red-500 @enderror">
                            @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Ahad'] as $hari)
                                <option value="{{ $hari }}" {{ old('hari', $schedule->hari) == $hari ? 'selected' : '' }}>{{ $hari }}</option>
                            @endforeach
                        </select>
                        @error('hari')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="jam_mulai" class="block font-semibold text-[#1C1E17] mb-1">Jam Mulai</label>
                        <input type="time" name="jam_mulai" id="jam_mulai" value="{{ old('jam_mulai', $schedule->jam_mulai) }}" class="w-full rounded border border-gray-300 bg-[#EEF3E9] text-[#1C1E17] px-3 py-2 focus:ring-blue-500 focus:outline-none @error('jam_mulai') border-red-500 @enderror">
                        @error('jam_mulai')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="jam_selesai" class="block font-semibold text-[#1C1E17] mb-1">Jam Selesai</label>
                        <input type="time" name="jam_selesai" id="jam_selesai" value="{{ old('jam_selesai', $schedule->jam_selesai) }}" class="w-full rounded border border-gray-300 bg-[#EEF3E9] text-[#1C1E17] px-3 py-2 focus:ring-blue-500 focus:outline-none @error('jam_selesai') border-red-500 @enderror">
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
    @endpush
</x-app-layout>
