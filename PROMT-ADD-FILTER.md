Anda bertindak sebagai senior Laravel 13 engineer, Blade/Alpine frontend engineer,
database engineer, dan QA engineer yang bekerja langsung pada repository Sabira Absensi.

Saya melampirkan screenshot halaman:

/promotion

Halaman tersebut digunakan admin untuk memilih siswa dan memasukkan atau memindahkan
mereka ke kelas tujuan.

Saat ini jumlah siswa cukup banyak, tetapi halaman hanya menampilkan checkbox, nama,
dan NIS. Saya ingin menambahkan filter server-side agar admin lebih mudah mencari dan
memilih siswa.

Kerjakan hanya fitur halaman Keanggotaan Siswa ini. Jangan mengubah modul lain yang
tidak berkaitan.

# KONDISI APLIKASI

- Laravel 13.22.0.
- PHP 8.4.1.
- Aplikasi sudah menggunakan satu canonical app shell.
- Theme System, Light, dan Dark sudah tersedia.
- AdminLTE, Bootstrap layout lama, jQuery, dan DataTables sudah tidak digunakan.
- Graphify sudah terpasang.
- Siswa dapat memiliki beberapa keanggotaan kelas.
- Program pendidikan terdiri dari Formal dan Muadalah.
- Jenis kelas terdiri dari Reguler dan Nonreguler.
- Histori keanggotaan siswa tidak boleh dihapus.
- Working tree sudah memiliki banyak perubahan lokal yang harus dipertahankan.

# TUJUAN

Tambahkan sistem pencarian, filter, pagination, dan pemilihan siswa pada halaman
Keanggotaan Siswa agar admin dapat:

1. Mencari siswa berdasarkan nama atau NIS.
2. Memfilter berdasarkan program pendidikan.
3. Memfilter berdasarkan jenis kelas.
4. Memfilter berdasarkan kelas aktif atau kelas asal.
5. Memfilter berdasarkan tingkat jika field tersebut tersedia.
6. Memfilter berdasarkan status keanggotaan.
7. Menyembunyikan siswa yang sudah berada di kelas tujuan.
8. Memilih siswa dari hasil filter.
9. Mempertahankan pilihan saat berpindah halaman pagination.
10. Melakukan proses tambah atau pindah kelas secara aman.

# ATURAN KERJA

1. Audit implementasi aktual sebelum mengubah kode.
2. Jangan mengasumsikan nama model, tabel, kolom, atau route.
3. Verifikasi semuanya dari migration, model, route, controller, Blade, dan test.
4. Gunakan Graphify untuk membantu memetakan alur halaman /promotion.
5. Verifikasi hasil Graphify langsung ke source code.
6. Jangan menjalankan migrate:fresh, db:wipe, atau operasi destruktif.
7. Jangan menghapus histori keanggotaan siswa.
8. Jangan menganggap satu siswa hanya boleh berada di satu kelas.
9. Jangan menggunakan jQuery atau DataTables.
10. Jangan memuat seluruh siswa ke memory lalu memfilter Collection.
11. Gunakan filter server-side melalui query database.
12. Pertahankan canonical app shell dan theme yang sudah ada.
13. Jangan menggunakan data dummy.
14. Jangan membuat halaman placeholder.
15. Jangan push atau deploy.
16. Jangan mengubah desain global aplikasi.
17. Jangan mengubah business rule kelas tanpa memeriksa implementasi aktual.
18. Seluruh input dari browser harus divalidasi ulang di server.
19. Gunakan database transaction untuk bulk operation.
20. Jangan mengklaim selesai tanpa menjalankan test dan frontend build.

# FASE 1 — BASELINE DAN AUDIT

Jalankan:

pwd
git branch --show-current
git status --short
git rev-parse HEAD
php -v
composer show laravel/framework
php artisan route:list --except-vendor
php artisan migrate:status
php artisan test
npm run build

Periksa Graphify menggunakan syntax yang benar dari instalasi lokal:

graphify --help

Gunakan Graphify untuk menelusuri:

- route /promotion;
- controller atau Livewire component;
- view halaman Keanggotaan Siswa;
- model siswa;
- class_groups;
- class_group_student;
- education_programs;
- academic_years;
- proses tambah atau pindah siswa;
- authorization;
- test terkait.

Jalankan query Graphify yang setara dengan:

"Trace the /promotion page from route to controller, Blade view, models, pivot
membership, validation, authorization, and tests."

"How are students currently added or moved between class groups?"

"Which fields distinguish Formal, Muadalah, Reguler, and Nonreguler memberships?"

Jika command query Graphify berbeda, gunakan syntax yang tersedia pada versi lokal.

Sebelum implementasi, berikan ringkasan singkat:

