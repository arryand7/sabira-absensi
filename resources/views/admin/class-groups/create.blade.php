<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidenav />
    </x-slot>

    <div class="flex">
        <div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">
            <div class="mb-4">
                <a href="{{ route('admin.class-groups.index') }}" class="inline-flex items-center text-sm text-gray-700 hover:text-blue-600">
                    <i class="bi bi-arrow-left-circle-fill text-lg mr-1"></i>
                    Kembali
                </a>
            </div>

            <div class="bg-[#8D9382] shadow rounded-2xl p-6 max-h-[calc(100vh-100px)] overflow-y-auto text-[#1C1E17]">
                <h1 class="text-2xl font-bold mb-4">Tambah Kelas</h1>

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

                    {{-- Nama Kelas --}}
                    <div>
                        <label class="block font-semibold mb-1" for="nama_kelas">Nama Kelas</label>
                        <input type="text" name="nama_kelas" id="nama_kelas"
                            class="w-full rounded border-gray-300 p-2 bg-[#EEF3E9] text-[#1C1E17] @error('nama_kelas') border-red-500 @enderror"
                            required>
                        @error('nama_kelas')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Jenis Kelas --}}
                    <div>
                        <label class="block font-semibold mb-1" for="jenis_kelas">Jenis Kelas</label>
                        <select name="jenis_kelas" id="jenis_kelas"
                            class="w-full rounded border-gray-300 p-2 bg-[#EEF3E9] text-[#1C1E17] @error('jenis_kelas') border-red-500 @enderror"
                            required>
                            <option value="">Pilih Jenis</option>
                            <option value="formal" {{ old('jenis_kelas') == 'formal' ? 'selected' : '' }}>Formal</option>
                            <option value="muadalah" {{ old('jenis_kelas') == 'muadalah' ? 'selected' : '' }}>Muadalah</option>
                            <option value="tambahan" {{ old('jenis_kelas') == 'tambahan' ? 'selected' : '' }}>Tambahan</option>
                        </select>
                        @error('jenis_kelas')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tahun Ajaran --}}
                    <div>
                        <label class="block font-semibold mb-1" for="academic_year_id">Tahun Ajaran</label>
                        <select name="academic_year_id" id="academic_year_id"
                            class="w-full rounded border-gray-300 p-2 bg-[#EEF3E9] text-[#1C1E17] @error('academic_year_id') border-red-500 @enderror"
                            required>
                            <option value="">-- Pilih Tahun Ajaran --</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                                    {{ $year->name}}
                                </option>
                            @endforeach
                        </select>
                        @error('academic_year_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Wali Kelas --}}
                    <div>
                        <label class="block font-semibold mb-1" for="wali_kelas_id">Wali Kelas</label>
                        <select name="wali_kelas_id" id="wali_kelas_id"
                            class="w-full rounded border-gray-300 p-2 bg-[#EEF3E9] text-[#1C1E17] @error('wali_kelas_id') border-red-500 @enderror">
                            <option value="">-- Pilih Wali Kelas --</option>
                            @foreach($gurus as $guru)
                                <option value="{{ $guru->id }}" {{ old('wali_kelas_id') == $guru->id ? 'selected' : '' }}>
                                    {{ $guru->user->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('wali_kelas_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tombol Simpan --}}
                    <div class="pt-2">
                        <button type="submit"
                            class="bg-[#8E412E] text-white px-4 py-2 rounded-md text-xs hover:bg-[#BA6F4D] shadow inline-flex items-center gap-1">
                            <i class="bi bi-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
