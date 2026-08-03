# Jadwal Guru dan Soft Conflict Workflow

## Baseline

- Repository: `/Users/ryand/Documents/LARAVEL/sabira/sabira-absensi`
- Branch: `main`
- Baseline commit: `63118ccf670ac597d061629ffd840bbbf13efe11`
- Laravel: `13.22.0`
- PHP: `8.4.1`
- Baseline test: 81 test, 626 assertion lulus
- Baseline frontend build: lulus
- Working tree sudah berisi banyak perubahan lokal dari fase sebelumnya dan dipertahankan.

## Temuan Graphify dan Verifikasi Source

| Temuan | Bukti | Confidence |
| --- | --- | --- |
| Halaman guru melalui `guru.schedule` menuju `TeacherScheduleController@index` dan `guru.schedule.index` | `routes/web.php`, controller, dan Blade aktual | GRAPH-EXTRACTED + DIRECT-SOURCE-VERIFIED |
| Hard conflict tersebar di controller guru, controller admin, dan import Excel | Empat blok overlap pada controller dan satu blok pada import | GRAPH-INFERRED-VERIFIED |
| Dashboard lama menghitung overlap sementara dan tidak memiliki record audit | `AdminDashboardController::countScheduleConflicts()` | DIRECT-SOURCE-VERIFIED |
| `SchedulePolicy` melindungi akses jadwal/sesi tetapi belum memiliki policy conflict | `AuthServiceProvider` dan `SchedulePolicy` | DIRECT-SOURCE-VERIFIED |

## Desain Implementasi

Alur penyimpanan sekarang:

```text
Form admin/guru/import
→ validasi struktur input
→ simpan Schedule
→ ScheduleConflictService::refreshFor
→ deteksi overlap dalam tahun ajaran + semester + hari yang sama
→ simpan ScheduleConflict pending_review
→ flash warning
→ badge sidebar + dashboard + halaman review admin
```

Rumus overlap:

```text
candidate.jam_mulai < schedule.jam_selesai
AND
candidate.jam_selesai > schedule.jam_mulai
```

Conflict type:

- `teacher_overlap`: guru sama;
- `class_overlap`: kelas sama.

Keputusan admin:

- `keep_current`: pertahankan jadwal baru/diubah, soft-delete pembanding;
- `keep_existing`: pertahankan pembanding, soft-delete jadwal baru/diubah;
- `keep_both`: verifikasi keduanya tetap aktif;
- `dismiss`: tandai false positive.

Soft delete dipilih agar sesi pembelajaran dan histori tidak hilang. Relasi sesi ke jadwal menggunakan `withTrashed()`.

## UI Jadwal Guru

- Tetap memakai canonical `<x-app-shell>` dan theme System/Light/Dark.
- Desktop menggunakan matriks Senin–Sabtu sebagai kolom dan jam 1–8 sebagai baris.
- Istirahat 09:55–10:25 ditampilkan sebagai baris konteks.
- Slot kosong menyediakan tombol tambah yang sudah membawa hari dan waktu.
- Jadwal overlap tetap tampil berdampingan dan diberi status `Bentrok`.
- Mobile menggunakan day selector horizontal dan agenda satu kolom.
- Mode daftar tetap tersedia dan menyertakan status konflik.
- Filter tersedia untuk program, kelas, tahun ajaran, dan semester; guru bersifat read-only pada halaman guru.

## Authorization

- Route review conflict berada di middleware `checkRole:admin,super_admin`.
- `ScheduleConflictPolicy` memverifikasi `viewAny`, `view`, dan `resolve`.
- `ResolveScheduleConflictRequest` memanggil policy sebelum validasi keputusan.
- Guru dapat melihat status benturan pada jadwalnya, tetapi tidak dapat membuka atau menyelesaikan workflow admin.
- Payload `user_id` pada form guru dibatasi ke user yang sedang login.

## Automated Verification

- Full test: 90 test, 675 assertion lulus.
- Test conflict khusus: 9 test lulus.
- Frontend production build: lulus.
- Migration lokal: `2026_08_03_000004` dan `2026_08_03_000005` sudah dijalankan.

Warning yang masih ada berasal dari konfigurasi PHPUnit schema lama dan metadata doc-comment test lama; bukan kegagalan fitur ini.

## Manual Browser Acceptance Checklist

Browser automation tidak tersedia pada sesi implementasi ini. Checklist berikut belum ditandai sebagai `BROWSER-VERIFIED`:

- [ ] Login guru dan buka `/jadwal-guru` pada desktop.
- [ ] Verifikasi matriks jam × Senin–Sabtu pada Light dan Dark.
- [ ] Uji toggle Mingguan/Daftar dan persistence setelah reload.
- [ ] Uji viewport 360px: day selector, agenda, filter, dan touch target.
- [ ] Klik slot kosong dan pastikan hari/waktu terisi pada form.
- [ ] Simpan jadwal normal dan pastikan flash sukses biasa.
- [ ] Simpan jadwal overlap dan pastikan tetap tersimpan dengan warning.
- [ ] Pastikan dua jadwal overlap sama-sama terlihat dengan badge Bentrok.
- [ ] Login admin dan pastikan badge Benturan Jadwal muncul.
- [ ] Buka dashboard admin dan drill-down `Tinjau Konflik`.
- [ ] Filter dan buka detail perbandingan conflict.
- [ ] Uji setiap keputusan pada database staging/testing.
- [ ] Pastikan dialog konfirmasi, catatan, back/refresh, mobile, dan console JavaScript aman.

## Status

- `SCHEDULE-UI-REDESIGN-PASSED`
- `SOFT-CONFLICT-WORKFLOW-PASSED`
- `ADMIN-CONFLICT-REVIEW-PASSED`
- `BROWSER-ACCEPTANCE-PENDING`
