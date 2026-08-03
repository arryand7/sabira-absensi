# Graphify UI Integration Audit

Tanggal: 3 Agustus 2026  
Repository: `/Users/ryand/Documents/LARAVEL/sabira/sabira-absensi`  
Baseline branch/commit: `main` / `63118ccf670ac597d061629ffd840bbbf13efe11`

Dokumen ini membedakan bukti Graphify, source, automated test, build, dan browser. Status `WORKING` tidak didasarkan pada graph saja.

## 1. Baseline

| Area | Baseline | After-state terakhir |
| --- | --- | --- |
| Working tree | 98 tracked file berubah dan banyak untracked; perubahan awal dipertahankan | Tetap dirty; tidak ada reset/push/deploy |
| PHP / Laravel | PHP 8.4.1 / Laravel 13.22.0 | Tidak berubah |
| Git | `main` / `63118ccf...` | Tidak ada commit dibuat |
| Route | 159 | 156 route final; tanpa duplicate name/URI atau handler hilang |
| Migration | Semua baseline `Ran` | Tambahan superadmin, draft sesi, dan koreksi telah `Ran` |
| Test | 61 passed, 196 assertions | 75 passed, 291 assertions |
| Frontend | `npm ci` dan build lulus | Build production lulus; warning caniuse-lite 16 bulan |
| Composer | validate/audit lulus | Tidak ada advisory keamanan |
| Graphify | 1.232 node, 2.194 edge, 247 community | 1.321 node, 2.433 edge, 255 community setelah update final |

Artefak Graphify tersedia: `graphify-out/graph.json`, `graphify-out/graph.html`, dan `graphify-out/GRAPH_REPORT.md`.

## 2. Graphify Findings

| Query | Graph path | Confidence | Source verification | Impact |
| --- | --- | --- | --- | --- |
| Main entry points | `routes/web.php → controller → model/view` | GRAPH-EXTRACTED | Seluruh action route direfleksikan; seluruh handler saat ini tersedia | Menemukan resource route yang membuat action palsu |
| Teaching flow | `TeacherScheduleController → SubmitTeachingSessionService → ScheduleSession/Attendance/TeacherTeachingAttendance` | GRAPH-EXTRACTED | Transaksi dan `updateOrCreate` diverifikasi di `SubmitTeachingSessionService.php:18-138` | Sesi, siswa, dan kehadiran guru tersimpan atomik |
| Service ke teacher attendance | `SubmitTeachingSessionService → execute → TeacherTeachingAttendance` | GRAPH-INFERRED-VERIFIED | `SubmitTeachingSessionService.php:111-128` | Edge inferred terbukti benar melalui source dan test |
| History/correction | `ScheduleSession → corrections → AttendanceCorrectionWorkflowTest` | GRAPH-EXTRACTED post-update | Kepemilikan di `TeacherHistoryController.php:25-38,125-136`; approval transaction di `ReviewAttendanceCorrectionService.php:12-47` | Edit langsung sesi selesai diganti audit workflow |
| Dashboard | `AdminDashboardController → report services/models` | GRAPH-EXTRACTED | Query KPI di `AdminDashboardController.php:47-112` | Angka statis dan query kolom lama dihilangkan; KPI memiliki drill-down |
| Gate preview | `GateUserSyncController → SyncReconciliationService → GateSyncRun/Item` | GRAPH-EXTRACTED | `GateUserSyncController.php:30-75` | Dry-run tidak mengubah domain user |
| Gate apply/report | `ApplyGateSyncService → GateProvisioningClient` | GRAPH-EXTRACTED | Transaction lokal dan report-back luar transaction diverifikasi | Kegagalan report menghasilkan `report_pending`, bukan rollback domain |
| Gate action/retry | `UpdateGateSyncActionsRequest → GateUserSyncController → ApplyGateSyncService::retryReport` | GRAPH-EXTRACTED post-update | Allowed action per kategori dan retry diverifikasi source/test | Conflict tidak auto-merge; local-only tidak dihapus |
| Navigation/orphan | traversal luas dan sering truncated | GRAPH-INFERRED-VERIFIED | Audit literal route Blade, placeholder href, route reflection, duplicate URI/name | Graph dipakai sebagai locator, bukan bukti akhir |