- route dan handler aktual;
- model dan tabel yang digunakan;
- business rule tambah/pindah saat ini;
- file yang akan diubah;
- test yang akan ditambahkan.

Setelah itu lanjutkan implementasi tanpa berhenti hanya pada laporan audit.

# FASE 2 — FILTER SERVER-SIDE

Tambahkan filter berikut pada query halaman /promotion.

## 1. Pencarian

Satu input pencarian untuk:

- nama siswa;
- NIS.

Ketentuan:

- mendukung partial search;
- whitespace di-trim;
- query kosong tidak memberi kondisi tambahan;
- gunakan escaping dan query builder/Eloquent yang aman;
- jangan menyusun raw SQL dari input pengguna.

Parameter URL yang disarankan:

search

Contoh:

/promotion?search=achmad

## 2. Program pendidikan

Filter:

- Formal;
- Muadalah.

Gunakan relasi kelas atau membership aktual dalam database.

Parameter:

program_id

atau parameter lain yang konsisten dengan struktur aktual.

## 3. Jenis kelas

Filter:

- Reguler;
- Nonreguler.

Parameter:

class_type

## 4. Kelas aktif atau kelas asal

Admin dapat memilih satu kelas untuk melihat siswa yang memiliki membership aktif
pada kelas tersebut.

Parameter:

source_class_group_id

## 5. Tingkat

Tambahkan filter tingkat hanya jika field grade_level atau padanannya benar-benar
tersedia pada schema.

Jangan membuat migration baru hanya untuk filter ini jika field belum tersedia.

## 6. Status keanggotaan

Pilihan minimal:

- Semua siswa;
- Memiliki keanggotaan aktif;
- Belum memiliki keanggotaan aktif;
- Sudah berada di kelas tujuan;
- Belum berada di kelas tujuan.

## 7. Sembunyikan anggota kelas tujuan

Tambahkan checkbox:

“Sembunyikan siswa yang sudah berada di kelas tujuan”

Behavior:

- aktif secara default setelah kelas tujuan dipilih;
- dapat dinonaktifkan admin;
- harus menggunakan query server-side;
- kelas tujuan diambil dari input target yang benar.

# FASE 3 — URL DAN QUERY STATE

Simpan filter di query string agar:

- filter tetap tersedia saat reload;
- pagination mempertahankan filter;
- URL dapat digunakan kembali;
- tombol Back browser bekerja dengan benar.

Gunakan:

->withQueryString()

atau mekanisme Laravel yang setara.

Tombol Reset Filter harus:

- menghapus search dan seluruh filter;
- mempertahankan kelas tujuan jika UX saat ini memerlukannya;
- tidak mereset theme atau navigasi.

# FASE 4 — UI FILTER

Gunakan canonical app shell dan komponen UI yang sudah tersedia.

Di bawah pilihan Kelas Tujuan, buat filter bar.

Desktop:

- Search nama/NIS sebagai field utama.
- Program.
- Jenis kelas.
- Kelas aktif/asal.
- Status.
- Per halaman.
- Reset Filter.

Mobile:

- Search tetap terlihat.
- Filter lain dapat berada dalam panel collapsible atau filter drawer.
- Tampilkan jumlah filter aktif.
- Tidak boleh ada horizontal overflow yang tidak diperlukan.

Tampilkan active filter chips, misalnya:

Formal ×
Reguler ×
XI Sains 1 ×

Gunakan komponen yang sudah ada seperti:

<x-filter-bar>
<x-input>
<x-select>
<x-button>
<x-status-badge>

atau komponen aktual yang sesuai repository.

Jangan membuat design system baru.

# FASE 5 — TABEL HASIL

Perbarui tabel menjadi:

- Checkbox.
- Nama.
- NIS.
- Program.
- Kelas Aktif.
- Jenis Kelas.
- Status.

Jika satu siswa memiliki beberapa kelas aktif:

- tampilkan maksimal beberapa badge ringkas;
- sediakan indikator “+N kelas” atau detail;
- jangan membuat baris terlalu tinggi;
- jangan memilih satu kelas secara acak sebagai satu-satunya kelas.

Status yang dapat ditampilkan sesuai data aktual:

- Belum memiliki kelas;
- Anggota aktif;
- Sudah di kelas tujuan;
- Memiliki beberapa kelas;
- Keanggotaan tidak aktif.

Tambahkan:

- pagination server-side;
- pilihan 25, 50, dan 100 data per halaman;
- summary:
  “Menampilkan X–Y dari Z siswa”;
- empty state;
- pesan khusus jika tidak ada hasil filter.

Untuk mobile, gunakan responsive table atau card list yang tetap mudah dipilih.

# FASE 6 — PEMILIHAN SISWA

Admin harus dapat:

