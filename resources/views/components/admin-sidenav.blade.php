@php
    $user = auth()->user();
    $appSetting = \App\AppSettingManager::current();
    $avatarUrl = $user?->karyawan?->foto
        ? asset('storage/' . $user->karyawan->foto)
        : asset('images/default-photo.jpg');
    $brandName = $appSetting->app_name ?? config('app.name', 'Sabira Absensi');
    $brandLogo = $appSetting->app_logo
        ? asset('storage/' . $appSetting->app_logo)
        : asset('images/logo.png');
    $laporanOpen = request()->routeIs('laporan.karyawan')
        || request()->routeIs('laporan.pertemuan')
        || request()->routeIs('laporan.murid*');
    $manajemenOpen = request()->routeIs('users.*')
        || request()->routeIs('admin.students.*')
        || request()->routeIs('divisis.*')
        || request()->routeIs('admin.lokasi.*');
    $akademikOpen = request()->routeIs('academic-years.*')
        || request()->routeIs('promotion.*');
    $masterOpen = request()->routeIs('admin.class-groups.*')
        || request()->routeIs('subjects.*')
        || request()->routeIs('admin.schedules.*')
        || request()->routeIs('admin.sholat');
    $pengaturanOpen = request()->routeIs('admin.settings.sso*')
        || request()->routeIs('admin.settings.app*');
@endphp

<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ route('admin.dashboard') }}" class="brand-link">
        <img src="{{ $brandLogo }}" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">{{ $brandName }}</span>
    </a>

    <div class="sidebar">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="{{ $avatarUrl }}" class="img-circle elevation-2" alt="User">
            </div>
            <div class="info">
                <a href="#" class="d-block">{{ $user->name ?? 'Guest' }}</a>
                <span class="text-xs text-muted">{{ ucfirst($user->role ?? '-') }}</span>
            </div>
        </div>

        @if($activeYear)
            <div class="px-3 pb-3 text-xs text-muted">
                Tahun Ajaran: <span class="font-weight-semibold">{{ $activeYear->name }}</span>
            </div>
        @else
            <div class="px-3 pb-3 text-xs text-warning">Tahun ajaran belum diset</div>
        @endif

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-house-door-fill"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <li class="nav-item has-treeview {{ $laporanOpen ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ $laporanOpen ? 'active' : '' }}">
                        <i class="nav-icon bi bi-clipboard-data"></i>
                        <p>
                            Laporan
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('laporan.karyawan') }}" class="nav-link {{ request()->routeIs('laporan.karyawan') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Absensi Karyawan</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('laporan.pertemuan') }}" class="nav-link {{ request()->routeIs('laporan.pertemuan') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Rekap Pertemuan</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('laporan.murid') }}" class="nav-link {{ request()->routeIs('laporan.murid') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Absen Kelas</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('laporan.murid.mapel') }}" class="nav-link {{ request()->routeIs('laporan.murid.mapel') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Absen Mata Pelajaran</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item has-treeview {{ $manajemenOpen ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ $manajemenOpen ? 'active' : '' }}">
                        <i class="nav-icon bi bi-people-fill"></i>
                        <p>
                            Manajemen Data
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Manajemen User</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.students.index') }}" class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Manajemen Murid</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('divisis.index') }}" class="nav-link {{ request()->routeIs('divisis.*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Manajemen Divisi</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.lokasi.edit') }}" class="nav-link {{ request()->routeIs('admin.lokasi.*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Lokasi</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item has-treeview {{ $akademikOpen ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ $akademikOpen ? 'active' : '' }}">
                        <i class="nav-icon bi bi-calendar-range"></i>
                        <p>
                            Tahun Ajaran
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('academic-years.index') }}" class="nav-link {{ request()->routeIs('academic-years.*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Manajemen Tahun Ajaran</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('promotion.index') }}" class="nav-link {{ request()->routeIs('promotion.*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Migrasi Data Siswa</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item has-treeview {{ $masterOpen ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ $masterOpen ? 'active' : '' }}">
                        <i class="nav-icon bi bi-building"></i>
                        <p>
                            Master Data Sekolah
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.class-groups.index') }}" class="nav-link {{ request()->routeIs('admin.class-groups.*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Manajemen Kelas</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('subjects.index') }}" class="nav-link {{ request()->routeIs('subjects.*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Mata Pelajaran</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.schedules.index') }}" class="nav-link {{ request()->routeIs('admin.schedules.*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Jadwal Guru</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.sholat') }}" class="nav-link {{ request()->routeIs('admin.sholat') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Kegiatan Sholat</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item has-treeview {{ $pengaturanOpen ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ $pengaturanOpen ? 'active' : '' }}">
                        <i class="nav-icon bi bi-gear-fill"></i>
                        <p>
                            Pengaturan
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.settings.app') }}" class="nav-link {{ request()->routeIs('admin.settings.app*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Pengaturan Aplikasi</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.settings.sso') }}" class="nav-link {{ request()->routeIs('admin.settings.sso*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>SSO Sabira Connect</p>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
    </div>
</aside>
