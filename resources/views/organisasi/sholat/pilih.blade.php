<x-app-layout>
    <div class="min-h-screen bg-[#F7F7F6] text-[#1C1E17] p-6">
        <h1 class="text-2xl font-bold mb-6 text-[#292D22]">Pilih Jenis Sholat</h1>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
            {{-- @if($sudahAbsen)
                <p class="text-xs mt-2 text-red-500 font-medium">Sudah absen</p>
            @endif --}}

            @foreach($dataSholat as $data)
                @php
                    $sholat = $data['sholat'];
                    $jadwal = $data['jadwal'];
                    $sudahAbsenSemua = $data['sudahAbsenSemua'];
                @endphp

                <a href="{{ (!$sudahAbsenSemua) ? route('asrama.sholat.form', ['jenis' => strtolower($sholat->nama)]) : '#' }}"
                    class="p-5 rounded-xl border shadow text-center transition
                            {{ !$sudahAbsenSemua
                                ? 'bg-[#EFF0ED] hover:bg-[#E3E4DF] border-[#D6D8D2] text-[#5C644C]'
                                : 'bg-[#F7F7F6] border-[#D6D8D2] opacity-50 cursor-not-allowed pointer-events-none text-[#8D9382]' }}">

                    <div class="text-3xl mb-2">
                        <i class="bi bi-moon-fill"></i>
                    </div>
                    <h2 class="text-lg font-semibold capitalize">{{ $sholat->nama }}</h2>
                    <p class="text-sm mt-1">
                        {{ $jadwal->jam_mulai . ' - ' . $jadwal->jam_selesai }}
                    </p>

                    @if($sudahAbsenSemua)
                        <p class="text-xs text-red-500 mt-2 font-medium">Sudah absen</p>
                    @endif
                </a>
            @endforeach

        </div>
    </div>

    {{-- Bootstrap Icons CDN (jika belum ada di layout utama) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</x-app-layout>
