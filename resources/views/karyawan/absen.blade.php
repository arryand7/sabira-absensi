<x-user-layout>
    {{-- @section('pageTitle', $pageTitle ?? 'Absen Karyawan') --}}
    <div class="py-2 px-2 md:px-6 max-w-4xl mx-auto">
        <!-- Map -->
        <div class="mb-4">
            <a href="{{ route('dashboard') }}"
               class="bg-[#8E412E] text-white px-4 py-2 rounded-md text-sm sm:text-base hover:bg-[#7A3827] transition">
                ← Kembali
            </a>
        </div>
        <div id="map" class="h-64 md:h-96 rounded-xl shadow mb-6"></div>

        @php
            $absenHariIni = \App\Models\AbsensiKaryawan::where('user_id', auth()->user()->id)
                ->whereDate('created_at', now())
                ->first();
        @endphp

        <div class="rounded-xl p-4 shadow-sm mb-6 border"
            style="background-color: {{ $absenHariIni ? '#ECFDF5' : '#FFFBEB' }};
                border-color: {{ $absenHariIni ? '#6EE7B7' : '#FCD34D' }};
                color: {{ $absenHariIni ? '#047857' : '#92400E' }};">

            @if ($absenHariIni)
                ✅ Check-In hari ini pada jam
                <strong>{{ \Carbon\Carbon::parse($absenHariIni->check_in)->format('H:i') }}</strong>.

                @if ($absenHariIni->check_out)
                    <br>✅ Check-Out pada jam
                    <strong>{{ \Carbon\Carbon::parse($absenHariIni->check_out)->format('H:i') }}</strong>.
                @else
                    <br>⚠️belum melakukan Check-Out hari ini.
                @endif

            @else
                ⚠️ belum melakukan Check-In hari ini.
            @endif
        </div>

        <!-- Location Info Box -->
        <div class="bg-emerald-50 border border-emerald-300 rounded-xl p-4 shadow-sm mb-6">
            <div class="mb-3">
                <label class="text-sm font-semibold text-emerald-800 block">Hari</label>
                <div class="border p-2 rounded text-base font-medium text-gray-700">{{ now()->translatedFormat('l, d F Y') }}</div>
            </div>
            <div>
                <label class="text-sm font-semibold text-emerald-800 block">Jam</label>
                <div class="border p-2 rounded text-base font-medium text-gray-700">{{ now()->format('H:i') }}</div>
            </div>
        </div>

        <!-- Form Buttons -->
        <div class="grid grid-cols-2 gap-4 mb-4 text-center">
            <!-- Check-In -->
            <form method="POST" action="{{ route('absensi.checkin') }}" id="checkin-form">
                @csrf
                <input type="hidden" id="latitude_checkin" name="latitude">
                <input type="hidden" id="longitude_checkin" name="longitude">
                <input type="hidden" id="device_hash" name="device_hash">

                <button type="submit" id="checkin-button" disabled class="w-full py-3 bg-green-600 text-white rounded-md font-semibold opacity-50">
                    Loading device...
                </button>
            </form>

            <!-- Check-Out -->
            <form method="POST" action="{{ route('absensi.checkout') }}" id="checkout-form">
                @csrf
                <input type="hidden" id="latitude_checkout" name="latitude">
                <input type="hidden" id="longitude_checkout" name="longitude">

                <button type="submit" id="checkout-button" class="w-full py-3 bg-red-600 text-white rounded-md font-semibold">
                    <i class="bi bi-box-arrow-right me-2"></i>Check-out
                </button>
            </form>
        </div>
    </div>



    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkinButton = document.getElementById('checkin-button');
            const deviceInput = document.getElementById('device_hash');

            // Disable tombol saat persiapan device hash
            checkinButton.disabled = true;
            checkinButton.innerText = 'Loading device...';

            // Generate dan Simpan Device Hash (UUID)
            let deviceHash = localStorage.getItem('device_hash');
            if (!deviceHash) {
                deviceHash = crypto.randomUUID();
                localStorage.setItem('device_hash', deviceHash);
            }
            deviceInput.value = deviceHash;

            console.log('Device Hash:', deviceHash);

            // Aktifkan tombol saat siap
            checkinButton.disabled = false;
            checkinButton.classList.remove('opacity-50');
            checkinButton.innerHTML = '<i class="bi bi-box-arrow-in-right me-2"></i>Check-in';

            // Opsional: isi ulang di submit (backup)
            document.getElementById('checkin-form').addEventListener('submit', function () {
                deviceInput.value = localStorage.getItem('device_hash');
            });

            // Map dan Geolokasi tetap jalan seperti sebelumnya
            const sekolahLat = {{ $lokasi?->latitude ?? '-7.310823820752337' }};
            const sekolahLng = {{ $lokasi?->longitude ?? '112.72923730812086' }};
            const radiusMeter = {{ ($lokasi?->radius ?? 0.2) * 1000 }};

            const map = L.map('map').setView([sekolahLat, sekolahLng], 16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
            L.marker([sekolahLat, sekolahLng]).addTo(map).bindPopup('Lokasi Sekolah');

            L.circle([sekolahLat, sekolahLng], {
                color: 'blue',
                fillColor: '#902A2A',
                fillOpacity: 0.2,
                radius: radiusMeter
            }).addTo(map);

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    // Simpan posisi di check-in dan check-out input
                    document.getElementById('latitude_checkin').value = lat;
                    document.getElementById('longitude_checkin').value = lng;
                    document.getElementById('latitude_checkout').value = lat;
                    document.getElementById('longitude_checkout').value = lng;

                    L.marker([lat, lng]).addTo(map).bindPopup('Lokasi Kamu').openPopup();
                    map.fitBounds([[sekolahLat, sekolahLng], [lat, lng]]);
                });
            }
        });
    </script>

</x-user-layout>

