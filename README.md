# Sabira Absensi

## Overview
Sabira Absensi adalah aplikasi absensi sekolah/pesantren yang mencakup absensi karyawan/guru, absensi siswa per jadwal, jurnal pertemuan, penjadwalan guru, serta absensi kegiatan asrama (sholat/kegiatan). Aplikasi ini multi-role dan mendukung export laporan PDF/Excel.

## Roles and Access
- Admin: kelola master data, jadwal, laporan, konfigurasi aplikasi, dan monitoring absensi.
- Guru: kelola jadwal pribadi, input absensi siswa per pertemuan, lihat histori.
- Karyawan: check-in/out berbasis lokasi dan histori pribadi.
- Organisasi/Asrama: input absensi sholat/kegiatan harian dan rekap.

## Core Modules
- Jadwal Guru: tambah/edit jadwal, deteksi bentrok guru/kelas, import Excel.
- Absensi Siswa: input absen per jadwal dan sesi, simpan materi/jurnal.
- Pertemuan: `schedule_sessions` menyimpan sesi per tanggal, `meeting_no` unik per mapel + kelas + tahun ajaran (reset tiap tahun ajaran).
- Absensi Karyawan/Guru: check-in/out geofence, validasi perangkat, histori.
- Asrama: master sholat, kegiatan insidental, jadwal harian, absensi.
- Laporan: export PDF/Excel untuk karyawan, siswa per kelas, per mapel, dan rekap pertemuan.
- Pengaturan Aplikasi: nama aplikasi, logo, deskripsi, dan profil pengguna (termasuk foto).

## Tech Stack
- Backend: Laravel 10 (PHP ^8.1), Sanctum, Livewire 3.
- Frontend: Vite, TailwindCSS, Alpine.js, AdminLTE.
- Export: `maatwebsite/excel` (Excel), `barryvdh/laravel-dompdf` (PDF).

## Data Model (Ringkas)
- `users`, `gurus`, `karyawan`
- `academic_years`, `class_groups`, `class_group_student`
- `subjects`, `schedules`
- `schedule_sessions` (sesi per tanggal) dan `student_attendance`
- `absensi_karyawans`, `absensi_lokasis`
- `kegiatan_asrama`, `jadwal_kegiatan_asrama`, `absensi_asrama`
- `app_settings` (konfigurasi aplikasi/SSO)

## Local Setup
Jalankan perintah berikut setelah `git pull`:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate

# Set konfigurasi database di .env lalu migrasi
php artisan migrate

# Seed data awal
php artisan db:seed --class=UserSeeder

# Buat tahun ajaran aktif lewat UI, lalu seed kelas dan mapel bila perlu
php artisan db:seed --class=ClassGroupSeeder
php artisan db:seed --class=SubjectSeeder

# Untuk penyimpanan foto/logo
php artisan storage:link
```

Jalankan mode dev:
```bash
npm run dev
php artisan serve
```

## Environment Variables
Contoh konfigurasi SSO (opsional):
```
SSO_BASE_URL=https://gate.sabira-iibs.id
SSO_CLIENT_ID=
SSO_CLIENT_SECRET=
SSO_REDIRECT_URI=
SSO_SCOPES="openid profile email roles"
```

## Deployment Notes
- Pastikan `APP_URL` dan kredensial database sudah benar.
- Jalankan `npm run build` untuk menghasilkan `public/build`.
- Jalankan migrasi produksi: `php artisan migrate --force`.
- Opsional: cache config dan route:
  - `php artisan config:cache`
  - `php artisan route:cache`
  - `php artisan view:cache`

## Credits
Created by Ryand Arifriantoni in collaboration with TelkomUniversity.
