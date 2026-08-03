# Dokumentasi Upgrade Laravel 13 & Deployment/Rollback Plan — Sabira Absensi

Dokumen ini mencakup prasyarat environment, prosedur deployment staging/production, serta rencana rollback komprehensif untuk aplikasi **Sabira Absensi** yang telah di-upgrade secara bertahap dari **Laravel 10 → Laravel 11 → Laravel 12 → Laravel 13.x**.

---

## 1. Prasyarat Server & Environment

| Component | Minimum Required | Current Verified Target |
| :--- | :--- | :--- |
| **PHP Runtime** | `^8.2` (Laravel 11/12/13) | **PHP 8.4.1** (CLI & FPM) |
| **PHP Extensions** | `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `gd` / `imagick`, `hash`, `intl`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `session`, `tokenizer`, `xml`, `zip` | All Enabled |
| **Composer** | `2.x` | **Composer 2.8.12** |
| **Node.js / npm** | `^18.x` / `^9.x` | **Node v24.4.1**, **npm 11.4.2** |
| **Database Server** | MySQL 8.0+ / MariaDB 10.3+ / SQLite 3.35+ | MySQL 8.0 / SQLite (testing) |
| **Web Server** | Nginx 1.20+ / Apache 2.4+ | Nginx (fastcgi_pass PHP-FPM 8.4) |
| **Cache & Queue Driver** | Database / Redis | Redis 7.0+ or DB Queue |

---

## 2. Pre-Deployment Protocol

Sebelum menjalankan deployment di staging atau production:
1. **Backup Full Database**: Ambil dump MySQL/MariaDB terkini (`mysqldump -u <user> -p <db_name> > backup_pre_l13.sql`).
2. **Backup Storage & Assets**: Salin seluruh direktori `storage/app/public` (foto user, foto Gate, logo).
3. **Catat State Commit & Lock**:
   - Commit baseline: `git rev-parse HEAD`
   - Salinan `composer.lock` & `package-lock.json` versi sebelumnya.
4. **Verifikasi Jalur Rollback**: Pastikan file backup database dapat di-restore dengan sukses di server uji.
5. **Jadwalkan Maintenance Window**: Aktifkan halaman maintenance selama proses cutover.

---

## 3. Staging & Production Deployment Steps

Jalankan urutan command deployment terkontrol berikut (IDEMPOTENT & NON-DESTRUCTIVE):

```bash
# 1. Aktifkan Maintenance Mode
php artisan down --message="Pemeliharaan sistem & upgrade platform Sabira Absensi sedang berlangsung."

# 2. Pull Kode Terbaru (Tag / Release Branch)
git fetch origin
git checkout main # Atau release tag upgrade/laravel-13

# 3. Optimize & Install Composer Dependencies (Production Mode)
composer install --no-dev --optimize-autoloader

# 4. Install & Build Frontend Assets
npm ci
npm run build

# 5. Jalankan Migration (Reversible & Non-Destructive)
php artisan migrate --force

# 6. Bersihkan & Regenerate Optimization Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 7. Restart Background Worker & Queue Listener
php artisan queue:restart

# 8. Reload PHP-FPM Service (Misal: PHP 8.4-FPM)
sudo systemctl reload php8.4-fpm

# 9. Nonaktifkan Maintenance Mode
php artisan up
```

---

## 4. Rollback Plan (Jika Terjadi Kendala Stabilitas)

Bila timbul kendala kritis di production pasca-cutover:

### Langkah A — Source Code & Dependency Rollback
```bash
# 1. Aktifkan Maintenance Mode
php artisan down --message="Rollback versi sedang dilakukan."

# 2. Revert Git Commit ke Baseline Sebelum Upgrade
git checkout <pre_upgrade_commit_hash>

# 3. Restore Composer Dependencies dari Lockfile Baseline
composer install --no-dev --optimize-autoloader

# 4. Rebuild Frontend Asset Baseline
npm ci
npm run build

# 5. Clear Caches
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 6. Restart Queue Workers & Reload PHP-FPM
php artisan queue:restart
sudo systemctl reload php8.4-fpm

# 7. Nonaktifkan Maintenance Mode
php artisan up
```

### Langkah B — Database Rollback (Bila Diperlukan)
> [!CAUTION]
> Jangan melakukan `migrate:rollback` atau `restore DB` tanpa mengaudit apakah ada data transaksi absensi baru yang masuk selama window deployment. Bila migrasi yang ditambahkan diupgrade bersifat non-destructive (hanya penambahan kolom nullable / index), rollback skema database **TIDAK PERLU** dilakukan.

---

## 5. Ringkasan Verifikasi Pasca-Upgrade

Seluruh 58 automated tests telah diverifikasi lulus pada Laravel 13:
- Authentication & Gate SSO Callback
- Gate Provisioning 8-Category Reconciliation Engine & 2-Step Sync
- Teaching Session Submission & Geofence Validation
- Student Progress Risk Detection & Teacher Teaching Anomaly Reports
- Executive Management Dashboard & Export Features
