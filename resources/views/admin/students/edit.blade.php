<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidenav />
    </x-slot>

    <div class="flex">
        <div class="mt-2 w-full sm:px-6 lg:px-8 space-y-6">

            <div class="bg-[#8D9382] shadow-md rounded-xl p-6 max-h-[calc(100vh-100px)] overflow-y-auto">
                <h1 class="text-2xl font-bold text-white mb-4">Edit Murid</h1>

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

                <form action="{{ route('admin.students.update', $student->id) }}" method="POST" class="space-y-4 text-[#1C1E17]">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="nama_lengkap" class="block font-medium mb-1">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" id="nama_lengkap"
                            class="w-full rounded border border-gray-300 px-3 py-2 bg-[#EEF3E9] text-[#1C1E17]
                                   @error('nama_lengkap') border-red-500 @enderror"
                            value="{{ old('nama_lengkap', $student->nama_lengkap) }}" required>
                        @error('nama_lengkap')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="nis" class="block font-medium mb-1">NIS</label>
                        <input type="text" name="nis" id="nis"
                            class="w-full rounded border border-gray-300 px-3 py-2 bg-[#EEF3E9] text-[#1C1E17]
                                   @error('nis') border-red-500 @enderror"
                            value="{{ old('nis', $student->nis) }}" required>
                        @error('nis')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="jenis_kelamin" class="block font-medium mb-1">Jenis Kelamin</label>
                        <select name="jenis_kelamin" id="jenis_kelamin"
                            class="w-full rounded border border-gray-300 px-3 py-2 bg-[#EEF3E9] text-[#1C1E17]
                                   @error('jenis_kelamin') border-red-500 @enderror" required>
                            <option value="">Pilih</option>
                            <option value="L" {{ old('jenis_kelamin', $student->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('jenis_kelamin', $student->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        @error('jenis_kelamin')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="kelas_formal" class="block font-medium mb-1">Kelas Reguler</label>
                        <select name="kelas_formal" id="kelas_formal"
                            class="w-full rounded border border-gray-300 px-3 py-2 bg-[#EEF3E9] text-[#1C1E17]
                                   @error('kelas_formal') border-red-500 @enderror">
                            <option value="">Tidak Ada</option>
                            @foreach($academicClasses as $class)
                                <option value="{{ $class->id }}" {{ old('kelas_formal', $kelasFormalId) == $class->id ? 'selected' : '' }}>
                                    {{ $class->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                        @error('kelas_formal')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="kelas_muadalah" class="block font-medium mb-1">Kelas Non-Reguler</label>
                        <select name="kelas_muadalah" id="kelas_muadalah"
                            class="w-full rounded border border-gray-300 px-3 py-2 bg-[#EEF3E9] text-[#1C1E17]
                                   @error('kelas_muadalah') border-red-500 @enderror">
                            <option value="">Tidak Ada</option>
                            @foreach($muadalahClasses as $class)
                                <option value="{{ $class->id }}" {{ old('kelas_muadalah', $kelasMuadalahId) == $class->id ? 'selected' : '' }}>
                                    {{ $class->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                        @error('kelas_muadalah')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-4 mt-6">
                        <button type="submit"
                            class="bg-[#8E412E] hover:bg-[#BA6F4D] text-white px-4 py-2 rounded-md text-xs shadow inline-flex items-center">
                            <i class="bi bi-save-fill mr-1"></i> Simpan Perubahan
                        </button>
                        <a href="{{ route('admin.students.index') }}"
                            class="bg-gray-300 hover:bg-gray-400 text-[#1C1E17] px-4 py-2 rounded-md text-xs shadow inline-flex items-center">
                            <i class="bi bi-x-circle-fill mr-1"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
