<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard Karyawan') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto">
        {{-- MAP CONTAINER --}}
        <div id="map" style="height: 400px;" class="rounded-xl shadow mb-6"></div>

        {{-- INFO / FEEDBACK --}}
        @if(session('success'))
            <div class="p-4 bg-green-200 text-green-800 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 bg-red-200 text-red-800 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        {{-- ABSEN FORM --}}
        <div class="flex gap-4">
            {{-- FORM CHECK-IN --}}
            <form method="POST" action="{{ route('absensi.checkin') }}">
                @csrf
                <input type="hidden" id="latitude" name="latitude">
                <input type="hidden" id="longitude" name="longitude">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">
                    Check-In
                </button>
            </form>

            {{-- FORM CHECK-OUT --}}
            <form method="POST" action="{{ route('absensi.checkout') }}" class="mt-2">
                @csrf
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded">
                    Check-Out
                </button>
            </form>

            <a href="{{ route('karyawan.history') }}" class="text-blue-600 hover:underline">
                📅 Lihat Riwayat Absensi
            </a>

        </div>
    </div>

    {{-- Leaflet JS & CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>

    <script>
        const sekolahLat = -7.3138501;
        const sekolahLng = 112.7256289;

        const map = L.map('map').setView([sekolahLat, sekolahLng], 16);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);

        // Marker Sekolah
        const schoolMarker = L.marker([sekolahLat, sekolahLng]).addTo(map)
            .bindPopup('Sekolah');

        // Coba ambil lokasi user
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;

                const userMarker = L.marker([lat, lng]).addTo(map)
                    .bindPopup('Lokasi Kamu').openPopup();

                const circle = L.circle([sekolahLat, sekolahLng], {
                    color: 'blue',
                    fillColor: '#902A2A',
                    fillOpacity: 0.2,
                    radius: 100
                }).addTo(map);

                map.fitBounds([
                    [sekolahLat, sekolahLng],
                    [lat, lng]
                ]);
            }, function() {
                alert('Gagal mendapatkan lokasi!');
            });
        } else {
            alert('Browser tidak mendukung geolokasi.');
        }
    </script>
</x-app-layout>
