@php
    $user = auth()->user();
    $role = $user->role ?? null;
    $appSetting = \App\AppSettingManager::current();
    $avatarUrl = $user?->karyawan?->foto
        ? asset('storage/' . $user->karyawan->foto)
        : asset('images/default-photo.jpg');
    $brandName = $appSetting->app_name ?? config('app.name', 'Sabira Absensi');
    $brandLogo = $appSetting->app_logo
        ? asset('storage/' . $appSetting->app_logo)
        : asset('images/logo.png');
    $isGuru = $role === 'guru';
    $isKaryawan = $role === 'karyawan';
    $isOrganisasi = $role === 'organisasi';
@endphp

<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ route('dashboard') }}" class="brand-link">
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
                <span class="text-xs text-muted">{{ ucfirst($role ?? '-') }}</span>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') || request()->routeIs('karyawan.dashboard') || request()->routeIs('asrama.index') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-house-door-fill"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-person-check"></i>
                        <p>Profil</p>
                    </a>
                </li>

                @if($isKaryawan || $isGuru)
                    <li class="nav-header">ABSENSI</li>
                    <li class="nav-item">
                        <a href="{{ route('absensi.index') }}" class="nav-link {{ request()->routeIs('absensi.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-clipboard-check"></i>
                            <p>Absen</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('karyawan.history') }}" class="nav-link {{ request()->routeIs('karyawan.history') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-clock-history"></i>
                            <p>Riwayat Absensi</p>
                        </a>
                    </li>
                @endif

                @if($isGuru)
                    <li class="nav-header">GURU</li>
                    <li class="nav-item">
                        <a href="{{ route('guru.schedule') }}" class="nav-link {{ request()->routeIs('guru.schedule*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-calendar-week"></i>
                            <p>Jadwal Mengajar</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('guru.history.index') }}" class="nav-link {{ request()->routeIs('guru.history.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-journal-text"></i>
                            <p>Riwayat Mengajar</p>
                        </a>
                    </li>
                @endif

                @if($isOrganisasi)
                    <li class="nav-header">ASRAMA</li>
                    <li class="nav-item">
                        <a href="{{ route('asrama.sholat') }}" class="nav-link {{ request()->routeIs('asrama.sholat') || request()->routeIs('asrama.sholat.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-moon-stars-fill"></i>
                            <p>Absen Sholat</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('asrama.kegiatan') }}" class="nav-link {{ request()->routeIs('asrama.kegiatan') || request()->routeIs('asrama.kegiatan.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-calendar-event-fill"></i>
                            <p>Absen Kegiatan</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('asrama.sholat.history') }}" class="nav-link {{ request()->routeIs('asrama.sholat.history') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-clock-history"></i>
                            <p>Riwayat Sholat</p>
                        </a>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
</aside>
