<x-app-layout>
    <div class="flex">
        <x-admin-sidenav />

        <div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
            <div class="mb-4">
                <a href="{{ route('subjects.index') }}" class="inline-flex items-center text-sm text-gray-700 hover:text-blue-600">
                    <i class="bi bi-arrow-left-circle-fill text-lg mr-1"></i>
                    Kembali
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-xl p-6 max-h-[calc(100vh-100px)] overflow-y-auto">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">Edit Mata Pelajaran</h1>

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

                <form action="{{ route('subjects.update', $subject->id) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="nama_mapel" class="block text-gray-800 dark:text-gray-200">Nama Mapel</label>
                        <input type="text" name="nama_mapel" id="nama_mapel"
                            class="w-full rounded border-gray-300 @error('nama_mapel') border-red-500 @enderror"
                            value="{{ old('nama_mapel', $subject->nama_mapel) }}" required>
                        @error('nama_mapel')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="kode_mapel" class="block text-gray-800 dark:text-gray-200">Kode Mapel</label>
                        <input type="text" name="kode_mapel" id="kode_mapel"
                            class="w-full rounded border-gray-300 @error('kode_mapel') border-red-500 @enderror"
                            value="{{ old('kode_mapel', $subject->kode_mapel) }}" required>
                        @error('kode_mapel')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="jenis_mapel" class="block text-gray-800 dark:text-gray-200">Jenis Mapel</label>
                        <select name="jenis_mapel" id="jenis_mapel"
                            class="w-full rounded border-gray-300 @error('jenis_mapel') border-red-500 @enderror" required>
                            <option value="">-- Pilih Jenis --</option>
                            <option value="akademik" {{ old('jenis_mapel', $subject->jenis_mapel) == 'akademik' ? 'selected' : '' }}>Akademik</option>
                            <option value="muadalah" {{ old('jenis_mapel', $subject->jenis_mapel) == 'muadalah' ? 'selected' : '' }}>Muadalah</option>
                        </select>
                        @error('jenis_mapel')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-4 mt-6">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            Update
                        </button>
                        <a href="{{ route('subjects.index') }}"
                            class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">
                            Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