1. Memilih satu siswa.
2. Memilih semua siswa pada halaman aktif.
3. Membatalkan semua pilihan.
4. Melihat jumlah pilihan.

Tampilkan:

“12 siswa dipilih”

Pilihan harus tetap tersedia ketika:

- berpindah pagination;
- mengubah filter;
- menutup dan membuka panel filter.

Gunakan implementasi yang sesuai arsitektur aktual:

- Livewire state;
- session-backed selection;
- atau hidden input/state JavaScript native yang aman.

Jangan menggunakan jQuery.

Pada saat submit:

- seluruh selected student IDs wajib divalidasi ulang;
- jangan percaya daftar ID dari browser;
- pastikan siswa masih ada;
- pastikan kelas tujuan valid;
- pastikan authorization masih berlaku;
- pastikan duplicate membership tidak dibuat.

Jika dukungan “pilih semua hasil filter” terlalu berisiko untuk implementasi saat ini,
prioritaskan:

- pilih semua halaman aktif;
- selection persisten antar-pagination.

Jelaskan keterbatasan tersebut dengan jujur.

# FASE 7 — TINDAKAN KEANGGOTAAN

Audit behavior halaman saat ini sebelum mengubahnya.

Jika halaman memang digunakan untuk dua tindakan, bedakan secara jelas:

## A. Tambahkan ke kelas

Dipakai untuk:

- kelas nonreguler;
- keanggotaan tambahan;
- siswa yang boleh berada pada beberapa kelas.

Behavior:

- keanggotaan lama tidak ditutup;
- buat membership target jika belum tersedia;
- duplicate active membership tidak boleh dibuat;
- siswa yang sudah berada di target dilewati dan dilaporkan.

## B. Pindahkan kelas reguler

Dipakai untuk perpindahan kelas reguler.

Behavior:

- hanya valid jika target adalah kelas reguler;
- cari membership reguler aktif pada program dan tahun ajaran yang sama;
- tutup membership reguler lama menggunakan status/left_at sesuai schema;
- buat membership target;
- jangan menghapus histori;
- jangan menutup membership nonreguler;
- jalankan seluruh bulk action dalam transaction.

Jika implementasi lama hanya mendukung satu tindakan, jangan memaksakan perubahan besar
tanpa menjelaskan dampaknya.

Tetapi minimal pastikan filter baru tidak merusak business rule yang sudah ada.

# FASE 8 — PREVIEW DAN KONFIRMASI

Sebelum proses bulk final, tampilkan ringkasan:

- Kelas tujuan.
- Program.
- Jenis kelas.
- Mode tindakan.
- Jumlah siswa dipilih.
- Jumlah yang sudah menjadi anggota.
- Jumlah yang akan diproses.
- Jumlah yang akan dilewati.
- Jumlah membership lama yang akan ditutup jika mode pindah.

Contoh:

Kelas tujuan: XI Sains 1
Program: Formal
Jenis: Reguler
Mode: Pindahkan kelas reguler
Dipilih: 15 siswa
Akan dipindahkan: 13 siswa
Sudah berada di kelas tujuan: 2 siswa

Gunakan confirm dialog yang sudah tersedia pada design system aplikasi.

Tombol akhir harus menjelaskan dampak:

“Tambahkan 10 Siswa”

atau:

“Pindahkan 13 Siswa”

Jangan hanya menggunakan label “Proses”.

# FASE 9 — VALIDASI, TRANSACTION, DAN AUDIT

Validasi minimal:

- kelas tujuan ada;
- kelas tujuan aktif;
- tahun ajaran valid;
- seluruh student IDs valid;
- action/mode valid;
- target nonreguler tidak menerima mode pindah kelas reguler;
- duplicate membership tidak dibuat;
- perubahan data setelah preview diperiksa ulang.

Gunakan database transaction untuk bulk operation.

Gunakan audit mechanism yang sudah tersedia jika ada.

Catat minimal:

- action;
- target class;
- jumlah siswa;
- actor/admin;
- timestamp;
- hasil processed/skipped/failed.

Jangan membuat sistem audit kedua jika aplikasi sudah memiliki audit log.

# FASE 10 — HASIL PROSES

Setelah proses, tampilkan ringkasan yang konkret:

Berhasil ditambahkan: 10
Berhasil dipindahkan: 0
Dilewati karena sudah menjadi anggota: 2
Gagal: 1

Jika ada kegagalan per siswa:

- tampilkan nama/NIS dan alasan yang aman;
- jangan menggagalkan seluruh proses secara diam-diam;
- jika operasi harus atomic berdasarkan aturan saat ini, rollback seluruh transaction
  dan tampilkan alasan yang jelas.

Pilih strategi atomic atau partial success berdasarkan business behavior aktual dan
jelaskan keputusan tersebut.

