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
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">Edit Jadwal Guru</h1>

                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <strong>Ups!</strong> Ada beberapa masalah dengan input kamu.
                        <ul class="mt-2 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.schedules.update', $schedule->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="user_id" class="block text-gray-800 dark:text-gray-200 font-semibold mb-1">Guru</label>
                        <select name="user_id" id="user_id" class="w-full rounded border-gray-300 @error('user_id') border-red-500 @enderror px-3 py-2 focus:ring-blue-500">
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ old('user_id', $schedule->user_id) == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="subject_id" class="block text-gray-800 dark:text-gray-200 font-semibold mb-1">Mata Pelajaran</label>
                        <select name="subject_id" id="subject_id" class="w-full rounded border-gray-300 @error('subject_id') border-red-500 @enderror px-3 py-2 focus:ring-blue-500">
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
                        <label for="class_group_id" class="block text-gray-800 dark:text-gray-200 font-semibold mb-1">Kelas</label>
                        <select name="class_group_id" id="class_group_id" class="w-full rounded border-gray-300 @error('class_group_id') border-red-500 @enderror px-3 py-2 focus:ring-blue-500">
                            @foreach($classGroups as $group)
                                <option value="{{ $group->id }}" {{ old('class_group_id', $schedule->class_group_id) == $group->id ? 'selected' : '' }}>
                                    {{ $group->nama_kelas }} ({{ $group->jenis_kelas }})
                                </option>
                            @endforeach
                        </select>
                        @error('class_group_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-4">
                        <div class="flex-1">
                            <label for="hari" class="block text-gray-800 dark:text-gray-200 font-semibold mb-1">Hari</label>
                            <select name="hari" id="hari" class="w-full rounded border-gray-300 @error('hari') border-red-500 @enderror px-3 py-2 focus:ring-blue-500">
                                @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Ahad'] as $hari)
                                    <option value="{{ $hari }}" {{ old('hari', $schedule->hari) == $hari ? 'selected' : '' }}>{{ $hari }}</option>
                                @endforeach
                            </select>
                            @error('hari')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex-1">
                            <label for="jam_mulai" class="block text-gray-800 dark:text-gray-200 font-semibold mb-1">Jam Mulai</label>
                            <input type="time" name="jam_mulai" id="jam_mulai" value="{{ old('jam_mulai', $schedule->jam_mulai) }}" class="w-full rounded border-gray-300 @error('jam_mulai') border-red-500 @enderror px-3 py-2 focus:ring-blue-500">
                            @error('jam_mulai')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex-1">
                            <label for="jam_selesai" class="block text-gray-800 dark:text-gray-200 font-semibold mb-1">Jam Selesai</label>
                            <input type="time" name="jam_selesai" id="jam_selesai" value="{{ old('jam_selesai', $schedule->jam_selesai) }}" class="w-full rounded border-gray-300 @error('jam_selesai') border-red-500 @enderror px-3 py-2 focus:ring-blue-500">
                            @error('jam_selesai')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex gap-4 mt-6">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                            Update
                        </button>
                        <a href="{{ route('admin.schedules.index') }}" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">
                            Kembali
                        </a>
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
