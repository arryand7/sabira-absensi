<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidenav />
    </x-slot>

    <div class="flex">
        <div class="mt-6 w-full sm:px-6 lg:px-8 space-y-6">

            <div class="bg-[#8D9382] shadow-md rounded-2xl p-6 max-h-[calc(100vh-100px)] overflow-y-auto">
                <h1 class="text-2xl font-bold text-[#1C1E17] mb-4">Edit Mata Pelajaran</h1>

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
                        <label for="nama_mapel" class="block text-sm font-medium text-[#1C1E17]">Nama Mapel</label>
                        <input type="text" name="nama_mapel" id="nama_mapel"
                            class="w-full rounded-md border-gray-300 bg-[#EEF3E9] text-[#1C1E17] shadow-sm @error('nama_mapel') border-red-500 @enderror"
                            value="{{ old('nama_mapel', $subject->nama_mapel) }}" required>
                        @error('nama_mapel')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="kode_mapel" class="block text-sm font-medium text-[#1C1E17]">Kode Mapel</label>
                        <input type="text" name="kode_mapel" id="kode_mapel"
                            class="w-full rounded-md border-gray-300 bg-[#EEF3E9] text-[#1C1E17] shadow-sm @error('kode_mapel') border-red-500 @enderror"
                            value="{{ old('kode_mapel', $subject->kode_mapel) }}" required>
                        @error('kode_mapel')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="jenis_mapel" class="block text-sm font-medium text-[#1C1E17]">Jenis Mapel</label>
                        <select name="jenis_mapel" id="jenis_mapel"
                            class="w-full rounded-md border-gray-300 bg-[#EEF3E9] text-[#1C1E17] shadow-sm @error('jenis_mapel') border-red-500 @enderror"
                            required>
                            <option value="">-- Pilih Jenis --</option>
                            <option value="formal" {{ old('jenis_mapel', $subject->jenis_mapel) == 'formal' ? 'selected' : '' }}>Formal</option>
                            <option value="muadalah" {{ old('jenis_mapel', $subject->jenis_mapel) == 'muadalah' ? 'selected' : '' }}>Muadalah</option>
                        </select>
                        @error('jenis_mapel')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-4 mt-6">
                        <button type="submit"
                            class="bg-[#8E412E] text-white px-4 py-2 rounded-md text-xs hover:bg-[#BA6F4D] shadow inline-flex items-center gap-2">
                            <i class="bi bi-check-circle"></i> Update
                        </button>
                        <a href="{{ route('subjects.index') }}"
                            class="bg-[#D9D9D9] text-[#1C1E17] px-4 py-2 rounded-md text-xs hover:bg-[#BFBFBF] shadow inline-flex items-center gap-2">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
