# UI Single Layout Audit

Tanggal audit: 3 Agustus 2026  
Repository: `/Users/ryand/Documents/LARAVEL/sabira/sabira-absensi`  
Branch/commit baseline: `main` / `63118ccf670ac597d061629ffd840bbbf13efe11`

## 1. Baseline

| Area | Hasil |
| --- | --- |
| Laravel | 13.22.0 |
| PHP | 8.4.1 |
| Route non-vendor | 156 |
| Migration | Seluruh migration sudah `Ran`; tidak ada pending |
| Test | 76 passed, 300 assertions |
| Frontend | `npm ci` dan `npm run build` lulus |
| Composer | Valid; tidak ada security advisory |
| Graphify | 0.9.29; graph akhir diperbarui menjadi 1.323 node, 2.428 edge, 259 community |
| Working tree | Sangat kotor; seluruh perubahan lokal dipertahankan dan tidak boleh di-reset |

Warning baseline:

- Skema konfigurasi PHPUnit deprecated dan metadata docblock akan deprecated pada PHPUnit 12.
- Data Browserslist sudah lama.
- Paket frontend lama membawa advisory npm dan harus ditinjau tanpa menjalankan pembaruan breaking secara otomatis.

## 2. Graphify Findings

| Query | Graph path / node | Confidence | Verifikasi source | Dampak |
| --- | --- | --- | --- | --- |
| Lokasi shell dan navigation | `layouts/app.blade.php`, `layouts.user-navigation`, `navigation.blade.php`, `layouts.sabira` | GRAPH-EXTRACTED | `layouts.app` meng-include `layouts.sabira`; `UserLayout` merender `layouts.user` | Terdapat dua shell aplikasi aktif |
| Route AdminLTE vs Tailwind | `layouts.sabira`, `bootstrap.js`, `tailwind.config.js`, `UserLayout` | GRAPH-INFERRED-VERIFIED | `layouts.user` memuat AdminLTE, Bootstrap, jQuery, DataTables; `layouts.sabira` memuat Vite/Alpine/Tailwind | Asset dan behavior berbeda antarrole |
| Duplikasi sidebar/topbar/theme | `navigation.blade.php`, `control-sidebar.blade.php`, `layouts.user-navigation` | GRAPH-EXTRACTED | Ditemukan `admin-sidenav`, `user-sidenav`, dua navbar, control-sidebar AdminLTE, dan sidebar hardcoded di `layouts.sabira` | Active state, branding, dan theme mudah menyimpang |
| View di luar shell dashboard guru | `guru/dashboard.blade.php` dibanding `UserLayout` views | GRAPH-INFERRED-VERIFIED | 13 view guru/karyawan/organisasi memakai `<x-user-layout>` | Halaman dalam role yang sama tampak seperti aplikasi berbeda |

Graphify hanya menjadi locator. Status aktif dan hubungan route/view diverifikasi langsung pada controller, Livewire component, route list, dan Blade.

## 3. Layout Inventory (Before)

| Layout | Asset | Pengguna aktif | Status |
| --- | --- | --- | --- |
| `layouts.sabira` | Vite, Tailwind, Alpine; Font Awesome CDN | Dashboard guru, wizard sesi, Gate Sync, dan seluruh view `<x-app-layout>` melalui include | ACTIVE, kandidat canonical tetapi navigation hardcoded dan theme belum lengkap |
| `layouts.app` | Meneruskan ke `layouts.sabira` | Mayoritas halaman admin, profile, form jadwal guru | ACTIVE alias |
| `components/layouts/sabira` | Meneruskan ke `layouts.sabira` | View `<x-layouts.sabira>` | ACTIVE alias |
| `layouts.user` | AdminLTE, Bootstrap 4, jQuery, DataTables, Vite | Jadwal/riwayat guru, dashboard/absensi karyawan, seluruh asrama | ACTIVE legacy |
| `layouts.guest` | AdminLTE CDN, Bootstrap Icons CDN, Vite | Login, reset password, konfirmasi, verifikasi | ACTIVE legacy guest |
| `layouts.navigation` | Navbar AdminLTE | `layouts.app` lama; kini tidak dirender oleh alias baru | LEGACY_ONLY |
| `layouts.user-navigation` | Navbar AdminLTE | `layouts.user` | ACTIVE duplicate |
| `admin-sidenav` | Sidebar AdminLTE admin | Named slot pada banyak view admin, tetapi slot diabaikan oleh alias `layouts.app` saat ini | DUPLICATE / tidak efektif |
| `user-sidenav` | Sidebar AdminLTE role | `layouts.user` | ACTIVE duplicate |
| `control-sidebar` | AdminLTE customizer | `layouts.user` | ACTIVE legacy theme control |

## 4. Route-to-Layout Matrix (Before)

