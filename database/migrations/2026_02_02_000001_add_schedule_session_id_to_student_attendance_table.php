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
            $table->foreignId('schedule_session_id')
                ->nullable()
                ->after('schedule_id')
                ->constrained('schedule_sessions')
                ->nullOnDelete();
            $table->unique(
                ['schedule_session_id', 'student_id'],
                'student_attendance_session_student_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_attendance', function (Blueprint $table) {
            $table->dropUnique('student_attendance_session_student_unique');
            $table->dropConstrainedForeignId('schedule_session_id');
        });
    }
};
