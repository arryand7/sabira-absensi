Berikut PRD penambahan/perbaikan fitur **Sabira Absensi** yang bisa langsung kamu jadikan backlog implementasi (Laravel 10 + Sanctum + Livewire 3). Aku buat modular supaya kamu bisa ambil per bagian tanpa “merombak total”.

---

# PRD – Peningkatan Sabira Absensi (vNext)

## 0) Tujuan & Outcome

**Tujuan utama**

1. Mengurangi kecurangan & error absensi (guru/karyawan).
2. Membuat absensi siswa **lebih konsisten** (anti dobel, kuat terhadap perubahan jadwal).
3. Mempercepat laporan PDF/Excel dan membuatnya lebih akurat.
4. Memudahkan admin melakukan koreksi dengan jejak audit.

**KPI (sederhana, bisa diukur)**

* Error “di luar radius” turun (dengan alasan tercatat).
* Double input absensi siswa turun → 0 (via constraint session).
* Waktu generate laporan bulanan turun signifikan (pakai summary).
* Semua koreksi absensi punya audit trail + alasan.

---

## 1) Fitur A — “Sesi Pertemuan” untuk Absensi Siswa (Wajib)

### Problem

Absensi siswa per jadwal rawan:

* dobel input,
* kacau saat ada tukar jam/perubahan jadwal,
* sulit rekap per pertemuan.

### Solusi

Tambahkan entity **Schedule Session (Pertemuan)** sebagai “container” absensi.

### Scope

**In-scope**

* Buat sesi pertemuan dari jadwal untuk tanggal tertentu.
* Absensi siswa selalu menempel ke sesi (bukan hanya schedule).
* Materi/pertemuan tersimpan di sesi.

**Out-of-scope (v1)**

* Otomatis generate sesi untuk 1 semester penuh (boleh nanti).

### User Stories

* Guru: “Saya klik jadwal hari ini → buat/lihat sesi pertemuan → isi hadir/izin/sakit/alpa + catat materi.”
* Admin: “Saya bisa lihat sesi & rekap per sesi jika ada komplain.”
* Sistem: “Tidak boleh ada dua sesi identik untuk jadwal & waktu yang sama.”

### Data Model

Tambahan tabel:

* `schedule_sessions`

  * id, schedule_id, academic_year_id
  * date, start_at, end_at
  * meeting_no (nullable)
  * topic/materi (text)
  * created_by (user_id)
  * status (open/locked)
  * timestamps
* Update `student_attendance`

  * tambah `schedule_session_id`
  * unique(schedule_session_id, student_id)

**Constraint**

* unique(schedule_id, date, start_at) atau unique(schedule_id, date) sesuai aturan sekolah.

### UI/Flow

* Guru > Jadwal Hari Ini > klik jadwal → halaman “Pertemuan”

  * Jika belum ada session: tombol “Mulai Pertemuan”
  * Isi absensi + materi
  * Tombol “Kunci Pertemuan” (lock) setelah selesai (opsional)

### Acceptance Criteria

* Tidak bisa submit absensi siswa tanpa session.
* Absensi tidak bisa dobel untuk siswa yang sama di session yang sama.
* Lock mencegah edit kecuali admin.

### Estimasi Implementasi

* Migrasi + relasi model + UI guru + validasi.

---

## 2) Fitur B — Koreksi Absensi + Audit Trail (Wajib)

### Problem

Koreksi manual sering terjadi (GPS error, sakit/izin telat input), tapi rawan tanpa jejak.

### Solusi

Buat modul koreksi terpisah dengan audit trail.

### Scope

* Request koreksi oleh guru/karyawan (opsional) dan approval admin.
* Koreksi oleh admin langsung tetap tercatat.

### User Stories

* Karyawan: “Saya ajukan koreksi check-in karena sinyal buruk, upload bukti.”
* Admin: “Saya approve/reject, sistem simpan siapa mengubah apa dan alasannya.”

### Data Model

* `attendance_corrections`

  * id, user_id, date
  * target_type (karyawan_attendance / student_attendance / asrama_attendance)
  * target_id
  * before_json, after_json
  * reason (text)
  * attachment_path (nullable)
  * status (pending/approved/rejected)
  * decided_by, decided_at
  * timestamps

### UI/Flow

* Karyawan/Guru: menu “Ajukan Koreksi”
* Admin: list koreksi pending + detail sebelum/sesudah + approve/reject

### Acceptance Criteria

* Setiap perubahan absensi melalui koreksi menghasilkan record audit.
* Admin dapat filter koreksi per user/divisi/periode.

---

## 3) Fitur C — Validasi Absensi Karyawan/Guru yang Lebih Kuat (GPS + Quality) (Wajib)

### Problem

Geofence saja sering false negative (indoor), dan device hash harian kurang kuat.

### Solusi

