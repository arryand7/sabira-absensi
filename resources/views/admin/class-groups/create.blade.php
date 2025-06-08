<x-app-layout>
    <div class="flex">
        <x-admin-sidenav />

        <div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
            <div class="mb-4">
                <a href="{{ route('admin.class-groups.index') }}" class="inline-flex items-center text-sm text-gray-700 hover:text-blue-600">
                    <i class="bi bi-arrow-left-circle-fill text-lg mr-1"></i>
                    Kembali
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-xl p-6 max-h-[calc(100vh-100px)] overflow-y-auto">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">Tambah Kelas</h1>

                @if ($errors->any())
                    <div class="text-red-600 mb-4">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.class-groups.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-gray-800 dark:text-gray-200 font-semibold mb-1">Nama Kelas</label>
                        <input type="text" name="nama_kelas" class="w-full rounded border-gray-300 dark:border-gray-600 p-2 @error('nama_kelas') border-red-500 @enderror" required>
                        @error('nama_kelas') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-gray-800 dark:text-gray-200 font-semibold mb-1">Jenis Kelas</label>
                        <select name="jenis_kelas" class="w-full rounded border-gray-300 dark:border-gray-600 p-2 @error('jenis_kelas') border-red-500 @enderror" required>
                            <option value="">Pilih Jenis</option>
                            <option value="akademik" {{ old('jenis_kelas') == 'akademik' ? 'selected' : '' }}>Akademik</option>
                            <option value="muadalah" {{ old('jenis_kelas') == 'muadalah' ? 'selected' : '' }}>Muadalah</option>
                        </select>
                        @error('jenis_kelas') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-gray-800 dark:text-gray-200 font-semibold mb-1">Tahun Ajaran</label>
                        <input type="text" name="tahun_ajaran" class="w-full rounded border-gray-300 dark:border-gray-600 p-2 @error('tahun_ajaran') border-red-500 @enderror" placeholder="Contoh: 2024/2025" required>
                        @error('tahun_ajaran') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="wali_kelas_id" class="block text-gray-800 dark:text-gray-200 font-semibold mb-1">Wali Kelas</label>
                        <select name="wali_kelas_id" id="wali_kelas_id" class="w-full rounded border-gray-300 dark:border-gray-600 p-2 shadow-sm @error('wali_kelas_id') border-red-500 @enderror">
                            <option value="">-- Pilih Wali Kelas --</option>
                            @foreach($gurus as $guru)
                                <option value="{{ $guru->id }}" {{ old('wali_kelas_id') == $guru->id ? 'selected' : '' }}>
                                    {{ $guru->user->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('wali_kelas_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                        Simpan
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