God nodes baseline: `User`, `AcademicYear`, `Controller`, `ClassGroup`, `Schedule`, `Student`, dan `ScheduleSession`. Perubahan pada node ini diperlakukan sebagai perubahan lintas modul.

## 3. Broken Feature Inventory

| Fitur/kondisi awal | Root cause | Perbaikan | Status |
| --- | --- | --- | --- |
| Route promosi duplikat; route add/remove tanpa method | MISSING_ROUTE / MISSING_HANDLER | Satu canonical route dan redirect; route palsu dihapus | WORKING |
| Resource Program/Subject/Divisi/Karyawan membuka action yang tidak ada | MISSING_HANDLER / MISSING_VIEW | Batasi resource atau lengkapi CRUD nyata | WORKING, TEST-VERIFIED untuk karyawan |
| Submit kegiatan asrama menunjuk method yang tidak ada | LEGACY_UI_STILL_ACTIVE | Route palsu dibuang, endpoint AJAX aktual dipertahankan | WORKING |
| Link `#`, link role salah, mobile menu lintas-role | PLACEHOLDER / WRONG_ROLE | Named route, button trigger, visibility role-aware | DIRECT-SOURCE-VERIFIED |
| Guru dapat mengirim identitas guru pengganti dari browser | WRONG_AUTHORIZATION | Assignment admin, policy, identitas dari `auth()` | TEST-VERIFIED |
| Absensi dapat dikirim tanpa seluruh siswa aktif | WRONG_PARAMETER | Validasi membership aktif dan kelengkapan payload | TEST-VERIFIED |
| Dashboard guru memakai kolom tidak ada dan KPI 100% | BACKEND_NOT_WIRED | Query `actual_teacher_id` dan KPI database | TEST-VERIFIED render |
| Histori menggunakan `{schedule}/{pertemuan}` | LEGACY_UI_STILL_ACTIVE | Canonical binding `ScheduleSession`; view edit lama dihapus | TEST-VERIFIED |
| Sesi selesai dapat diedit tanpa audit | WRONG_AUTHORIZATION | AttendanceCorrection request/review + snapshot + transaction | TEST-VERIFIED |
| Gate tersedia untuk admin biasa | WRONG_ROLE | Role `super_admin`, middleware berlapis, UI scoped | TEST-VERIFIED |
| Gate tidak punya pilihan action/retry | BACKEND_NOT_WIRED | Form action per kategori dan retry report-pending | TEST-VERIFIED |
| Dashboard operasional tidak lengkap | BACKEND_NOT_WIRED | KPI jadwal/sesi/konflik/koreksi/risk/geofence + links | DIRECT-SOURCE-VERIFIED |
| Laporan tidak memiliki drill-down siswa/guru | ROUTE_NOT_DISCOVERABLE | Detail progres/timeline dan laporan guru | TEST-VERIFIED |
| `public/hot` menunjuk dev server mati | FRONTEND_ASSET | Marker dihapus dan di-ignore | BUILD-VERIFIED |
| Password superadmin seeder hardcoded dan register publik aktif | WRONG_AUTHORIZATION | Environment bootstrap password; register publik dihapus | TEST pending final suite |
| URI export siswa salah ketik `muid` | WRONG_ROUTE | Canonical `/admin/laporan/murid/{student}/download/*` | DIRECT-SOURCE-VERIFIED |

Root-cause yang ditemukan: `MISSING_ROUTE`, `MISSING_HANDLER`, `MISSING_VIEW`, `BACKEND_NOT_WIRED`, `WRONG_ROLE`, `WRONG_MIDDLEWARE`, `WRONG_PARAMETER`, `FRONTEND_ASSET`, `PLACEHOLDER`, `TEST_ONLY_IMPLEMENTATION`, dan `LEGACY_UI_STILL_ACTIVE`. Tidak ada JavaScript error yang boleh dinyatakan selesai sebelum browser run.

## 4. Route and Menu Matrix