Perbaiki validasi dengan **GPS accuracy logging** + opsi metode tambahan.

### Scope

**In-scope**

* Simpan lat/lng + accuracy (meter) + reason gagal.
* Aturan: jika accuracy > threshold (mis. 80m), minta ulang.
* Device fingerprint lebih stabil (kombinasi beberapa sinyal).

**Optional**

* QR dinamis untuk mode indoor (fase berikutnya).

### Data Model (update `absensi_karyawans`)

* check_in_lat/lng/accuracy, check_out_lat/lng/accuracy
* validation_method (gps / gps+qr / manual_admin)
* fail_reason (nullable)
* device_fingerprint (string)
* source (web/mobile)

### UI/Flow

* Saat absen:

  * tampilkan “Akurasi lokasi: X meter”
  * jika buruk → tombol “Coba lagi”
  * jika out of radius → tampilkan jarak & lokasi terdekat

### Acceptance Criteria

* Absensi menyimpan accuracy.
* Jika permission lokasi ditolak → user diberi instruksi + tercatat fail_reason.

---

## 4) Fitur D — Kebijakan Absensi (Policy Engine Ringan) (High Value)

### Problem

Aturan telat/pulang cepat beda divisi/jenis guru. Kalau hardcode, cepat kacau.

### Solusi

Satu tabel kebijakan yang dipakai modul karyawan/guru (dan nanti bisa siswa/asrama).

### Data Model

* `attendance_policies`

  * id, scope_type (divisi/jenis_guru/global)
  * scope_id (nullable)
  * check_in_start, check_in_end
  * grace_late_minutes
  * check_out_start, check_out_end
  * grace_early_minutes
  * allow_outside_geofence (bool)
  * effective_from
  * timestamps

### Acceptance Criteria

* Sistem menentukan status (hadir/telat/pulang_cepat) berdasarkan policy aktif.

---

## 5) Fitur E — Rekap Cepat dengan Summary Table (Performa Laporan) (Wajib untuk skala besar)

### Problem

Laporan PDF/Excel berat bila query raw join banyak tabel.

### Solusi

Generate tabel ringkasan harian/bulanan via cron.

### Data Model

* `attendance_daily_summaries`

  * user_id, date, status, minutes_late, minutes_early
  * check_in_at, check_out_at
  * location_id, validation_method
  * indexes (user_id+date), (date), (status)

* `student_attendance_summaries` (opsional v1)

  * class_group_id, date, subject_id, hadir/izin/sakit/alpa

### Job/Cron

* `php artisan attendance:build-summaries --date=...`
* Jalan tiap malam untuk hari berjalan (dan bisa rebuild range).

### Acceptance Criteria

* Export bulanan memakai summary table.
* Waktu generate turun (dibanding sebelumnya).

---

## 6) Fitur F — Import Jadwal & Deteksi Bentrok yang Lebih Tegas (Nice-to-have)

### Scope

* Template Excel import jadwal (class, subject, teacher, day, start, end).
* Deteksi bentrok:

  * bentrok guru (dua kelas di jam sama)
  * bentrok kelas (dua mapel di jam sama)

### Acceptance Criteria

* Import menolak baris yang bentrok dan memberi laporan error per baris.

---

## 7) Fitur G — Notifikasi & Reminder (Nice-to-have)

### Scope

* Reminder check-in (mis. 10 menit sebelum batas).
* Reminder guru untuk input absensi sesi yang belum diisi.

Implementasi ringan:

* Laravel scheduler + queue (atau tanpa queue dulu, tergantung infra).

---

# 8) Rencana Rilis (disusun agar gampang dieksekusi)

## Release 1 (Paling wajib, stabilitas)

* A: Schedule Sessions + relasi student_attendance
* B: Koreksi + audit trail
* C: GPS accuracy logging + fail_reason
* E (minimal): attendance_daily_summaries untuk karyawan/guru

## Release 2 (Kualitas aturan & performa)

* D: Attendance policies
* E (lanjutan): student summaries + rebuild tools
* F: Import jadwal + bentrok

## Release 3 (User experience)

* G: Notifikasi + reminder
* QR dinamis (jika dibutuhkan)

---

# 9) Catatan Implementasi Laravel (biar kamu cepat mulai)

* Mulai dari migrasi: `schedule_sessions`, `attendance_corrections`, `attendance_policies`, `attendance_daily_summaries`.
* Tambahkan constraint unik & index dari awal (ini yang paling sering dilupakan).
* Livewire:

  * Komponen “Start Session / Lock Session”
  * Komponen “Correction Approval”
  * Komponen “Daily Summary Report”

---

Kalau kamu mau, aku bisa lanjutkan jadi **backlog teknis siap coding** per fitur:

* daftar endpoint/route,
* struktur controller/service,
* contoh migration + indexes yang tepat,
* dan skema perubahan UI Livewire per role.