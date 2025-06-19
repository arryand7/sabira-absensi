<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'guru', 'karyawan', 'organisasi') DEFAULT 'karyawan'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'guru', 'karyawan') DEFAULT 'karyawan'");
    }
};
