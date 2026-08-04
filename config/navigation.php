<?php

return [
    [
        'label' => 'Beranda',
        'items' => [
            ['label' => 'Beranda', 'route' => 'dashboard', 'icon' => 'fas fa-house', 'roles' => ['super_admin', 'admin', 'guru', 'karyawan', 'organisasi'], 'active' => ['dashboard', 'admin.dashboard', 'guru.dashboard', 'karyawan.dashboard']],
        ],
    ],
    [
        'label' => 'Operasional Hari Ini',
        'items' => [
            ['label' => 'Jadwal Hari Ini', 'route' => 'guru.schedule', 'icon' => 'far fa-calendar-check', 'roles' => ['guru'], 'active' => ['guru.schedule*']],
            ['label' => 'Riwayat Mengajar', 'route' => 'guru.history.index', 'icon' => 'fas fa-book-open-reader', 'roles' => ['guru'], 'active' => ['guru.history*']],
            ['label' => 'Kehadiran Kerja', 'route' => 'absensi.index', 'icon' => 'fas fa-fingerprint', 'roles' => ['guru', 'karyawan'], 'active' => ['absensi.*']],
            ['label' => 'Riwayat Kehadiran', 'route' => 'karyawan.history', 'icon' => 'fas fa-clock-rotate-left', 'roles' => ['karyawan'], 'active' => ['karyawan.history']],
            ['label' => 'Aktivitas Asrama', 'route' => 'asrama.index', 'icon' => 'fas fa-building-user', 'roles' => ['organisasi'], 'active' => ['asrama.index']],
            ['label' => 'Absensi Sholat', 'route' => 'asrama.sholat', 'icon' => 'fas fa-mosque', 'roles' => ['organisasi'], 'active' => ['asrama.sholat*']],
            ['label' => 'Kegiatan Asrama', 'route' => 'asrama.kegiatan', 'icon' => 'fas fa-list-check', 'roles' => ['organisasi'], 'active' => ['asrama.kegiatan*']],
        ],
    ],
    [
        'label' => 'Akademik',
        'items' => [
            ['label' => 'Program Pendidikan', 'route' => 'admin.education-programs.index', 'icon' => 'fas fa-graduation-cap', 'roles' => ['super_admin', 'admin'], 'active' => ['admin.education-programs*']],
            ['label' => 'Kelompok Kelas', 'route' => 'admin.class-groups.index', 'icon' => 'fas fa-layer-group', 'roles' => ['super_admin', 'admin'], 'active' => ['admin.class-groups*']],
            ['label' => 'Keanggotaan Siswa', 'route' => 'promotion.index', 'icon' => 'fas fa-arrow-right-arrow-left', 'roles' => ['super_admin', 'admin'], 'active' => ['promotion*']],
            ['label' => 'Mata Pelajaran', 'route' => 'subjects.index', 'icon' => 'fas fa-book', 'roles' => ['super_admin', 'admin'], 'active' => ['subjects*']],
            ['label' => 'Jadwal Guru', 'route' => 'admin.schedules.index', 'icon' => 'fas fa-calendar-days', 'roles' => ['super_admin', 'admin'], 'active' => ['admin.schedules*']],
            ['label' => 'Kebijakan Jam', 'route' => 'admin.schedule-time-slots.index', 'icon' => 'fas fa-clock', 'roles' => ['super_admin', 'admin'], 'active' => ['admin.schedule-time-slots*']],
            ['label' => 'Benturan Jadwal', 'route' => 'admin.schedule-conflicts.index', 'icon' => 'fas fa-triangle-exclamation', 'roles' => ['super_admin', 'admin'], 'active' => ['admin.schedule-conflicts*'], 'badge' => 'schedule_conflicts'],
            ['label' => 'Tahun Ajaran', 'route' => 'academic-years.index', 'icon' => 'fas fa-calendar', 'roles' => ['super_admin', 'admin'], 'active' => ['academic-years*']],
        ],
    ],
    [
        'label' => 'Absensi',
        'items' => [
            ['label' => 'Absensi Siswa', 'route' => 'laporan.murid', 'icon' => 'fas fa-user-check', 'roles' => ['super_admin', 'admin'], 'active' => ['laporan.murid*']],
            ['label' => 'Kehadiran Mengajar', 'route' => 'laporan.pertemuan', 'icon' => 'fas fa-person-chalkboard', 'roles' => ['super_admin', 'admin'], 'active' => ['laporan.pertemuan*']],
            ['label' => 'Kehadiran Kerja', 'route' => 'laporan.karyawan', 'icon' => 'fas fa-briefcase', 'roles' => ['super_admin', 'admin'], 'active' => ['laporan.karyawan*']],
            ['label' => 'Koreksi Kehadiran', 'route' => 'admin.attendance-corrections.index', 'icon' => 'fas fa-pen-to-square', 'roles' => ['super_admin', 'admin'], 'active' => ['admin.attendance-corrections*']],
        ],
    ],
    [
        'label' => 'Laporan',
        'items' => [
            ['label' => 'Progres Siswa', 'route' => 'laporan.murid', 'icon' => 'fas fa-chart-line', 'roles' => ['super_admin', 'admin'], 'active' => ['laporan.murid*']],
            ['label' => 'Pelaksanaan Mengajar', 'route' => 'laporan.pertemuan', 'icon' => 'fas fa-chart-column', 'roles' => ['super_admin', 'admin'], 'active' => ['laporan.pertemuan*']],
            ['label' => 'Laporan Kepegawaian', 'route' => 'laporan.karyawan', 'icon' => 'fas fa-chart-pie', 'roles' => ['super_admin', 'admin'], 'active' => ['laporan.karyawan*']],
            ['label' => 'Executive Overview', 'route' => 'admin.dashboard', 'icon' => 'fas fa-gauge-high', 'roles' => ['super_admin', 'admin'], 'active' => ['admin.dashboard']],
        ],
    ],
    [
        'label' => 'Pengguna dan Akses',
        'items' => [
            ['label' => 'Pengguna', 'route' => 'users.index', 'icon' => 'fas fa-users-gear', 'roles' => ['super_admin', 'admin'], 'active' => ['users*']],
            ['label' => 'Guru dan Karyawan', 'route' => 'karyawan.index', 'icon' => 'fas fa-id-card', 'roles' => ['super_admin', 'admin'], 'active' => ['karyawan.index', 'karyawan.create', 'karyawan.show', 'karyawan.edit']],
            ['label' => 'Siswa', 'route' => 'admin.students.index', 'icon' => 'fas fa-user-graduate', 'roles' => ['super_admin', 'admin'], 'active' => ['admin.students*']],
            ['label' => 'Divisi dan Unit', 'route' => 'divisis.index', 'icon' => 'fas fa-sitemap', 'roles' => ['super_admin', 'admin'], 'active' => ['divisis*']],
        ],
    ],
    [
        'label' => 'Integrasi',
        'items' => [
            ['label' => 'Sinkronisasi Gate', 'route' => 'admin.sync.index', 'icon' => 'fas fa-rotate', 'roles' => ['super_admin'], 'active' => ['admin.sync*']],
            ['label' => 'Konfigurasi SSO', 'route' => 'admin.settings.sso', 'icon' => 'fas fa-key', 'roles' => ['super_admin'], 'active' => ['admin.settings.sso*']],
        ],
    ],
    [
        'label' => 'Pengaturan',
        'items' => [
            ['label' => 'Profil Aplikasi', 'route' => 'admin.settings.app', 'icon' => 'fas fa-sliders', 'roles' => ['super_admin', 'admin'], 'active' => ['admin.settings.app*']],
            ['label' => 'Lokasi dan Geofence', 'route' => 'admin.lokasi.edit', 'icon' => 'fas fa-location-dot', 'roles' => ['super_admin', 'admin'], 'active' => ['admin.lokasi*']],
            ['label' => 'Profil Saya', 'route' => 'profile.edit', 'icon' => 'fas fa-circle-user', 'roles' => ['super_admin', 'admin', 'guru', 'karyawan', 'organisasi', 'siswa', 'wali'], 'active' => ['profile.*']],
        ],
    ],
];
