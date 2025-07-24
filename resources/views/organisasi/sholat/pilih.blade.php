<x-user-layout>
    <div class="min-h-screen bg-[#F7F7F6] text-[#1C1E17] p-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-[#292D22]">Pilih Jenis Sholat</h1>

            <a href="{{ route('asrama.index') }}"
            class="inline-flex items-center gap-2 px-4 py-2 bg-[#5C644C] hover:bg-[#535A44] text-white rounded-md text-sm shadow transition">
                <i class="bi bi-arrow-left-circle"></i> Kembali
            </a>
        </div>


        <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
            @foreach($dataSholat as $data)
                @php
                    $sholat = $data['sholat'];
                    $jadwal = $data['jadwal'];
                    $sudahAbsenSemua = $data['sudahAbsenSemua'];
                @endphp

                <a href="{{ !$sudahAbsenSemua ? route('asrama.sholat.form', ['jenis' => strtolower($sholat->nama)]) : '#' }}"
                    class="p-5 rounded-xl border shadow text-center transition
                            {{ (!$jadwal || !$sudahAbsenSemua)
                                ? 'bg-[#EFF0ED] hover:bg-[#E3E4DF] border-[#D6D8D2] text-[#5C644C]'
                                : 'bg-[#F7F7F6] border-[#D6D8D2] opacity-50 cursor-not-allowed pointer-events-none text-[#8D9382]' }}">

                    <div class="text-3xl mb-2">
                        <i class="bi bi-moon-fill"></i>
                    </div>
                    <h2 class="text-lg font-semibold capitalize">{{ $sholat->nama }}</h2>
                    @if ($jadwal)
                        <p class="text-sm mt-1">
                            {{ $jadwal->jam_mulai . ' - ' . $jadwal->jam_selesai }}
                        </p>
                    @else
                        <p class="text-sm mt-1 text-gray-500 italic">Belum dimulai</p>
                    @endif

                    @if($sudahAbsenSemua)
                        <p class="text-xs text-red-500 mt-2 font-medium">Sudah absen</p>
                    @endif
                </a>
            @endforeach
        </div>
    </div>

    {{-- Bootstrap Icons CDN (jika belum ada di layout utama) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</x-user-layout>
