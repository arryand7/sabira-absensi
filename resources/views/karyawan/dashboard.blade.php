<x-app-layout>
    <div class="min-h-screen text-white">

        <!-- Welcome Box -->
        <div class="bg-white text-black rounded-md m-4 p-4 shadow">
            <div class="flex items-center gap-4">
                <img src="{{ asset('storage/' . Auth::user()->karyawan?->foto) }}"onerror="this.onerror=null; this.src='{{ asset('images/default-photo.jpg') }}'"alt="Foto"class="w-20 h-24 object-cover rounded">
                <div>
                    <p class="text-md font-semibold">Welcome, {{ Auth::user()->name }}</p>
                    <p class="text-sm text-gray-600">
                        {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}<br>
                        {{ \Carbon\Carbon::now()->format('H:i') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Menu Grid -->
        <div class="grid grid-cols-2 gap-4 px-6 py-4 text-center text-white">
                <a href="{{ route('absensi.index') }}" class="dark:bg-gray-800 p-6 rounded-md font-semibold hover:bg-gray-700 transition">
                    ABSEN
                </a>

            <a href="{{ route('karyawan.history') }}" class="dark:bg-gray-800 p-6 rounded-md font-semibold hover:bg-gray-700 transition">
                RIWAYAT ABSEN
            </a>
            @if (Auth::user()->role === 'guru')
                <a href="{{ route('guru.schedule') }}" class="dark:bg-gray-800 p-6 rounded-md font-semibold col-span-2 hover:bg-gray-700 transition">
                    JADWAL
                </a>
            @else
                <div class="dark:bg-gray-700 p-6 rounded-md font-semibold opacity-50 cursor-not-allowed" title="Hanya untuk Guru">
                    JADWAL
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
