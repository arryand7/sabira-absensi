<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('users')->where('role', 'super_admin')->exists()) {
            return;
        }

        $adminId = DB::table('users')
            ->where('role', 'admin')
            ->whereIn('status', ['aktif', 'active'])
            ->oldest('id')
            ->value('id');

        if ($adminId) {
            DB::table('users')->where('id', $adminId)->update(['role' => 'super_admin']);
        }
    }

    public function down(): void
    {
        // Role yang dipilih pengguna setelah migration tidak diturunkan secara otomatis.
    }
};
