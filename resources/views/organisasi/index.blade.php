<x-app-shell>
    <div class="min-h-screen bg-[var(--sabira-surface-soft)] text-[var(--sabira-ink)] p-6">
        <h1 class="text-2xl font-bold mb-6 text-[var(--sabira-ink)]">Absensi Asrama</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Tombol Absen Sholat -->
            <a href="{{ route('asrama.sholat') }}"
               class="p-5 bg-[var(--sabira-surface-soft)] hover:bg-[var(--sabira-surface-strong)] border border-[var(--sabira-border)] rounded-xl shadow text-center transition">
                <div class="text-[var(--sabira-muted)] text-3xl mb-2">
                    <i class="bi bi-moon-stars-fill"></i>
                </div>
                <h2 class="text-lg font-semibold text-[var(--sabira-ink)]">Absen Sholat</h2>
                <p class="text-sm text-[var(--sabira-body)] mt-1">Untuk absensi kegiatan rutin seperti sholat Subuh, Dzuhur, dst.</p>
            </a>

            <!-- Tombol Absen Kegiatan -->
            <a href="{{ route('asrama.kegiatan') }}"
               class="p-5 bg-[var(--sabira-surface-soft)] hover:bg-[var(--sabira-surface-strong)] border border-[var(--sabira-border)] rounded-xl shadow text-center transition">
                <div class="text-[var(--sabira-muted)] text-3xl mb-2">
                    <i class="bi bi-calendar-event-fill"></i>
                </div>
                <h2 class="text-lg font-semibold text-[var(--sabira-ink)]">Absen Kegiatan Asrama</h2>
                <p class="text-sm text-[var(--sabira-body)] mt-1">Untuk kegiatan seperti kajian, kultum, dan lainnya.</p>
            </a>

            <!-- Tombol History Sholat -->
            <a href="{{ route('asrama.sholat.history') }}"
               class="p-5 bg-[var(--sabira-surface-soft)] hover:bg-[var(--sabira-surface-strong)] border border-[var(--sabira-border)] rounded-xl shadow text-center transition">
                <div class="text-[var(--sabira-muted)] text-3xl mb-2">
                    <i class="bi bi-clock-history"></i>
                </div>
                <h2 class="text-lg font-semibold text-[var(--sabira-ink)]">History Sholat</h2>
                <p class="text-sm text-[var(--sabira-body)] mt-1">Lihat riwayat absensi sholat</p>
            </a>
        </div>
    </div>

</x-app-shell>
