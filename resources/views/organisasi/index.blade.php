<x-user-layout>
    <div class="min-h-screen bg-[#F7F7F6] text-[#1C1E17] p-6">
        <h1 class="text-2xl font-bold mb-6 text-[#292D22]">Absensi Asrama</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Tombol Absen Sholat -->
            <a href="{{ route('asrama.sholat') }}"
               class="p-5 bg-[#EFF0ED] hover:bg-[#E3E4DF] border border-[#D6D8D2] rounded-xl shadow text-center transition">
                <div class="text-[#5C644C] text-3xl mb-2">
                    <i class="bi bi-moon-stars-fill"></i>
                </div>
                <h2 class="text-lg font-semibold text-[#1C1E17]">Absen Sholat</h2>
                <p class="text-sm text-[#44483B] mt-1">Untuk absensi kegiatan rutin seperti sholat Subuh, Dzuhur, dst.</p>
            </a>

            <!-- Tombol Absen Kegiatan -->
            <a href="{{ route('asrama.kegiatan') }}"
               class="p-5 bg-[#EFF0ED] hover:bg-[#E3E4DF] border border-[#D6D8D2] rounded-xl shadow text-center transition">
                <div class="text-[#5C644C] text-3xl mb-2">
                    <i class="bi bi-calendar-event-fill"></i>
                </div>
                <h2 class="text-lg font-semibold text-[#1C1E17]">Absen Kegiatan Asrama</h2>
                <p class="text-sm text-[#44483B] mt-1">Untuk kegiatan seperti kajian, kultum, dan lainnya.</p>
            </a>

            <!-- Tombol History Sholat -->
            <a href="{{ route('asrama.sholat.history') }}"
               class="p-5 bg-[#EFF0ED] hover:bg-[#E3E4DF] border border-[#D6D8D2] rounded-xl shadow text-center transition">
                <div class="text-[#5C644C] text-3xl mb-2">
                    <i class="bi bi-clock-history"></i>
                </div>
                <h2 class="text-lg font-semibold text-[#1C1E17]">History Sholat</h2>
                <p class="text-sm text-[#44483B] mt-1">Lihat riwayat absensi sholat</p>
            </a>
        </div>
    </div>

    {{-- Bootstrap Icons CDN (jika belum include di layout utama) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</x-user-layout>
