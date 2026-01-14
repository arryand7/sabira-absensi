<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE class_groups MODIFY jenis_kelas ENUM('formal','muadalah','tambahan') NOT NULL");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE class_groups DROP CONSTRAINT IF EXISTS class_groups_jenis_kelas_check");
            DB::statement("ALTER TABLE class_groups ADD CONSTRAINT class_groups_jenis_kelas_check CHECK (jenis_kelas IN ('formal','muadalah','tambahan'))");
        }
    }

    public function down(): void
    {
        DB::table('class_groups')
            ->where('jenis_kelas', 'tambahan')
            ->update(['jenis_kelas' => 'formal']);

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE class_groups MODIFY jenis_kelas ENUM('formal','muadalah') NOT NULL");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE class_groups DROP CONSTRAINT IF EXISTS class_groups_jenis_kelas_check");
            DB::statement("ALTER TABLE class_groups ADD CONSTRAINT class_groups_jenis_kelas_check CHECK (jenis_kelas IN ('formal','muadalah'))");
        }
    }
};
