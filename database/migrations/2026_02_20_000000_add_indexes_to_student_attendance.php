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
        Schema::table('student_attendance', function (Blueprint $table) {
            $table->index(['tanggal', 'student_id'], 'idx_student_attendance_tanggal_student');
            $table->index(['schedule_id', 'tanggal'], 'idx_student_attendance_schedule_tanggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_attendance', function (Blueprint $table) {
            $table->dropIndex('idx_student_attendance_tanggal_student');
            $table->dropIndex('idx_student_attendance_schedule_tanggal');
        });
    }
};
