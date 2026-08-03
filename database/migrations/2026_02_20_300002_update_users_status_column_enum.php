<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY status ENUM('aktif', 'nonaktif', 'active', 'suspended') NOT NULL DEFAULT 'aktif'");
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_status_check');
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_status_check CHECK (status IN ('aktif', 'nonaktif', 'active', 'suspended'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY status ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif'");
        }
    }
};
