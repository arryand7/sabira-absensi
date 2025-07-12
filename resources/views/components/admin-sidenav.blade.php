<div x-data="{ sidebarOpen: false }" class="h-screen flex overflow-hidden bg-[#5c644c] text-[#F7F7F6]">

    <!-- Overlay (Mobile) -->
    <div x-show="sidebarOpen"
         x-transition.opacity
         class="fixed inset-0 z-20 bg-black bg-opacity-50 md:hidden"
         @click="sidebarOpen = false">
    </div>

    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0 ease-out' : '-translate-x-full ease-in'"
           class="fixed inset-y-0 left-0 z-30 w-64 overflow-y-auto transition duration-300 transform bg-[#5c644c] text-[#F7F7F6] md:translate-x-0 md:static md:inset-0 shadow-lg rounded-r-2xl md:rounded-none">

        <!-- Sidebar Header -->
        <div class="px-4 py-3 border-b border-[#F7F7F6]/20">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold">Admin</h1>
                <button class="md:hidden" @click="sidebarOpen = false" aria-label="Tutup Sidebar">✕</button>
            </div>

            @if($activeYear)
                <p class="text-sm mt-1 text-[#F7F7F6]/70">
                    Tahun Ajaran: <span class="font-medium">{{ $activeYear->name }}</span>
                </p>
            @else
                <p class="text-sm mt-1 text-red-300 italic">Tahun ajaran belum diset</p>
            @endif
        </div>

        <!-- Navigation -->
        <nav class="px-4 py-4 space-y-1 text-sm font-medium">

            <!-- Dashboard -->
            <a href="{{ route('admin.dashboard') }}"
            class="flex items-center gap-3 px-4 py-2 rounded-xl transition
                    {{ Route::is('admin.dashboard') ? 'bg-[#F7F7F6] text-[#5c644c] shadow' : 'hover:bg-[#F7F7F6] hover:text-[#5c644c]' }}">
                <i class="bi bi-house-door-fill text-lg"></i> Dashboard
            </a>

            <!-- Laporan Absensi -->
            <hr class="my-2 border-[#F7F7F6]/30">
            <p class="text-xs text-[#F7F7F6]/60 uppercase px-4">Laporan Absensi</p>

            <a href="{{ route('laporan.karyawan') }}"
            class="flex items-center gap-3 px-4 py-2 rounded-xl transition
                    {{ Route::is('laporan.karyawan') ? 'bg-[#F7F7F6] text-[#5c644c] shadow' : 'hover:bg-[#F7F7F6] hover:text-[#5c644c]' }}">
                <i class="bi bi-bar-chart-fill text-lg"></i> Absensi Karyawan
            </a>

            <!-- Submenu Absensi Murid -->
            <div x-data="{ open: {{ Route::is('laporan.murid') || Route::is('laporan.murid.*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                        class="w-full flex items-center justify-between gap-3 px-4 py-2 rounded-xl transition
                            {{ Route::is('laporan.murid.*') ? 'bg-[#F7F7F6] text-[#5c644c] shadow' : 'hover:bg-[#F7F7F6] hover:text-[#5c644c]' }}">
                    <div class="flex items-center gap-3">
                        <i class="bi bi-bar-chart-fill text-lg"></i> Absensi Murid
                    </div>
                    <i class="bi" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                </button>
                <div x-show="open" x-transition class="ml-8 mt-2 space-y-1">
                    <a href="{{ route('laporan.murid') }}"
                    class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm transition
                    {{ Route::is('laporan.murid') ? 'bg-[#F7F7F6] text-[#5c644c]' : 'hover:bg-[#F7F7F6] hover:text-[#5c644c]' }}">
                        <i class="bi bi-journal-bookmark-fill"></i> Absen Kelas
                    </a>
                    <a href="{{ route('laporan.murid.mapel') }}"
                    class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm transition
                    {{ Route::is('laporan.murid.mapel') ? 'bg-[#F7F7F6] text-[#5c644c]' : 'hover:bg-[#F7F7F6] hover:text-[#5c644c]' }}">
                        <i class="bi bi-journal-check"></i> Absen Mata Pelajaran
                    </a>
                </div>
            </div>

            <!-- Manajemen Data -->
            <hr class="my-2 border-[#F7F7F6]/30">
            <p class="text-xs text-[#F7F7F6]/60 uppercase px-4">Manajemen Data</p>

            <a href="{{ route('users.index') }}"
            class="flex items-center gap-3 px-4 py-2 rounded-xl transition
                    {{ Route::is('users.*') ? 'bg-[#F7F7F6] text-[#5c644c] shadow' : 'hover:bg-[#F7F7F6] hover:text-[#5c644c]' }}">
                <i class="bi bi-people-fill text-lg"></i> Manajemen User
            </a>

            <a href="{{ route('admin.students.index') }}"
            class="flex items-center gap-3 px-4 py-2 rounded-xl transition
                    {{ Route::is('admin.students.*') ? 'bg-[#F7F7F6] text-[#5c644c] shadow' : 'hover:bg-[#F7F7F6] hover:text-[#5c644c]' }}">
                <i class="bi bi-person-lines-fill text-lg"></i> Manajemen Murid
            </a>

            <!-- Tahun Ajaran -->
            <hr class="my-2 border-[#F7F7F6]/30">
            <p class="text-xs text-[#F7F7F6]/60 uppercase px-4">Tahun Ajaran</p>

            <a href="{{ route('academic-years.index') }}"
            class="flex items-center gap-3 px-4 py-2 rounded-xl transition
                    {{ Route::is('academic-years.*') ? 'bg-[#F7F7F6] text-[#5c644c] shadow' : 'hover:bg-[#F7F7F6] hover:text-[#5c644c]' }}">
                <i class="bi bi-people-fill text-lg"></i> Manajemen Tahun Ajaran
            </a>

            <a href="{{ route('promotion.index') }}"
            class="flex items-center gap-3 px-4 py-2 rounded-xl transition
                    {{ Route::is('promotion.*') ? 'bg-[#F7F7F6] text-[#5c644c] shadow' : 'hover:bg-[#F7F7F6] hover:text-[#5c644c]' }}">
                <i class="bi bi-people-fill text-lg"></i> Migrasi Data Siswa
            </a>

            <!-- Master Data Karyawan -->
            <hr class="my-2 border-[#F7F7F6]/30">
            <p class="text-xs text-[#F7F7F6]/60 uppercase px-4">Master Data Karyawan</p>

            <a href="{{ route('divisis.index') }}"
            class="flex items-center gap-3 px-4 py-2 rounded-xl transition
                    {{ Route::is('divisis.*') ? 'bg-[#F7F7F6] text-[#5c644c] shadow' : 'hover:bg-[#F7F7F6] hover:text-[#5c644c]' }}">
                <i class="bi bi-diagram-3-fill text-lg"></i> Manajemen Divisi
            </a>

            <a href="{{ route('admin.lokasi.edit') }}"
            class="flex items-center gap-3 px-4 py-2 rounded-xl transition
                    {{ Route::is('admin.lokasi.*') ? 'bg-[#F7F7F6] text-[#5c644c] shadow' : 'hover:bg-[#F7F7F6] hover:text-[#5c644c]' }}">
                <i class="bi bi-geo-alt text-lg"></i> Lokasi
            </a>

            <!-- Master Data Sekolah -->
            <hr class="my-2 border-[#F7F7F6]/30">
            <p class="text-xs text-[#F7F7F6]/60 uppercase px-4">Master Data Sekolah</p>

            <a href="{{ route('admin.class-groups.index') }}"
            class="flex items-center gap-3 px-4 py-2 rounded-xl transition
                    {{ Route::is('admin.class-groups.*') ? 'bg-[#F7F7F6] text-[#5c644c] shadow' : 'hover:bg-[#F7F7F6] hover:text-[#5c644c]' }}">
                <i class="bi bi-building-fill text-lg"></i> Manajemen Kelas
            </a>

            <a href="{{ route('subjects.index') }}"
            class="flex items-center gap-3 px-4 py-2 rounded-xl transition
                    {{ Route::is('subjects.*') ? 'bg-[#F7F7F6] text-[#5c644c] shadow' : 'hover:bg-[#F7F7F6] hover:text-[#5c644c]' }}">
                <i class="bi bi-book text-lg"></i> Mata Pelajaran
            </a>

            <a href="{{ route('admin.schedules.index') }}"
            class="flex items-center gap-3 px-4 py-2 rounded-xl transition
                    {{ Route::is('admin.schedules.*') ? 'bg-[#F7F7F6] text-[#5c644c] shadow' : 'hover:bg-[#F7F7F6] hover:text-[#5c644c]' }}">
                <i class="bi bi-calendar-event-fill text-lg"></i> Jadwal Guru
            </a>

            <a href="{{ route('admin.sholat') }}"
            class="flex items-center gap-3 px-4 py-2 rounded-xl transition
                    {{ Route::is('admin.sholat') ? 'bg-[#F7F7F6] text-[#5c644c] shadow' : 'hover:bg-[#F7F7F6] hover:text-[#5c644c]' }}">
                <i class="bi bi-clock-fill text-lg"></i> Kegiatan Sholat
            </a>

            <!-- Logout -->
            <hr class="my-2 border-[#F7F7F6]/30">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full text-left flex items-center gap-3 px-4 py-2 rounded-xl transition
                            text-red-300 hover:bg-red-100 hover:text-red-800">
                    <i class="bi bi-box-arrow-right text-lg"></i> Logout
                </button>
            </form>
        </nav>

    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Topbar (Mobile Only) -->
        <header class="md:hidden flex items-center justify-between px-4 py-4 bg-[#5c644c] text-[#F7F7F6] shadow-lg">
            <button @click="sidebarOpen = true" class="focus:outline-none" aria-label="Buka Sidebar">☰</button>
            <h2 class="text-lg font-semibold">Dashboard</h2>
        </header>

        <!-- Page Content -->
        <main class="flex-1 overflow-y-auto p-6 bg-[#5c644c] text-[#F7F7F6]">
            {{ $slot }}
        </main>
    </div>
</div>
