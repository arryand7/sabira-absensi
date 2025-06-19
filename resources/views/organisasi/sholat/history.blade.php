<x-app-layout>
        <h2 class="font-semibold text-xl text-[#292D22]">
            Rekap Absensi Sholat
        </h2>

    <div class="py-4 max-w-5xl mx-auto sm:px-6 lg:px-4">
        <div class="bg-[#EFF0ED] shadow rounded-xl p-6 overflow-x-auto border border-[#D6D8D2]">
            <!-- Filter Bulan dan Tahun -->
            <form method="GET" action="{{ route('asrama.sholat.history') }}" class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="bulan" class="block text-sm text-[#3C3F33] mb-1">Bulan</label>
                    <select name="bulan" id="bulan"
                        class="w-full border border-[#C8CEC0] rounded px-3 py-2 bg-white focus:ring-2 focus:ring-[#5C644C]">
                        @foreach(range(1, 12) as $bln)
                            <option value="{{ $bln }}" {{ $bulan == $bln ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($bln)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="tahun" class="block text-sm text-[#3C3F33] mb-1">Tahun</label>
                    <select name="tahun" id="tahun"
                        class="w-full border border-[#C8CEC0] rounded px-3 py-2 bg-white focus:ring-2 focus:ring-[#5C644C]">
                        @foreach(range(now()->year - 5, now()->year + 1) as $thn)
                            <option value="{{ $thn }}" {{ $tahun == $thn ? 'selected' : '' }}>
                                {{ $thn }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit"
                        class="bg-[#5C644C] hover:bg-[#4A5240] text-white font-semibold px-4 py-2 rounded w-full transition">
                        Tampilkan
                    </button>
                </div>
            </form>

            <!-- Livewire Component -->
            <livewire:rekap-sholat :bulan="$bulan" :tahun="$tahun" />
        </div>
    </div>
</x-app-layout>
