# Sabira Absensi

[![CI](https://github.com/arryand7/sabira-absensi/actions/workflows/ci.yml/badge.svg)](https://github.com/arryand7/sabira-absensi/actions/workflows/ci.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

Sabira Absensi adalah aplikasi operasional sekolah dan pesantren berbasis Laravel 13 untuk penjadwalan guru, jurnal pembelajaran, absensi siswa per sesi, kehadiran mengajar guru, presensi kerja berbasis geofence, kegiatan asrama, laporan manajemen, dan sinkronisasi identitas Gate SSO.

## Fitur Utama

- Jadwal guru dinamis per tahun ajaran, guru, kelas, mata pelajaran, hari, dan slot jam pelajaran.
- Penugasan guru pengganti resmi untuk tanggal tertentu.
- Alur mengajar: pilih jadwal, isi jurnal dan kondisi kelas, isi seluruh absensi siswa aktif, validasi lokasi server-side, lalu selesaikan sesi.
- Draft sesi otomatis yang dapat dilanjutkan tanpa membuat duplikasi.
- Nomor pertemuan unik per mata pelajaran, kelas, dan tahun ajaran sehingga kembali dimulai pada tahun ajaran baru.
- Kehadiran mengajar guru tercatat atomik bersama jurnal dan absensi siswa.
- Koreksi sesi selesai melalui pengajuan guru dan approval/rejection admin dengan snapshot sebelum/sesudah.
- Presensi kerja karyawan dan guru dengan check-in, checkout, radius lokasi, histori, serta koreksi admin.
- Laporan siswa per individu, kelas, dan mata pelajaran; laporan pelaksanaan mengajar; export PDF dan Excel.
- Dashboard operasional berbasis database: jadwal, sesi selesai/belum dilaporkan, konflik, koreksi, risiko siswa, anomali guru/geofence, dan presensi kerja.
- Sinkronisasi Gate SSO: dry-run delapan kategori, pilihan tindakan, apply transactional, report-back, retry, dan histori.
- Pengaturan profil aplikasi, logo, deskripsi, SSO, lokasi, profil pengguna, dan foto.
- Modul absensi sholat serta kegiatan asrama.

## Role Aktual

| Role | Akses utama |
| --- | --- |
| `super_admin` | Semua fungsi admin dan Gate Sync |
| `admin` | Dashboard, master data, jadwal, koreksi, pengguna, dan laporan |
| `guru` | Dashboard guru, jadwal, sesi mengajar, histori/koreksi, dan presensi kerja |
| `karyawan` | Check-in, checkout, dan histori presensi pribadi |
| `organisasi` | Absensi sholat, kegiatan, dan histori asrama |
| `siswa` | Identitas siswa hasil provisioning Gate; akses profil pribadi |
| `wali` | Identitas orang tua/wali hasil provisioning Gate; akses profil pribadi |

Role disimpan pada database dan authorization diterapkan pada route, request/policy, handler, query kepemilikan, serta export. Self-registration publik dinonaktifkan; akun dibuat oleh superadmin atau Gate Sync.

`users.type`, `users.application_role`, dan `users.role` mempunyai arti berbeda. Tipe canonical Gate (`student`, `teacher`, `parent`, `staff`, `admin`) dipetakan melalui `GateUserMapper` ke role otorisasi lokal; nilai Gate tidak pernah ditulis langsung ke enum `users.role`. Mapping yang belum dikenal ditahan sebagai conflict pada dry-run.

## Arsitektur Domain

```text
Menu / Browser
  → named route + role middleware
  → controller + Form Request / Policy
  → service transaction
  → Eloquent model + database constraint
  → canonical Blade app shell + Tailwind/Alpine/Livewire
  → feature tests
```

Model inti:

- Identitas: `User`, `Guru`, `Karyawan`, `Divisi`.
- Akademik: `AcademicYear`, `EducationProgram`, `ClassGroup`, `ClassGroupStudent`, `Subject`, `Schedule`.
- Pembelajaran: `ScheduleSession`, `Attendance`, `TeacherTeachingAttendance`, `AttendanceCorrection`.
- Presensi kerja: `AbsensiKaryawan`, `AbsensiLokasi`.
- Asrama: `KegiatanAsrama`, `JadwalKegiatanAsrama`, `AbsensiAsrama`.
- Integrasi: `GateSyncRun`, `GateSyncItem`, `AppSetting`.

Knowledge graph arsitektur dapat dibuat secara lokal dengan Graphify dan sengaja tidak disimpan di repository karena merupakan artefak generated. Audit menu-to-database dan confidence Graphify didokumentasikan di [docs/GRAPHIFY-UI-INTEGRATION-AUDIT.md](docs/GRAPHIFY-UI-INTEGRATION-AUDIT.md).

## Kebutuhan Sistem

- PHP `^8.2` (baseline proyek terverifikasi pada PHP 8.4.1)
- Composer 2
- MySQL/MariaDB
- Node.js dan npm
- Extension PHP yang dibutuhkan Laravel, DomPDF, dan PhpSpreadsheet

Frontend interaktif menggunakan satu pipeline Vite 8 dengan Tailwind CSS, Alpine.js, Livewire, ikon lokal, dan Flowbite yang diisolasi untuk komponen modal. Seluruh role memakai canonical app shell yang sama; AdminLTE, Bootstrap layout, jQuery, dan DataTables tidak lagi digunakan oleh route aktif. Export menggunakan `maatwebsite/excel` dan `barryvdh/laravel-dompdf`.

Tema menyediakan mode `System`, `Light`, dan `Dark`. Pilihan disimpan pada `localStorage` dengan key `sabira-theme`; mode System mengikuti perubahan `prefers-color-scheme` tanpa reload.

## Instalasi Lokal

```bash
git clone <repository-url>
cd sabira-absensi
composer install
npm ci
cp .env.example .env
php artisan key:generate
```

Atur koneksi database pada `.env`, lalu:

```bash
php artisan migrate
php artisan db:seed
php artisan storage:link
npm run build
php artisan serve
```

`UserSeeder` menggunakan `SABIRA_ADMIN_EMAIL` dan `SABIRA_ADMIN_PASSWORD`. Pada environment lokal, password acak akan dicetak sekali bila akun belum ada dan password tidak diisi. Pada production, `SABIRA_ADMIN_PASSWORD` wajib diisi ketika membuat akun awal. Seeder tidak mengubah password akun yang sudah ada.

Untuk development frontend:

```bash
npm run dev
php artisan serve
```

Jangan commit `.env`, credential Gate, file `public/hot`, atau data export pengguna.

## Konfigurasi Environment

```dotenv
APP_NAME="Sabira Absensi"
APP_ENV=local
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lara_absensi
DB_USERNAME=root
DB_PASSWORD=

SSO_BASE_URL=https://gate.sabira-iibs.id
SSO_CLIENT_ID=
SSO_CLIENT_SECRET=
SSO_REDIRECT_URI=http://localhost:8000/sso/callback
SSO_SCOPES="openid profile email roles"

SABIRA_ADMIN_EMAIL=admin@sabira.test
SABIRA_ADMIN_PASSWORD=
```

Gunakan `127.0.0.1`, bukan nama host container yang tidak tersedia dari host macOS. Jika XAMPP memakai port selain `3306`, samakan `DB_PORT`, kemudian jalankan `php artisan optimize:clear` sebelum mencoba ulang.

## Quality Gate

Jalankan sebelum membuat pull request atau deployment:

```bash
php artisan optimize:clear
php artisan route:list --except-vendor
php artisan migrate:status
php artisan test
vendor/bin/pint --test
npm run build
composer validate --strict
composer audit
graphify update .
```

Baseline UI akhir 3 Agustus 2026:

- Laravel 13.22.0 dan PHP 8.4.1.
- Seluruh migration berstatus `Ran`.
- 98 test dengan 744 assertion lulus, termasuk canonical shell lintas-role, kontrak lima tipe user Gate, kebijakan jam dinamis, jadwal lintas-program, signature PDF/Excel, dan authorization export.
- Build Vite 8 lulus; audit npm dan Composer tidak menemukan advisory keamanan.
- Warning non-blocking: metadata doc-comment PHPUnit lama dan skema konfigurasi PHPUnit deprecated.

## Deployment

Contoh urutan deployment setelah backup database dan konfigurasi `.env` production:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Pastikan web server mengarah ke direktori `public/`, `storage/` dan `bootstrap/cache/` dapat ditulis, HTTPS aktif, scheduler/queue dikonfigurasi bila digunakan, serta kredensial Gate tidak berada di repository. Jangan menjalankan `migrate:fresh`, `db:wipe`, atau seeder data demo pada database production.

## Troubleshooting

- `SQLSTATE[HY000] [2002] Connection refused`: periksa MySQL/XAMPP, host, port, dan credential `.env`; lalu `php artisan optimize:clear`.
- `Duplicate column name`: jangan mengulang atau mengedit migration yang sudah pernah dijalankan. Periksa `php artisan migrate:status` dan buat migration korektif yang idempotent bila schema lama berbeda.
- `Target class [Seeder] does not exist`: pastikan class seeder benar-benar ada dan terdaftar di `DatabaseSeeder`; jalankan `composer dump-autoload` setelah menambah class.
- Asset mencoba port Vite: pastikan `public/hot` tidak ada di production dan jalankan `npm run build`.
- Gate dry-run gagal: periksa `SSO_BASE_URL`, `SSO_CLIENT_ID`, `SSO_CLIENT_SECRET`, HTTPS, dan akses endpoint provisioning.

## Dokumentasi Tambahan

- [Audit Graphify dan integrasi UI](docs/GRAPHIFY-UI-INTEGRATION-AUDIT.md)
- [Audit canonical app shell dan single-layout UI](docs/UI-SINGLE-LAYOUT-AUDIT.md)
- [Ketentuan sinkronisasi Gate](SYNC-TO-GATE.md)
- [Catatan upgrade Laravel 13](docs/LARAVEL-13-UPGRADE.md)

## Kredit dan Hak Cipta

Copyright © 2026 Ryand Arifriantoni (`arryand7@gmail.com`). Dibuat oleh Ryand Arifriantoni berkolaborasi dengan TelkomUniversity.

Kode sumber dirilis menggunakan [Lisensi MIT](LICENSE). Nama, logo, data operasional, dan identitas organisasi yang terpisah dari kode sumber tetap mengikuti hak pemilik masing-masing.
