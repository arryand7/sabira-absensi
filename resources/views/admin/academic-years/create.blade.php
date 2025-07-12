<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidenav />
    </x-slot>

    <div class="flex">
        <div class="mt-2 w-full sm:px-6 lg:px-8 space-y-6">
            <div class="bg-[#8D9382] shadow-md rounded-xl p-6 max-h-[calc(100vh-100px)] overflow-y-auto text-[#1C1E17]">
                <h1 class="text-2xl font-bold mb-4">
                    {{ isset($academicYear) ? 'Edit' : 'Tambah' }} Tahun Ajaran
                </h1>

                @if ($errors->any())
                    <div class="bg-red-200 text-red-800 p-3 rounded mb-4 text-sm">
                        <ul class="list-disc pl-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('success'))
                    <div class="bg-green-200 text-green-800 p-3 rounded mb-4 text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ isset($academicYear) ? route('academic-years.update', $academicYear) : route('academic-years.store') }}" class="space-y-4">
                    @csrf
                    @if (isset($academicYear)) @method('PUT') @endif

                    <div>
                        <label for="name" class="block font-medium mb-1">Nama Tahun Ajaran</label>
                        <input type="text" name="name" id="name"
                            class="w-full rounded border border-gray-300 px-3 py-2 bg-[#EEF3E9]
                                   @error('name') border-red-500 @enderror"
                            value="{{ old('name', $academicYear->name ?? '') }}" required>
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="start_date" class="block font-medium mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" id="start_date"
                            class="w-full rounded border border-gray-300 px-3 py-2 bg-[#EEF3E9]
                                   @error('start_date') border-red-500 @enderror"
                            value="{{ old('start_date', $academicYear->start_date ?? '') }}" required>
                        @error('start_date')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_date" class="block font-medium mb-1">Tanggal Selesai</label>
                        <input type="date" name="end_date" id="end_date"
                            class="w-full rounded border border-gray-300 px-3 py-2 bg-[#EEF3E9]
                                   @error('end_date') border-red-500 @enderror"
                            value="{{ old('end_date', $academicYear->end_date ?? '') }}" required>
                        @error('end_date')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" id="is_active"
                            class="accent-[#8E412E]" {{ old('is_active', $academicYear->is_active ?? false) ? 'checked' : '' }}>
                        <label for="is_active" class="text-sm">Jadikan Aktif</label>
                    </div>

                    <div class="flex gap-4 mt-6">
                        <button type="submit"
                            class="bg-[#8E412E] text-white px-4 py-2 rounded-md text-xs hover:bg-[#BA6F4D] shadow">
                            <i class="bi bi-save"></i> {{ isset($academicYear) ? 'Update' : 'Simpan' }}
                        </button>
                        <a href="{{ route('academic-years.index') }}"
                            class="bg-[#8E412E] text-white px-4 py-2 rounded-md text-xs hover:bg-[#BA6F4D] shadow inline-flex items-center">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
