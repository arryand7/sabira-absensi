<x-app-shell>
<h2 class="font-semibold text-xl text-[var(--sabira-ink)] mb-6">
        Duplikat Kelas ke Tahun Ajaran Baru
    </h2>

    <div class="bg-white p-6 rounded shadow w-full max-w-xl">
        <form method="POST" action="{{ route('admin.class-groups.duplicate') }}">
            @csrf
            <div class="mb-4">
                <label class="block font-medium">Tahun Ajaran Asal</label>
                <select name="source_year" class="w-full rounded border-gray-300">
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="block font-medium">Tahun Ajaran Tujuan</label>
                <select name="target_year" class="w-full rounded border-gray-300">
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="bg-[var(--sabira-primary)] text-white px-4 py-2 rounded hover:bg-[var(--sabira-primary-active)]">
                Duplikat Sekarang
            </button>
        </form>
    </div>
</x-app-shell>