| Role | Menu/fitur | Route/handler | Authorization | Status |
| --- | --- | --- | --- | --- |
| super_admin/admin | Dashboard | `admin.dashboard` → `AdminDashboardController@index` | `checkRole:admin,super_admin` | WORKING |
| super_admin/admin | Jadwal/master akademik | `admin.schedules.*`, program, kelas, subject, tahun | role middleware + validation | WORKING |
| super_admin/admin | Izin dan Koreksi | `admin.attendance-corrections.*` | role middleware + Form Request | WORKING / TEST-VERIFIED |
| super_admin/admin | Laporan siswa/guru/karyawan | `laporan.*` | admin group; export juga berada dalam group | WORKING / TEST-VERIFIED sebagian |
| super_admin | Gate Sync | `admin.sync.*` | outer admin + inner superadmin + request authorize | WORKING / TEST-VERIFIED |
| admin | Gate Sync | menu tidak tampil; direct route 403 | defense in depth | WORKING |
| guru | Dashboard/Jadwal/Mulai Sesi/Draft | `guru.dashboard`, `guru.schedule*` | guru middleware + SchedulePolicy/Form Request | WORKING / TEST-VERIFIED |
| guru | Histori/Koreksi | `guru.history.*` | guru middleware + ownership query | WORKING / TEST-VERIFIED |
| guru/karyawan | Presensi kerja | `absensi.*`, `karyawan.history` | role middleware + current user query | WORKING / TEST-VERIFIED |
| organisasi | Sholat/kegiatan/histori | `asrama.*` | organisasi middleware | WORKING / automated coverage terbatas |

Verifikasi statis terakhir sebelum dokumentasi: seluruh literal `route(...)` di Blade tersedia; seluruh route action memiliki class/method; tidak ditemukan duplicate name/URI; tidak ditemukan `href="#"`, `javascript:void`, atau href kosong.

## 5. Route-to-Feature Matrix

| Fitur | Route | Request/Policy | Service | Model | View | Test | Status |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Sesi mengajar | `guru.schedule.absen/draft/submit` | SubmitAttendanceRequest + SchedulePolicy | GeofenceService + SubmitTeachingSessionService | ScheduleSession, Attendance, TeacherTeachingAttendance | wizard guru | TeachingSession tests | WORKING |
| Guru pengganti | `admin.schedules.assign-substitute` | admin validation + SchedulePolicy saat guru memakai | submit service | ScheduleSession | dialog admin + wizard | spoof/substitute tests | WORKING |
| Koreksi sesi | `guru.history.correction.store`, admin review | Store/Review correction requests | ReviewAttendanceCorrectionService | AttendanceCorrection + snapshot | detail guru/admin | AttendanceCorrectionWorkflowTest | WORKING |
| Progress siswa | report list/detail/export | admin role group | StudentProgressReportService | Attendance, membership | list/detail/PDF/Excel | risk + drill-down tests | WORKING; source export belum seluruhnya satu service |
| Pelaksanaan guru | report list/detail/export | admin role group | TeacherTeachingReportService | ScheduleSession | list/detail/PDF/Excel | report + drill-down tests | WORKING |
| Gate Sync | preview/actions/apply/retry | superadmin middleware/request | Gate client/reconcile/apply | GateSyncRun/Item, User | sync index/show | GateSyncFeatureTest | WORKING |

## 6. Authorization Matrix Aktual

| Fitur | Superadmin | Admin | Guru | Karyawan | Organisasi |
| --- | ---: | ---: | ---: | ---: | ---: |
| Dashboard admin/master/laporan/export | Ya | Ya | Tidak | Tidak | Tidak |
| Gate Sync | Ya | Tidak | Tidak | Tidak | Tidak |
| Assignment pengganti | Ya | Ya | Tidak | Tidak | Tidak |
| Sesi jadwal sendiri/assignment resmi | Tidak via UI guru | Tidak via UI guru | Ya | Tidak | Tidak |
| Pengajuan koreksi sesi sendiri | Tidak | Tidak | Ya | Tidak | Tidak |
| Review koreksi | Ya | Ya | Tidak | Tidak | Tidak |
| Presensi kerja pribadi | Tidak | Tidak | Ya | Ya | Tidak |
| Asrama | Tidak | Tidak | Tidak | Tidak | Ya |
| Profil sendiri | Ya | Ya | Ya | Ya | Ya |

Role aktual hanya `super_admin`, `admin`, `guru`, `karyawan`, dan `organisasi`. Role `siswa`, `wali_kelas`, `kurikulum`, `kesiswaan`, `kepegawaian`, dan `management` belum ada dalam schema; tidak diklaim tersedia. Fungsi management saat ini dijalankan role admin/superadmin.

