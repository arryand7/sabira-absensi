<x-app-shell>
    <div class="min-h-screen text-[var(--sabira-ink)] pb-8">

        <!-- Welcome Box -->
        <div class="bg-[var(--sabira-surface-soft)] rounded-md m-4 p-4 shadow-md flex items-center gap-4">
            <img src="{{ Auth::user()->karyawan?->foto
                    ? asset('storage/' . Auth::user()->karyawan->foto)
                    : asset('images/default-photo.jpg') }}"
                alt="Foto"
                class="w-20 h-24 object-cover rounded shadow">


            <div>
                <p class="text-base font-semibold">Hello,</p>
                <p class="text-sm font-semibold">{{ Auth::user()->name }}</p>
                <p class="text-sm text-[var(--sabira-muted)]">
                    {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}<br>
                    {{-- <span class="text-[var(--sabira-muted)] font-medium text-base">{{ \Carbon\Carbon::now()->format('H:i') }}</span> --}}
                </p>
            </div>
        </div>

        <!-- Menu Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 px-4 text-center text-sm font-semibold">
            
            <a href="{{ route('profile.edit') }}"
               class="bg-[var(--sabira-surface-soft)] hover:bg-[var(--sabira-surface-soft)] transition p-4 rounded-md shadow flex flex-col items-center justify-center gap-2">
                <i class="bi bi-person-check text-2xl text-[var(--sabira-muted)]"></i>
                <span class="text-[var(--sabira-body)]">Edit Profile</span>
            </a>

            <a href="{{ route('absensi.index') }}"
               class="bg-[var(--sabira-surface-soft)] hover:bg-[var(--sabira-surface-soft)] transition p-4 rounded-md shadow flex flex-col items-center justify-center gap-2">
                <i class="bi bi-clipboard-check text-2xl text-[var(--sabira-muted)]"></i>
                <span class="text-[var(--sabira-body)]">ABSEN</span>
            </a>

            <a href="{{ route('karyawan.history') }}"
               class="bg-[var(--sabira-surface-soft)] hover:bg-[var(--sabira-surface-soft)] transition p-4 rounded-md shadow flex flex-col items-center justify-center gap-2">
                <i class="bi bi-clock-history text-2xl text-[var(--sabira-muted)]"></i>
                <span class="text-[var(--sabira-body)]">RIWAYAT ABSENSI</span>
            </a>

            @if (Auth::user()->role === 'guru')
                <a href="{{ route('guru.schedule') }}"
                   class="bg-[var(--sabira-surface-soft)] hover:bg-[var(--sabira-surface-soft)] transition p-4 rounded-md shadow flex flex-col items-center justify-center gap-2">
                    <i class="bi bi-calendar-week text-2xl text-[var(--sabira-muted)]"></i>
                    <span class="text-[var(--sabira-body)]">JADWAL MENGAJAR</span>
                </a>

                <a href="{{ route('guru.history.index') }}"
                   class="bg-[var(--sabira-surface-soft)] hover:bg-[var(--sabira-surface-soft)] transition p-4 rounded-md shadow flex flex-col items-center justify-center gap-2">
                    <i class="bi bi-journal-text text-2xl text-[var(--sabira-muted)]"></i>
                    <span class="text-[var(--sabira-body)]">RIWAYAT MENGAJAR</span>
                </a>
            @else
                <div class="bg-[var(--sabira-surface-soft)] text-[var(--sabira-muted)] p-4 rounded-md shadow flex flex-col items-center justify-center gap-2 cursor-not-allowed" title="Hanya untuk Guru">
                    <i class="bi bi-calendar-x text-2xl"></i>
                    <span>JADWAL</span>
                </div>
            @endif
        </div>
    </div>
</x-app-shell>
