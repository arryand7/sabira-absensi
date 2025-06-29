# 🚀 Setup Project Laravel

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
