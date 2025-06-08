<x-app-layout>
    <div class="flex">
        <x-admin-sidenav />

        <div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
            <div class="mb-4">
                <a href="{{ route('admin.schedules.index') }}" class="inline-flex items-center text-sm text-gray-700 hover:text-blue-600">
                    <i class="bi bi-arrow-left-circle-fill text-lg mr-1"></i>
                    Kembali
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-xl p-6 max-h-[calc(100vh-100px)] overflow-y-auto">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">
                    {{ isset($schedule) ? 'Edit Jadwal' : 'Tambah Jadwal' }}
                </h2>

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

                <form action="{{ isset($schedule) ? route('admin.schedules.update', $schedule->id) : route('admin.schedules.store') }}" method="POST" class="space-y-6">
                    @csrf
                    @if(isset($schedule))
                        @method('PUT')
                    @endif

                    @if(isset($selectedGuruId))
                        <input type="hidden" name="user_id" value="{{ $selectedGuruId }}">
                        <p class="mb-4 text-gray-700 dark:text-gray-300"><strong>Guru:</strong> {{ $teachers->firstWhere('id', $selectedGuruId)?->name ?? 'Tidak ditemukan' }}</p>
                    @else
                        <div>
                            <label for="user_id" class="block text-gray-800 dark:text-gray-200 font-semibold mb-1">Guru</label>
                            <select name="user_id" id="user_id" class="w-full rounded border border-gray-300 dark:border-gray-600 px-3 py-2 focus:ring-blue-500 focus:outline-none dark:bg-gray-700 dark:text-gray-200 @error('user_id') border-red-500 @enderror">
                                <option value="">-- Pilih Guru --</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" {{ old('user_id', $schedule->user_id ?? '') == $teacher->id ? 'selected' : '' }}>
                                        {{ $teacher->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <div>
                        <label for="subject_id" class="block text-gray-800 dark:text-gray-200 font-semibold mb-1">Mata Pelajaran</label>
                        <select name="subject_id" id="subject_id" class="w-full rounded border border-gray-300 dark:border-gray-600 px-3 py-2 focus:ring-blue-500 focus:outline-none dark:bg-gray-700 dark:text-gray-200 @error('subject_id') border-red-500 @enderror">
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ old('subject_id', $schedule->subject_id ?? '') == $subject->id ? 'selected' : '' }}>{{ $subject->nama_mapel }}</option>
                            @endforeach
                        </select>
                        @error('subject_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="class_group_id" class="block text-gray-800 dark:text-gray-200 font-semibold mb-1">Kelas</label>
                        <select name="class_group_id" id="class_group_id" class="w-full rounded border border-gray-300 dark:border-gray-600 px-3 py-2 focus:ring-blue-500 focus:outline-none dark:bg-gray-700 dark:text-gray-200 @error('class_group_id') border-red-500 @enderror">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($classGroups as $group)
                                <option value="{{ $group->id }}" {{ old('class_group_id', $schedule->class_group_id ?? '') == $group->id ? 'selected' : '' }}>
                                    {{ $group->nama_kelas }} ({{ ucfirst($group->jenis_kelas) }})
                                </option>
                            @endforeach
                        </select>
                        @error('class_group_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="hari" class="block text-gray-800 dark:text-gray-200 font-semibold mb-1">Hari</label>
                            <select name="hari" id="hari" class="w-full rounded border border-gray-300 dark:border-gray-600 px-3 py-2 focus:ring-blue-500 focus:outline-none dark:bg-gray-700 dark:text-gray-200 @error('hari') border-red-500 @enderror">
                                <option value="">-- Pilih Hari --</option>
                                @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Ahad'] as $hari)
                                    <option value="{{ $hari }}" {{ old('hari', $schedule->hari ?? '') == $hari ? 'selected' : '' }}>{{ $hari }}</option>
                                @endforeach
                            </select>
                            @error('hari')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="jam_mulai" class="block text-gray-800 dark:text-gray-200 font-semibold mb-1">Jam Mulai</label>
                            <input type="time" name="jam_mulai" id="jam_mulai" value="{{ old('jam_mulai', $schedule->jam_mulai ?? '') }}" class="w-full rounded border border-gray-300 dark:border-gray-600 px-3 py-2 focus:ring-blue-500 focus:outline-none dark:bg-gray-700 dark:text-gray-200 @error('jam_mulai') border-red-500 @enderror">
                            @error('jam_mulai')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="jam_selesai" class="block text-gray-800 dark:text-gray-200 font-semibold mb-1">Jam Selesai</label>
                            <input type="time" name="jam_selesai" id="jam_selesai" value="{{ old('jam_selesai', $schedule->jam_selesai ?? '') }}" class="w-full rounded border border-gray-300 dark:border-gray-600 px-3 py-2 focus:ring-blue-500 focus:outline-none dark:bg-gray-700 dark:text-gray-200 @error('jam_selesai') border-red-500 @enderror">
                            @error('jam_selesai')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded">
                            {{ isset($schedule) ? 'Update' : 'Simpan' }}
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
    @endpush

</x-app-layout>
