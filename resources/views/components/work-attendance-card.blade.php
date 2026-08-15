@props(['lokasi', 'attendance' => null, 'instance' => 'work-attendance'])

@php
    $prefix = preg_replace('/[^a-z0-9_-]/i', '-', $instance);
    $schoolLatitude = (float) ($lokasi?->latitude ?? -7.310823820752337);
    $schoolLongitude = (float) ($lokasi?->longitude ?? 112.72923730812086);
    $radiusMeters = (float) (($lokasi?->radius ?? 0.2) * 1000);
@endphp

<section class="rounded-[var(--radius-md)] border border-[var(--sabira-border-soft)] bg-[var(--sabira-surface)] p-5 md:p-6"
    aria-labelledby="{{ $prefix }}-title">
    <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 id="{{ $prefix }}-title" class="text-sm font-bold uppercase tracking-wider text-[var(--sabira-ink)]">
                Kehadiran Kerja
            </h2>
            <p class="mt-1 text-xs text-[var(--sabira-muted)]">Aktifkan izin lokasi untuk melakukan check-in atau check-out.</p>
        </div>
        <span class="text-xs font-medium text-[var(--sabira-muted)]">{{ now()->translatedFormat('l, d F Y · H:i') }} WIB</span>
    </div>

    @if ($attendance)
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">
            <i class="fas fa-circle-check mr-1"></i>
            Check-in <strong>{{ \Carbon\Carbon::parse($attendance->check_in)->format('H:i') }}</strong>
            @if ($attendance->check_out)
                · Check-out <strong>{{ \Carbon\Carbon::parse($attendance->check_out)->format('H:i') }}</strong>
            @else
                · Belum check-out
            @endif
        </div>
    @else
        <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
            <i class="fas fa-location-dot mr-1"></i> Belum melakukan check-in hari ini.
        </div>
    @endif

    <div id="{{ $prefix }}-map" class="h-56 w-full overflow-hidden rounded-xl border border-[var(--sabira-border-soft)] bg-slate-100 md:h-64"
        aria-label="Peta lokasi kehadiran"></div>
    <p id="{{ $prefix }}-location-status" class="mt-2 text-xs text-[var(--sabira-muted)]" role="status">
        Meminta lokasi perangkat…
    </p>

    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
        <form method="POST" action="{{ route('absensi.checkin') }}" id="{{ $prefix }}-checkin-form">
            @csrf
            <input type="hidden" name="latitude" data-coordinate="latitude">
            <input type="hidden" name="longitude" data-coordinate="longitude">
            <input type="hidden" name="device_hash" data-device-hash>
            <button type="submit" data-location-action disabled class="sabira-button sabira-button-primary w-full opacity-50 disabled:cursor-not-allowed">
                <i class="fas fa-location-crosshairs"></i> Menunggu lokasi…
            </button>
        </form>

        <form method="POST" action="{{ route('absensi.checkout') }}" id="{{ $prefix }}-checkout-form">
            @csrf
            <input type="hidden" name="latitude" data-coordinate="latitude">
            <input type="hidden" name="longitude" data-coordinate="longitude">
            <button type="submit" data-location-action disabled class="sabira-button w-full bg-rose-600 text-white opacity-50 disabled:cursor-not-allowed hover:bg-rose-700">
                <i class="fas fa-right-from-bracket"></i> Menunggu lokasi…
            </button>
        </form>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const root = document.getElementById(@js($prefix . '-map'))?.closest('section');
        if (!root || !window.L) return;

        const school = [@js($schoolLatitude), @js($schoolLongitude)];
        const map = window.L.map(@js($prefix . '-map')).setView(school, 16);
        window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(map);
        window.L.marker(school).addTo(map).bindPopup('Lokasi Sekolah');
        window.L.circle(school, { color: '#4f46e5', fillColor: '#6366f1', fillOpacity: 0.12, radius: @js($radiusMeters) }).addTo(map);

        const status = root.querySelector('[role="status"]');
        const buttons = root.querySelectorAll('[data-location-action]');
        const setCoordinates = (latitude, longitude) => {
            root.querySelectorAll('[data-coordinate="latitude"]').forEach(input => input.value = latitude);
            root.querySelectorAll('[data-coordinate="longitude"]').forEach(input => input.value = longitude);
            buttons.forEach((button, index) => {
                button.disabled = false;
                button.classList.remove('opacity-50');
                button.innerHTML = index === 0
                    ? '<i class="fas fa-right-to-bracket"></i> Check In'
                    : '<i class="fas fa-right-from-bracket"></i> Check Out';
            });
        };

        const deviceInput = root.querySelector('[data-device-hash]');
        let deviceHash = localStorage.getItem('device_hash');
        if (!deviceHash) {
            deviceHash = window.crypto?.randomUUID?.() ?? `sabira-${Date.now()}-${Math.random().toString(16).slice(2)}`;
            localStorage.setItem('device_hash', deviceHash);
        }
        deviceInput.value = deviceHash;

        if (!navigator.geolocation) {
            status.textContent = 'Browser ini tidak mendukung geolocation. Gunakan browser modern dengan HTTPS.';
            status.classList.add('text-rose-600');
            return;
        }

        navigator.geolocation.getCurrentPosition(position => {
            const user = [position.coords.latitude, position.coords.longitude];
            setCoordinates(user[0], user[1]);
            window.L.marker(user).addTo(map).bindPopup('Lokasi Anda').openPopup();
            map.fitBounds(window.L.latLngBounds([school, user]).pad(0.2));
            status.textContent = `Lokasi ditemukan (akurasi ±${Math.round(position.coords.accuracy)} meter).`;
            status.classList.add('text-emerald-600');
        }, error => {
            const messages = {
                1: 'Izin lokasi ditolak. Izinkan akses lokasi pada browser, lalu muat ulang halaman.',
                2: 'Lokasi perangkat tidak tersedia. Pastikan GPS atau layanan lokasi aktif.',
                3: 'Pencarian lokasi melewati batas waktu. Coba kembali di area dengan sinyal lebih baik.',
            };
            status.textContent = messages[error.code] ?? 'Lokasi tidak dapat diperoleh. Muat ulang dan coba kembali.';
            status.classList.add('text-rose-600');
        }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 30000 });
    }, { once: true });
</script>
