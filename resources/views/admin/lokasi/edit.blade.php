<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidenav />
    </x-slot>

    <div x-data="{ sidebarOpen: false }" class="flex h-full">
        <div class="flex-1 flex flex-col overflow-hidden">
            <main class="flex-1 overflow-x-hidden overflow-y-auto p-1">
                <h2 class="font-semibold text-2xl text-[#1C1E17] mb-3">
                    Edit Lokasi Absen
                </h2>

                <div class="max-w-xl mx-auto bg-[#8D9382] text-[#1C1E17] p-6 rounded-2xl shadow-md">
                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.lokasi.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="latitude" class="block text-sm font-medium">
                                Latitude
                            </label>
                            <input type="text" name="latitude" id="latitude"
                                class="w-full rounded-md border-gray-300 bg-[#EEF3E9] text-[#1C1E17] p-2 shadow-sm"
                                value="{{ old('latitude', $lokasi->latitude) }}" required>
                        </div>

                        <div class="mb-4">
                            <label for="longitude" class="block text-sm font-medium">
                                Longitude
                            </label>
                            <input type="text" name="longitude" id="longitude"
                                class="w-full rounded-md border-gray-300 bg-[#EEF3E9] text-[#1C1E17] p-2 shadow-sm"
                                value="{{ old('longitude', $lokasi->longitude) }}" required>
                        </div>

                        <div class="mb-4">
                            <button type="button" id="detectLocation"
                                class="px-4 py-2 bg-green-600 text-white rounded-md text-sm hover:bg-green-700 shadow">
                                Gunakan Lokasi Saat Ini
                            </button>
                            <small class="text-sm block mt-1 text-[#EEF3E9]">
                                Atau klik langsung pada peta untuk memilih lokasi.
                            </small>
                        </div>

                        <!-- Map -->
                        <div id="map" class="h-64 rounded-lg shadow mb-6"></div>

                        <div>
                            <button type="submit"
                                class="bg-[#8E412E] hover:bg-[#BA6F4D] text-white text-sm px-4 py-2 rounded-md shadow">
                                <i class="bi bi-check-circle mr-1"></i> Simpan Lokasi
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
                }, function () {
                    alert('Gagal mendapatkan lokasi. Pastikan izin lokasi diaktifkan.');
                });
            } else {
                alert('Browser tidak mendukung geolokasi.');
            }
        });
    </script>
</x-app-layout>