# FASE 11 — INDEX DAN PERFORMA

Periksa query yang dihasilkan.

Pastikan tidak terjadi N+1 query pada:

- membership;
- class group;
- education program;
- academic year.

Gunakan eager loading dan `whereHas`/`whereDoesntHave` secara tepat.

Periksa index yang sudah tersedia untuk:

- student/user identifier;
- NIS;
- class_group_student.student_id;
- class_group_student.class_group_id;
- status;
- academic year jika dipakai.

Jangan menambah index secara membabi buta.

Jika index baru diperlukan:

- buat migration baru yang reversible;
- jelaskan query yang dibantu;
- jangan mengubah data.

# FASE 12 — AUTOMATED TEST

Tambahkan test minimal:

1. Admin dapat membuka halaman /promotion.
2. Superadmin dapat membuka halaman.
3. User tidak berwenang ditolak.
4. Search nama bekerja.
5. Search NIS bekerja.
6. Filter program bekerja.
7. Filter jenis kelas bekerja.
8. Filter kelas aktif bekerja.
9. Filter belum memiliki kelas bekerja.
10. Filter sudah berada di kelas tujuan bekerja.
11. Checkbox sembunyikan anggota target bekerja.
12. Pagination mempertahankan filter.
13. Per-page bekerja.
14. Tampilan memakai canonical app shell.
15. Light/Dark/System controls tetap tersedia.
16. Tambah ke kelas tidak menghapus membership lama.
17. Duplicate membership tidak dibuat.
18. Pindah reguler menutup membership reguler lama.
19. Pindah reguler tidak menutup membership nonreguler.
20. Target nonreguler menolak mode pindah reguler.
21. Selected IDs divalidasi ulang.
22. User tidak berwenang tidak dapat menjalankan bulk action.
23. Hasil proses menampilkan summary yang benar.
24. Query tidak menghasilkan regression pada fitur lama.

Sesuaikan test dengan business rule aktual.

Jalankan:

php artisan optimize:clear
php artisan test
npm run build
vendor/bin/pint --test
composer audit

Jangan mengklaim berhasil jika salah satu command gagal.

# FASE 13 — GRAPHIFY UPDATE

Setelah implementasi:

- perbarui Graphify menggunakan syntax lokal yang benar;
- jangan mengarang command jika versi Graphify berbeda;
- periksa graphify --help.

Verifikasi:

- route /promotion;
- controller atau Livewire component;
- query filter;
- model membership;
- form submit;
- transaction service;
- test.

Perbarui dokumentasi teknis yang relevan atau buat:

docs/STUDENT-MEMBERSHIP-FILTER.md

# MANUAL ACCEPTANCE CHECKLIST

Jika browser automation tidak tersedia, buat checklist manual berikut.

Admin:

1. Buka Keanggotaan Siswa.
2. Pilih kelas tujuan.
3. Cari nama siswa.
4. Cari NIS.
5. Filter Formal.
6. Filter Muadalah.
7. Filter Reguler.
8. Filter Nonreguler.
9. Filter kelas asal.
10. Filter siswa tanpa kelas.
11. Sembunyikan siswa yang sudah di target.
12. Pilih beberapa siswa.
13. Pindah pagination.
14. Pastikan pilihan tetap tersedia.
15. Reset filter.
16. Preview proses.
17. Tambahkan ke kelas.
18. Pindahkan kelas reguler.
19. Periksa histori keanggotaan.
20. Periksa hasil summary.

Viewport:

- 390×844;
- 768×1024;
- 1440×900.

Theme:

- System;
- Light;
- Dark.

# OUTPUT WAJIB

Berikan laporan dengan format:

1. Baseline.
2. Implementasi aktual sebelum perubahan.
3. Graphify findings.
4. Filter yang dibuat.
5. Query dan performa.
6. UI yang diubah.
7. Selection behavior.
8. Business rule tambah/pindah.
9. Validation dan transaction.
10. File yang diubah.
11. Automated test.
12. Build dan formatting.
13. Remaining gaps.
14. Manual acceptance checklist.
15. Final status.

Status yang boleh digunakan:

- STUDENT-MEMBERSHIP-FILTER-PASSED
- SERVER-SIDE-SEARCH-PASSED
- BULK-SELECTION-PASSED
- MULTI-CLASS-MEMBERSHIP-SAFE
- REGULAR-CLASS-TRANSFER-PASSED
- BROWSER-ACCEPTANCE-PENDING
- READY-FOR-MANUAL-REVIEW
- BLOCKED

Jangan menggunakan klaim “100% selesai”.

Mulai dari audit halaman /promotion dan struktur membership aktual. Setelah audit,
langsung implementasikan filter server-side, pagination, selection workflow, dan
validasi tanpa mengubah modul lain yang tidak terkait.