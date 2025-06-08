<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Lokasi Absen') }}
        </h2>
    </x-slot>

    <div x-data="{ sidebarOpen: false }" class="flex h-full">
        <x-admin-sidenav />

        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Topbar for mobile -->
            <header class="flex items-center justify-between px-4 py-4 bg-white dark:bg-gray-800 shadow md:hidden">
                <button @click="sidebarOpen = true" class="text-gray-800 dark:text-white focus:outline-none">
                    ☰
                </button>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto p-6">
                <div class="max-w-xl mx-auto bg-white dark:bg-gray-800 p-6 rounded shadow">
                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.lokasi.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="latitude" class="block text-sm font-medium text-gray-800 dark:text-gray-200">
                                Latitude
                            </label>
                            <input type="text" name="latitude" id="latitude"
                                   class="w-full border rounded p-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                   value="{{ old('latitude', $lokasi->latitude) }}" required>
                        </div>

                        <div class="mb-4">
                            <label for="longitude" class="block text-sm font-medium text-gray-800 dark:text-gray-200">
                                Longitude
                            </label>
                            <input type="text" name="longitude" id="longitude"
                                   class="w-full border rounded p-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                   value="{{ old('longitude', $lokasi->longitude) }}" required>
                        </div>

                        <div class="mb-4">
                            <button type="button" id="detectLocation"
                                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 mb-2">
                                Gunakan Lokasi Saat Ini
                            </button>
                            <small class="text-gray-600 dark:text-gray-300 block">
                                Atau klik langsung pada peta untuk memilih lokasi.
                            </small>
                        </div>

                        <!-- Map -->
                        <div id="map" class="h-64 rounded-lg shadow mb-6"></div>

                        <div>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                Simpan Lokasi
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>

    <script>
        const defaultLat = {{ old('latitude', $lokasi->latitude ?? -7.25) }};
        const defaultLng = {{ old('longitude', $lokasi->longitude ?? 112.75) }};

        const map = L.map('map').setView([defaultLat, defaultLng], 16);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);

        let marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

        marker.on('dragend', function (e) {
            const position = marker.getLatLng();
            document.getElementById('latitude').value = position.lat;
            document.getElementById('longitude').value = position.lng;
        });

        map.on('click', function (e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            marker.setLatLng([lat, lng]);
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
        });

        document.getElementById('detectLocation').addEventListener('click', function () {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    document.getElementById('latitude').value = lat;
                    document.getElementById('longitude').value = lng;

                    marker.setLatLng([lat, lng]);
                    map.setView([lat, lng], 17);
                }, function (error) {
                    alert('Gagal mendapatkan lokasi. Pastikan izin lokasi diaktifkan.');
                });
            } else {
                alert('Browser tidak mendukung geolokasi.');
            }
        });
    </script>
</x-app-layout>
