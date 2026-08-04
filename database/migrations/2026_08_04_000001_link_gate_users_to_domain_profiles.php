<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->unique()->after('id')->constrained()->nullOnDelete();
        });

        Schema::table('karyawan', function (Blueprint $table) {
            $table->string('nip')->nullable()->unique()->after('user_id');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE students MODIFY jenis_kelamin ENUM('L','P') NULL");
        } else {
            Schema::table('students', function (Blueprint $table) {
                $table->string('jenis_kelamin')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });

        Schema::table('karyawan', function (Blueprint $table) {
            $table->dropUnique(['nip']);
            $table->dropColumn('nip');
        });
    }
};
