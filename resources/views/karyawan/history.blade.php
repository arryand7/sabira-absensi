<x-user-layout>
    <!-- Header: Judul + Tombol Kembali -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap justify-between items-center gap-4 py-2">
            <h2 class="text-lg sm:text-xl font-semibold text-gray-800 leading-tight">
                Riwayat Absensi
            </h2>
            <a href="{{ url()->previous() }}"
               class="bg-[#8E412E] text-white px-4 py-2 rounded-md text-sm sm:text-base hover:bg-[#7A3827] transition">
                ← Kembali
            </a>
        </div>
    </div>

    <!-- Konten -->
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
</x-user-layout>
