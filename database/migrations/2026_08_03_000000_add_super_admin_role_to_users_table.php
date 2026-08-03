<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'admin', 'guru', 'karyawan', 'organisasi'])
                ->default('karyawan')
                ->change();
        });
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'super_admin')->update(['role' => 'admin']);

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'guru', 'karyawan', 'organisasi'])
                ->default('karyawan')
                ->change();
        });
    }
};
