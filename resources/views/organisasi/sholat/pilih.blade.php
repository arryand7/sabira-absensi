<x-app-shell>
    <div class="min-h-screen bg-[var(--sabira-surface-soft)] text-[var(--sabira-ink)] p-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-[var(--sabira-ink)]">Pilih Jenis Sholat</h1>

            <a href="{{ route('asrama.index') }}"
            class="inline-flex items-center gap-2 px-4 py-2 bg-[var(--sabira-primary)] hover:bg-[var(--sabira-primary-active)] text-white rounded-md text-sm shadow transition">
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

                @if(!$sudahAbsenSemua)
                <a href="{{ route('asrama.sholat.form', ['jenis' => strtolower($sholat->nama)]) }}"
                    class="p-5 rounded-xl border shadow text-center transition
                            {{ (!$jadwal || !$sudahAbsenSemua)
                                ? 'bg-[var(--sabira-surface-soft)] hover:bg-[var(--sabira-surface-strong)] border-[var(--sabira-border)] text-[var(--sabira-muted)]'
                                : 'bg-[var(--sabira-surface-soft)] border-[var(--sabira-border)] opacity-50 cursor-not-allowed pointer-events-none text-[var(--sabira-muted)]' }}">

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
                @else
                <div aria-disabled="true" class="p-5 rounded-xl border shadow text-center bg-[var(--sabira-surface-soft)] border-[var(--sabira-border)] opacity-50 cursor-not-allowed text-[var(--sabira-muted)]">
                    <div class="text-3xl mb-2"><i class="bi bi-moon-fill"></i></div>
                    <h2 class="text-lg font-semibold capitalize">{{ $sholat->nama }}</h2>
                    @if ($jadwal)<p class="text-sm mt-1">{{ $jadwal->jam_mulai . ' - ' . $jadwal->jam_selesai }}</p>@endif
                    <p class="text-xs text-red-500 mt-2 font-medium">Sudah absen</p>
                </div>
                @endif
            @endforeach
        </div>
    </div>

</x-app-shell>
