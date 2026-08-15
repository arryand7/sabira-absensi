<x-app-shell headerTitle="Kehadiran Kerja" headerSubtitle="Check-in dan check-out berdasarkan lokasi">
    <div class="mx-auto max-w-4xl space-y-4">
        <a href="{{ route('dashboard') }}" class="sabira-button sabira-button-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>

        <x-work-attendance-card :lokasi="$lokasi" :attendance="$absenHariIni" instance="attendance-page" />
    </div>
</x-app-shell>
