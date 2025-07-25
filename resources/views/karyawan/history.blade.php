<x-app-layout>
    
    <div class="px-2 py-2">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm text-gray-700 hover:text-blue-600">
            <i class="bi bi-arrow-left-circle me-1 text-lg"></i> Kembali
        </a>
    </div>

    <div class="py-4 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Filter Form -->
        <form method="GET" class="mb-6 flex flex-col sm:flex-row sm:flex-wrap gap-4 bg-[#BEC1B7] p-4 rounded-md border border-[#8D9382]">
            <div class="flex-1 min-w-[140px]">
                <label class="block text-sm font-medium text-[#1C1E17] mb-1">Bulan</label>
                <select name="bulan" class="w-full border border-[#8D9382] rounded px-3 py-2 bg-white text-[#1C1E17]">
                    @foreach(range(1, 12) as $bln)
                        <option value="{{ $bln }}" {{ $bulan == $bln ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($bln)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex-1 min-w-[140px]">
                <label class="block text-sm font-medium text-[#1C1E17] mb-1">Tahun</label>
                <select name="tahun" class="w-full border border-[#8D9382] rounded px-3 py-2 bg-white text-[#1C1E17]">
                    @foreach(range(now()->year - 3, now()->year + 1) as $thn)
                        <option value="{{ $thn }}" {{ $tahun == $thn ? 'selected' : '' }}>{{ $thn }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mt-2 sm:mt-6">
                <button type="submit" class="w-full sm:w-auto bg-[#5C644C] text-white px-4 py-2 rounded hover:bg-[#535A44] transition">
                    Tampilkan
                </button>
            </div>
        </form>

        <!-- Kalender Absensi -->
        <livewire:kalender-absensi :bulan="$bulan" :tahun="$tahun" />
    </div>
</x-app-layout>
