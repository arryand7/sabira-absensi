# Sabira Absensi

## Blueprint Aplikasi

### Ringkasan
- Aplikasi absensi untuk sekolah/pesantren yang mencakup absensi karyawan/guru, absensi siswa per jadwal pelajaran, dan absensi kegiatan asrama (sholat/kegiatan).
- Mendukung multi-role dengan alur berbeda (admin, guru, karyawan, organisasi/asrama) dan pelaporan PDF/Excel.

### Peran & Akses
- admin: dashboard absensi harian, master data, jadwal, laporan, dan konfigurasi lokasi/SSO.
- guru: kelola jadwal pribadi, input absensi siswa per pertemuan, lihat histori.
- karyawan: check-in/out berbasis lokasi, lihat histori pribadi.
- organisasi (asrama): input absensi sholat & kegiatan asrama, lihat rekap.

### Modul Inti
- Autentikasi & Role: Laravel Breeze + middleware `checkRole` untuk isolasi akses.
- Absensi Karyawan/Guru: check-in/out berbasis geofence, validasi perangkat harian, histori & kalender.
- Master Data: karyawan, divisi, guru, siswa, kelas, mapel, tahun ajaran, wali kelas.
- Jadwal & Absensi Siswa: pengelolaan jadwal guru, deteksi bentrok, input absensi per pertemuan + materi.
- Asrama: master sholat berulang, kegiatan insidental, jadwal harian, absensi & histori.
- Laporan: rekap karyawan (filter divisi/jenis guru), detail karyawan, laporan siswa PDF, rekap mapel PDF/Excel.
- SSO Settings: konfigurasi SSO tersimpan di `app_settings` dan dapat diubah via admin.

### Alur Kerja Utama
1. Admin membuat tahun ajaran aktif, kelas, mapel, dan data pengguna.
2. Admin/ guru mengelola jadwal pelajaran (manual atau import).
3. Guru mengisi absensi siswa per jadwal dan pertemuan.
4. Karyawan/guru melakukan check-in/out dari lokasi yang sesuai radius geofence.
5. Organisasi/asrama mengelola absensi sholat dan kegiatan harian.
6. Admin menarik laporan dan export untuk kebutuhan rekap.

### Model Data (Ringkas)
- users: akun + role/status + SSO fields.
- karyawan (user_id, divisi_id) dan gurus (user_id, jenis).
- divisis: referensi unit kerja.
- academic_years: tahun ajaran aktif.
- class_groups: kelas per tahun ajaran + wali kelas.
- class_group_student: pivot siswa-kelas per tahun ajaran.
- subjects: mapel formal/muadalah.
- schedules: jadwal guru per kelas/mapel/hari/jam.
- student_attendance: absensi siswa per pertemuan.
- absensi_karyawans: check-in/out karyawan/guru + status + lokasi + device_hash.
- absensi_lokasis: titik koordinat dan radius geofence.
- kegiatan_asrama, jadwal_kegiatan_asrama, absensi_asrama: absensi asrama.
- app_settings: konfigurasi SSO yang tersimpan di DB.

### ERD (ASCII)
```
users (id) 1---1 karyawan (user_id)
users (id) 1---1 gurus (user_id)
users (id) 1---N schedules (user_id)
users (id) 1---N absensi_karyawans (user_id)

divisis (id) 1---N karyawan (divisi_id)

academic_years (id) 1---N class_groups (academic_year_id)
academic_years (id) 1---N schedules (academic_year_id)
academic_years (id) 1---N class_group_student (academic_year_id)

class_groups (id) N---N students (id) via class_group_student
class_groups (id) 1---N schedules (class_group_id)
subjects (id) 1---N schedules (subject_id)

schedules (id) 1---N student_attendance (schedule_id)
students (id) 1---N student_attendance (student_id)

absensi_lokasis (id) 1---N absensi_karyawans (geofence)

kegiatan_asrama (id) 1---N jadwal_kegiatan_asrama (kegiatan_asrama_id)
jadwal_kegiatan_asrama (id) 1---N absensi_asrama (jadwal_kegiatan_asrama_id)
students (id) 1---N absensi_asrama (student_id)

app_settings (id) 1---1 sso config (single row)
```

### Integrasi & Teknologi
- Backend: Laravel 10 (PHP ^8.1), Sanctum, Livewire 3.
- Frontend: Vite, TailwindCSS, Alpine.js, Flowbite.
- Export: `maatwebsite/excel` (Excel), `barryvdh/laravel-dompdf` (PDF).

### Struktur Kode Penting
- `routes/web.php`: definisi route per role.
- `app/Http/Controllers`: logika modul admin/guru/karyawan/asrama.
- `app/Livewire`: komponen kalender absensi & rekap sholat.
- `app/Models`: relasi data utama.
- `database/migrations`: struktur tabel inti.
- `app/Exports` dan `app/Imports`: Excel export/import.
- `resources/views`: UI per role (admin/guru/karyawan/organisasi).

## Setup Project Laravel

Setelah melakukan `git pull`, jalankan semua perintah berikut secara berurutan:

```bash
composer install
npm install
npm run build   # Jalankan jika ingin deploy/hosting
php artisan storage:link
php artisan key:generate

php artisan migrate # sesuaikan nama database di .env
php artisan db:seed --class=UserSeeder

# Login ke website menggunakan akun dari UserSeeder:

# Setelah login sebagai admin, buat tahun ajaran baru melalui halaman admin.
# jika sudah membuat tahun ajaran baru bisa seeder kelas juga
php artisan db:seed --class=ClassGroupSeeder
```

### Contoh .env (SSO)
Tambahkan keys ini di `.env` bila memakai SSO:

```
SSO_BASE_URL=https://gate.sabira-iibs.id
SSO_CLIENT_ID=
SSO_CLIENT_SECRET=
SSO_REDIRECT_URI=
SSO_SCOPES="openid profile email roles"
```

### Contoh Seed Data
- Buat admin awal:
  - `php artisan db:seed --class=UserSeeder`
- Buat data kelas (setelah membuat tahun ajaran aktif):
  - `php artisan db:seed --class=ClassGroupSeeder`
- Opsional: seed mapel (jika diperlukan):
  - `php artisan db:seed --class=SubjectSeeder`