## 7. Implementation

Kelompok file yang berubah dan alasannya:

- `routes/web.php`, `routes/auth.php`: canonical route, penghapusan route palsu/register, koreksi, drill-down, Gate action/retry.
- Request/policy/service sesi: kepemilikan, membership aktif, geofence, autosave draft, atomic submit, duplicate protection.
- `AttendanceCorrection*`, migration, controller, service, dan view: workflow audit koreksi.
- Dashboard admin/guru dan report services: data database dan drill-down.
- Controller/view karyawan: CRUD yang sesuai schema aktual.
- Gate controller/service/view/test: superadmin-only, action selection, apply, report pending, retry.
- Sidebar/shell/profile: route nyata, active/open state, role visibility, mobile navigation.
- Seeder/env/docs: bootstrap superadmin aman dan dokumentasi deployment.

## 8. Browser Verification

`BROWSER-VERIFIED` belum diberikan. Skill browser telah dibaca, tetapi alat kontrol browser yang diwajibkan skill tidak tersedia pada sesi audit. Tidak dilakukan klaim visual berdasarkan feature test.

Checklist yang masih harus dijalankan ketika browser runtime tersedia:

- Guru desktop/mobile: login → dashboard → jadwal → mulai sesi → autosave/reload → seluruh siswa → lokasi → submit → detail → request correction.
- Admin desktop/mobile: sidebar/dropdown → dashboard KPI → drill-down → siswa/guru report → filter → PDF/Excel → koreksi approve/reject.
- Superadmin: Gate status → dry-run → delapan kategori → ubah action → apply → simulasi report pending → retry.
- Karyawan: check-in → status → checkout → histori.
- Periksa console, failed network, modal, back/refresh, empty/error state, unauthorized URL, dan double click.

## 9. Automated Tests

Quality gate final:

```text
Tests: 75
Assertions: 291
Passed: 75
Failed: 0
Warnings: PHPUnit XML/doc-comment deprecation
Duration: 4.26s
```

Build Vite dan Pint lulus setelah perapian final. Composer audit: tidak ada advisory.

## 10. Graphify After-State

- Graph diperbarui: ya, setelah koreksi/dashboard/Gate dan sekali lagi setelah report/auth/docs final (`graphify update .`).
- Before: 1.232 node / 2.194 edge / 247 community.
- After final: 1.321 node / 2.433 edge / 255 community.
- Changed paths: teaching draft/submit/correction, dashboard-report, Gate actions/apply/retry.
- Query post-change menemukan node `AttendanceCorrectionWorkflowTest`, `UpdateGateSyncActionsRequest`, dan `retryReport`.
- Query orphan masih menghasilkan traversal luas/truncated dan mencampur docs/test; hasilnya tidak dipakai tanpa source verification.
- Query final kembali menemukan jalur teaching test/session, dashboard ke report services/models, dan Gate controller `retryReport`.

## 11. Remaining Gaps

1. Browser acceptance desktop/mobile belum dapat dijalankan pada sesi ini.
2. Role organisasi mempunyai automated coverage lebih sedikit dibanding admin/guru/karyawan.
3. Role management/kurikulum/kesiswaan/kepegawaian/wali kelas/siswa belum dimodelkan; admin/superadmin saat ini menjalankan fungsi management.
4. Ambang risiko belum configurable dari UI; rule masih berada dalam StudentProgressReportService.
5. HTML/PDF/Excel laporan siswa masih memiliki beberapa builder terpisah. Signature file dan authorization sudah diuji, tetapi parity seluruh nilai sel belum dibandingkan satu per satu.
6. PHPUnit XML dan annotation lama menimbulkan deprecation warning; Browserslist database usang.
7. Browser permission lokasi dan download file belum BROWSER-VERIFIED.

## 12. Final Status

Status yang sah saat dokumen ini ditulis:

- `AUDIT-COMPLETE`
- `CORE-NAVIGATION-PASSED` (source + automated route checks)
- `CORE-FLOWS-PASSED` (automated tests)
- `GRAPH-UPDATED-AND-VERIFIED`

Belum sah: `BROWSER-ACCEPTANCE-PASSED` dan `READY-FOR-STAGING-REVIEW`.
