<x-app-shell>
<div class="bg-[var(--sabira-neutral-strong)] shadow-md rounded-xl p-6 max-h-[calc(100vh-100px)] overflow-y-auto text-[var(--sabira-ink)]">
        <h2 class="text-2xl font-bold text-[var(--sabira-ink)] mb-6">Edit Tahun Ajaran</h2>

        @if ($errors->any())
            <div class="mb-4 text-red-600">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('academic-years.update', $academicYear->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block font-medium text-gray-700">Nama Tahun Ajaran</label>
                <input type="text" name="name" value="{{ old('name', $academicYear->name) }}" class="w-full border-gray-300 rounded-md shadow-sm" required>
            </div>

            <div>
                <label class="block font-medium text-gray-700">Tanggal Mulai</label>
                <input type="date" name="start_date"
                    value="{{ old('start_date', \Carbon\Carbon::parse($academicYear->start_date)->format('Y-m-d')) }}"
                    class="w-full border-gray-300 rounded-md shadow-sm" required>
            </div>

            <div>
                <label class="block font-medium text-gray-700">Tanggal Selesai</label>
                <input type="date" name="end_date"
                    value="{{ old('end_date', \Carbon\Carbon::parse($academicYear->end_date)->format('Y-m-d')) }}"
                    class="w-full border-gray-300 rounded-md shadow-sm" required>
            </div>


            <div class="flex items-center space-x-2">
                <input type="checkbox" name="is_active" value="1" {{ $academicYear->is_active ? 'checked' : '' }} id="is_active">
                <label for="is_active" class="text-gray-700">Jadikan Tahun Ajaran Aktif</label>
            </div>

            {{-- <div class="flex justify-end">
                <a href="{{ route('academic-years.index') }}" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded shadow">Batal</a>
                <button type="submit" class="ml-2 px-4 py-2 bg-[var(--sabira-primary)] hover:bg-[var(--sabira-primary-active)] text-white rounded shadow">Simpan Perubahan</button>
            </div> --}}
            <div class="flex gap-4 mt-6">
                        <button type="submit"
                            class="bg-[var(--sabira-primary)] text-white px-4 py-2 rounded-md text-xs hover:bg-[var(--sabira-primary-active)] shadow">
                            <i class="bi bi-save"></i> Simpan Perubahan
                        </button>
                        <a href="{{ route('academic-years.index') }}"
                            class="bg-[var(--sabira-primary)] text-white px-4 py-2 rounded-md text-xs hover:bg-[var(--sabira-primary-active)] shadow inline-flex items-center">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
        </form>
    </div>
</x-app-shell>
