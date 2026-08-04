<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('karyawan', 'nama_lengkap')) {
            Schema::table('karyawan', function (Blueprint $table) {
                $table->string('nama_lengkap')->nullable()->after('divisi_id');
            });
        }

        DB::table('karyawan')
            ->where(function ($query) {
                $query->whereNull('nama_lengkap')->orWhere('nama_lengkap', '');
            })
            ->orderBy('id')
            ->eachById(function ($employee) {
                $name = DB::table('users')->where('id', $employee->user_id)->value('name');

                if (is_string($name) && trim($name) !== '') {
                    DB::table('karyawan')->where('id', $employee->id)->update([
                        'nama_lengkap' => trim($name),
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Kolom ini merupakan bagian dari schema legacy dan tidak aman untuk dihapus.
    }
};