| Kelompok route/view | Shell sebelum migrasi | Asset sebelum migrasi | Risiko |
| --- | --- | --- | --- |
| Admin, laporan, master data, pengaturan, profile | `x-app-layout → layouts.sabira` | Vite + CDN Font Awesome | Named sidebar lama tersebar; styling halaman belum bertoken |
| Dashboard guru, wizard sesi, Gate Sync | `x-layouts.sabira → layouts.sabira` | Vite + CDN Font Awesome | Menggunakan shell modern tetapi branding/navigation hardcoded |
| Jadwal dan riwayat guru | `x-user-layout → layouts.user` | AdminLTE + Bootstrap + jQuery + DataTables + Vite | Konflik CSS/JS dan UI tidak konsisten |
| Dashboard, absensi, histori karyawan | `x-user-layout → layouts.user` | AdminLTE + Bootstrap + jQuery + DataTables + Vite | Konflik CSS/JS dan UI tidak konsisten |
| Organisasi/asrama | `x-user-layout → layouts.user` | AdminLTE + Bootstrap + jQuery + DataTables + Vite | Mobile dan navigation bergantung AdminLTE |
| Authentication | `x-guest-layout → layouts.guest` | AdminLTE CDN + Vite | Branding/theme berbeda dari aplikasi |
| PDF/Excel Blade | Tanpa app shell (dokumen ekspor) | CSS dokumen/renderer | Dikecualikan dari shell interaktif; harus tetap memakai sumber data yang sama |
| Livewire partial | Mengikuti parent route | Vite/Livewire parent | Tidak boleh diberi shell kedua |

## 5. Active Legacy Views (Before)

Direct-source-verified `<x-user-layout>`:

- `guru/schedule/index.blade.php`
- `guru/history/index.blade.php`
- `guru/history/detail.blade.php`
- `karyawan/dashboard.blade.php`
- `karyawan/absen.blade.php`
- `karyawan/history.blade.php`
- `organisasi/index.blade.php`
- `organisasi/sholat/pilih.blade.php`
- `organisasi/sholat/form.blade.php`
- `organisasi/sholat/history.blade.php`
- `organisasi/kegiatan/index.blade.php`
- `organisasi/kegiatan/absen.blade.php`
- `organisasi/kegiatan/history.blade.php`

## 6. Duplicated Branding and Controls

- `layouts.sabira`: “SABIRA ABSENSI / Monitoring Presensi Terpadu”.
- `layouts.user`, `admin-sidenav`, `user-sidenav`: nama/logo dari `AppSettingManager`, namun struktur AdminLTE terpisah.
- `layouts.guest`: nama/deskripsi dinamis dengan card AdminLTE.
- `control-sidebar`: menyimpan pengaturan `adminlte.customizer`, terpisah dari dark mode Tailwind.
- Sidebar desktop dan mobile bottom navigation ditulis terpisah sehingga daftar item dan active matcher dapat berbeda.

Brand canonical yang ditetapkan:

> SABIRA ABSENSI  
> Monitoring Kehadiran dan Pembelajaran

## 7. Migration Risk

1. Utility Bootstrap (`btn`, `card`, `table`, `form-control`, `row`, `col-*`) masih ada pada sebagian view legacy; shell dapat dimigrasikan lebih dahulu, lalu halaman dinormalisasi dengan komponen/token.
2. Bootstrap Icons dan Font Awesome dipakai luas. Keduanya dipindahkan ke npm/Vite sebelum CDN dilepas.
3. DataTables lama bergantung jQuery. Tabel aktif perlu fallback native/responsive sebelum bundle tersebut dilepas.
4. Named slot `<x-slot name="sidebar">` tersebar di halaman admin. Slot ini sudah tidak efektif dan harus dihapus setelah navigation canonical tersedia.
5. Flowbite dipakai pada modal user dan tetap diisolasi melalui Vite sampai modal canonical menggantikannya.

## 8. Migration Order

1. Design tokens, theme initializer, theme manager, dan asset ikon melalui Vite.
2. `x-app-shell`, `x-sidebar`, `x-mobile-drawer`, `x-topbar`, `x-footer`, dan satu sumber `config/navigation.php`.
3. Aliaskan seluruh component layout lama ke canonical shell sebagai safety net.
4. Migrasikan view guru dan redesign jadwal mingguan/daftar.
5. Migrasikan karyawan dan organisasi.
6. Hapus named sidebar slots dan migrasikan seluruh view admin/profile.
7. Migrasikan guest/auth ke varian guest dari canonical shell.
8. Audit route, Blade, legacy asset, theme, responsive, accessibility, test, build, dan browser.

## 9. After State

### Canonical shell dan aset

