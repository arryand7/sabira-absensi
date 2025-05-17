<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard Karyawan') }}
        </h2>
    </x-slot>

    <div class="px-4 py-2">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm text-gray-700 hover:text-blue-600">
            <i class="bi bi-arrow-left-circle-fill text-lg mr-1"></i>
        </a>
    </div>

    <div class="py-2 px-2 md:px-6 max-w-4xl mx-auto">

        <!-- Map -->
        <div id="map" class="h-64 md:h-96 rounded-xl shadow mb-6"></div>

        <!-- Location Info Box -->
        <div class="bg-emerald-50 border border-emerald-300 rounded-xl p-4 shadow-sm mb-6">
            <div class="mb-3">
                <label class="text-sm font-semibold text-emerald-800 block">DAY</label>
                <div class="border p-2 rounded text-sm text-gray-700">{{ now()->translatedFormat('l, d F Y') }}</div>
            </div>
            <div>
                <label class="text-sm font-semibold text-emerald-800 block">TIME</label>
                <div class="border p-2 rounded text-sm text-gray-700">
                    {{ now()->format('H:i') }}
                </div>
            </div>
        </div>

        <!-- Feedback Messages -->
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">
                {{ session('error') }}
            </div>
        @endif

        <!-- Form Buttons -->
        <div class="grid grid-cols-2 gap-4 mb-4 text-center">
            <!-- Check-In -->
            <form method="POST" action="{{ route('absensi.checkin') }}">
                @csrf
                <input type="hidden" id="latitude" name="latitude">
                <input type="hidden" id="longitude" name="longitude">
                <button type="submit" class="w-full py-3 bg-green-600 text-white rounded-md font-semibold">
                    Check-in
                </button>
            </form>

            <!-- Check-Out -->
            <form method="POST" action="{{ route('absensi.checkout') }}">
                @csrf
                <button type="submit" class="w-full py-3 bg-red-600 text-white rounded-md font-semibold">
                    Check-out
                </button>
            </form>
        </div>
    </div>

    <!-- Leaflet JS & CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>

    <script>
        const sekolahLat = -7.326132309307022;
        const sekolahLng = 112.73316542604522;

        const map = L.map('map').setView([sekolahLat, sekolahLng], 16);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);

        const schoolMarker = L.marker([sekolahLat, sekolahLng]).addTo(map)
            .bindPopup('Lokasi Sekolah');

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;

                L.marker([lat, lng]).addTo(map).bindPopup('Lokasi Kamu').openPopup();

                L.circle([sekolahLat, sekolahLng], {
                    color: 'blue',
                    fillColor: '#902A2A',
                    fillOpacity: 0.2,
                    radius: 100
                }).addTo(map);

                map.fitBounds([
                    [sekolahLat, sekolahLng],
                    [lat, lng]
                ]);
            }, function(error) {
                alert('Gagal mendapatkan lokasi! Pastikan izin lokasi aktif.');
            }, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            });
        } else {
            alert('Browser tidak mendukung geolokasi.');
        }
    </script>
</x-app-layout>
