<x-app-layout>
    <div class="min-h-screen bg-[#D6D8D2] text-[#1C1E17] pb-8">

        <!-- Welcome Box -->
        <div class="bg-[#F7F7F6] rounded-md m-4 p-4 shadow-md flex items-center gap-4">
            <img src="{{ asset('storage/' . Auth::user()->karyawan?->foto) }}"
                onerror="this.onerror=null; this.src='{{ asset('images/default-photo.jpg') }}'"
                alt="Foto"
                class="w-20 h-24 object-cover rounded shadow">

            <div>
                <p class="text-lg font-semibold text-[#373C2E]">Selamat datang, {{ Auth::user()->name }}</p>
                <p class="text-sm text-[#8D9382]">
                    {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}<br>
                    <span class="text-[#5C644C] font-medium text-base">{{ \Carbon\Carbon::now()->format('H:i') }}</span>
                </p>
            </div>
        </div>

        <!-- Menu Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 px-4 text-center text-sm font-semibold">
            <a href="{{ route('absensi.index') }}"
               class="bg-[#F7F7F6] hover:bg-[#EFF0ED] transition p-4 rounded-md shadow flex flex-col items-center justify-center gap-2">
                <i class="bi bi-clipboard-check text-2xl text-[#5C644C]"></i>
                <span class="text-[#373C2E]">ABSEN</span>
            </a>

            <a href="{{ route('karyawan.history') }}"
               class="bg-[#F7F7F6] hover:bg-[#EFF0ED] transition p-4 rounded-md shadow flex flex-col items-center justify-center gap-2">
                <i class="bi bi-clock-history text-2xl text-[#5C644C]"></i>
                <span class="text-[#373C2E]">RIWAYAT ABSENSI</span>
            </a>

            @if (Auth::user()->role === 'guru')
                <a href="{{ route('guru.schedule') }}"
                   class="bg-[#F7F7F6] hover:bg-[#EFF0ED] transition p-4 rounded-md shadow flex flex-col items-center justify-center gap-2">
                    <i class="bi bi-calendar-week text-2xl text-[#5C644C]"></i>
                    <span class="text-[#373C2E]">JADWAL MENGAJAR</span>
                </a>

                <a href="{{ route('guru.history.index') }}"
                   class="bg-[#F7F7F6] hover:bg-[#EFF0ED] transition p-4 rounded-md shadow flex flex-col items-center justify-center gap-2">
                    <i class="bi bi-journal-text text-2xl text-[#5C644C]"></i>
                    <span class="text-[#373C2E]">RIWAYAT MENGAJAR</span>
                </a>
            @else
                <div class="bg-[#EFF0ED] text-[#8D9382] p-4 rounded-md shadow flex flex-col items-center justify-center gap-2 cursor-not-allowed" title="Hanya untuk Guru">
                    <i class="bi bi-calendar-x text-2xl"></i>
                    <span>JADWAL</span>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