| Area | Implementasi akhir | Verifikasi |
| --- | --- | --- |
| App shell | `resources/views/components/app-shell.blade.php` | DIRECT-SOURCE-VERIFIED, TEST-VERIFIED |
| Desktop sidebar | `resources/views/components/sidebar.blade.php` | DIRECT-SOURCE-VERIFIED |
| Mobile drawer | `mobile-drawer.blade.php` merender component sidebar yang sama | DIRECT-SOURCE-VERIFIED, TEST-VERIFIED |
| Topbar/footer | `topbar.blade.php` dan `footer.blade.php` | DIRECT-SOURCE-VERIFIED |
| Navigasi | `config/navigation.php`; route dicek dengan `Route::has`, role difilter, active matcher membuka parent group | DIRECT-SOURCE-VERIFIED, TEST-VERIFIED |
| Theme | script sebelum Vite CSS; `sabira-theme`; System/Light/Dark; listener perubahan OS | DIRECT-SOURCE-VERIFIED, TEST-VERIFIED |
| Asset | Vite 8 + Laravel Vite Plugin 3, Tailwind, Alpine focus/collapse, Livewire, ikon npm, Flowbite terisolasi | BUILD-VERIFIED |

Seluruh template halaman interaktif di `admin`, `auth`, `guru`, `karyawan`, `organisasi`, dan `profile` menggunakan `<x-app-shell>`. Pengecualian yang sah adalah template PDF/Excel, Livewire partial, component, dan partial form yang mengikuti parent shell.

### Route-to-layout matrix (After)

| Kelompok route | Canonical shell | Navigation | Theme | Status |
| --- | --- | --- | --- | --- |
| Admin/superadmin | `x-app-shell` | `config/navigation.php` | System/Light/Dark | TEST-VERIFIED |
| Guru | `x-app-shell` | sumber yang sama | System/Light/Dark | TEST-VERIFIED |
| Karyawan | `x-app-shell` | sumber yang sama | System/Light/Dark | TEST-VERIFIED |
| Organisasi/asrama | `x-app-shell` | sumber yang sama | System/Light/Dark | TEST-VERIFIED |
| Authentication | varian `guest` dari `x-app-shell` | tanpa sidebar | System/Light/Dark | TEST-VERIFIED |
| Export PDF/Excel | layout dokumen, bukan halaman browser | tidak berlaku | tidak berlaku | TEST-VERIFIED |

Route `admin.absensi.edit` sebelumnya mengarah ke view yang tidak tersedia. View `admin/absensi/edit.blade.php` telah ditambahkan dan diuji dari route sampai render form database-backed.

### Legacy removal

Artefak berikut tidak lagi tersedia atau tidak lagi dirujuk:

- `layouts.app`, `layouts.user`, `layouts.guest`, dan `layouts.sabira`;
- class component `AppLayout`, `UserLayout`, dan `GuestLayout`;
- `admin-sidenav`, `user-sidenav`, kedua navigation bar lama, dan AdminLTE control sidebar;
- pemanggilan jQuery, DataTables, SweetAlert global, CDN AdminLTE, dan placeholder `href="#"` pada source aktif.

Interaksi pilihan massal, auto-filter, promotion, serta konfirmasi hapus yang sebelumnya bergantung jQuery telah diganti dengan DOM API native. Tabel tetap menggunakan HTML semantik dan container responsif; halaman jadwal guru memiliki weekly grid desktop serta agenda per hari pada mobile.

### Design system dan reusable components

Token warna, spacing, radius, typography, shadow, form, table, card, dark compatibility, dan responsive shell berada di `resources/css/app.css`. Komponen reusable akhir meliputi app shell, sidebar, mobile drawer, topbar, footer, breadcrumb, page header/actions, card/stat card, status badge, button/icon button, filter bar, data table, pagination, empty state, alert, modal/confirm dialog, form field/input/select/textarea/date range, avatar, stepper, progress bar, timeline, dan theme switcher.

### Verification akhir

| Pemeriksaan | Hasil |
| --- | --- |
| Route | 156 non-vendor route |
| Migration | seluruhnya `Ran`, tidak ada pending |
| Test | 81 passed, 626 assertions |
| Frontend build | Vite 8.2.0 lulus |
| npm audit | 0 vulnerability |
| Composer audit | 0 advisory |
| Pint | lulus setelah formatting test canonical shell |
| PHPStan | tidak tersedia (`vendor/bin/phpstan` tidak terpasang) |
| Graphify | 1.323 node, 2.428 edge, 259 community; source query tidak menemukan dependency source aktif ke AdminLTE/jQuery/DataTables |

### Browser acceptance dan gap

Kontrol in-app browser, Playwright, dan Laravel Dusk tidak tersedia pada sesi eksekusi ini. Karena itu viewport 360–1440, console JavaScript nyata, perubahan system theme saat browser hidup, geolocation permission, modal Flowbite, dan screenshot before/after belum berstatus `BROWSER-VERIFIED`. Coverage browser digantikan sementara oleh feature test lintas-role, render route nyata, static source regression, build Vite, serta test alur sesi/absensi/export/Gate yang sudah ada.

Graphify masih dapat menampilkan istilah legacy dari bagian historis dokumen audit atau rollback documentation. Itu bukan dependency source aktif; hasil tersebut diverifikasi dengan `rg` langsung. Flowbite masih berada dalam pipeline Vite khusus modal user, tetapi bukan layout atau asset pipeline kedua.
