<x-app-layout>
    <div class="flex">
        <x-admin-sidenav />

        <div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
            <div class="mb-4">
                <a href="{{ route('admin.students.index') }}"
                   class="inline-flex items-center text-sm text-gray-700 hover:text-blue-600">
                    <i class="bi bi-arrow-left-circle-fill text-lg mr-1"></i>
                    Kembali
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-xl p-6 max-h-[calc(100vh-100px)] overflow-y-auto">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">Tambah Murid</h1>

                <form action="{{ route('admin.students.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label for="nama_lengkap" class="block text-gray-800 dark:text-gray-200 mb-1">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" id="nama_lengkap"
                            class="w-full rounded border border-gray-300 dark:border-gray-600 px-3 py-2
                                   @error('nama_lengkap') border-red-500 @enderror"
                            value="{{ old('nama_lengkap') }}" required>
                        @error('nama_lengkap')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="nis" class="block text-gray-800 dark:text-gray-200 mb-1">NIS</label>
                        <input type="text" name="nis" id="nis"
                            class="w-full rounded border border-gray-300 dark:border-gray-600 px-3 py-2
                                   @error('nis') border-red-500 @enderror"
                            value="{{ old('nis') }}" required>
                        @error('nis')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="jenis_kelamin" class="block text-gray-800 dark:text-gray-200 mb-1">Jenis Kelamin</label>
                        <select name="jenis_kelamin" id="jenis_kelamin"
                            class="w-full rounded border border-gray-300 dark:border-gray-600 px-3 py-2
                                   @error('jenis_kelamin') border-red-500 @enderror" required>
                            <option value="">Pilih</option>
                            <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        @error('jenis_kelamin')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="kelas_akademik" class="block text-gray-800 dark:text-gray-200 mb-1">Kelas Akademik</label>
                        <select name="kelas_akademik" id="kelas_akademik"
                            class="w-full rounded border border-gray-300 dark:border-gray-600 px-3 py-2
                                   @error('kelas_akademik') border-red-500 @enderror">
                            <option value="">Tidak Ada</option>
                            @foreach($academicClasses as $class)
                                <option value="{{ $class->id }}" {{ old('kelas_akademik') == $class->id ? 'selected' : '' }}>
                                    {{ $class->nama_kelas }} ({{ $class->tahun_ajaran }})
                                </option>
                            @endforeach
                        </select>
                        @error('kelas_akademik')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="kelas_muadalah" class="block text-gray-800 dark:text-gray-200 mb-1">Kelas Muadalah</label>
                        <select name="kelas_muadalah" id="kelas_muadalah"
                            class="w-full rounded border border-gray-300 dark:border-gray-600 px-3 py-2
                                   @error('kelas_muadalah') border-red-500 @enderror">
                            <option value="">Tidak Ada</option>
                            @foreach($muadalahClasses as $class)
                                <option value="{{ $class->id }}" {{ old('kelas_muadalah') == $class->id ? 'selected' : '' }}>
                                    {{ $class->nama_kelas }} ({{ $class->tahun_ajaran }})
                                </option>
                            @endforeach
                        </select>
                        @error('kelas_muadalah')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-4 mt-6">
                        <button type="submit"
                            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            Simpan
                        </button>
                        <a href="{{ route('admin.students.index') }}"
                            class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
