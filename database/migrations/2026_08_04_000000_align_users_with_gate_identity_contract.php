<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const ROLES = "'super_admin','admin','guru','karyawan','organisasi','siswa','wali'";

    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE users MODIFY role ENUM('.self::ROLES.") NOT NULL DEFAULT 'karyawan'");
            DB::statement('ALTER TABLE users MODIFY email VARCHAR(255) NULL');

            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('users')->whereIn('role', ['siswa', 'wali'])->update(['role' => 'karyawan']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('super_admin','admin','guru','karyawan','organisasi') NOT NULL DEFAULT 'karyawan'");
        }
    }
};
