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
        Schema::table('absensi_karyawan', function (Blueprint $table) {
            $table->time('check_in')->nullable()->after('waktu_absen');
            $table->time('check_out')->nullable()->after('check_in');
            $table->string('status')->nullable()->after('check_out');
        });
    }

    public function down(): void
    {
        Schema::table('absensi_karyawan', function (Blueprint $table) {
            $table->dropColumn(['check_in', 'check_out', 'status']);
        });
    }

};
