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
        Schema::create('teacher_teaching_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
            $table->foreignId('schedule_session_id')->constrained('schedule_sessions')->onDelete('cascade');
            $table->date('attendance_date');
            $table->timestamp('check_in_at')->useCurrent();
            $table->enum('status', ['hadir', 'terlambat', 'substitute', 'manual_admin'])->default('hadir');
            $table->enum('source', ['journal_submission', 'manual_admin', 'substitute_teacher'])->default('journal_submission');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('location_accuracy', 8, 2)->nullable();
            $table->enum('location_validation_status', [
                'inside_geofence',
                'outside_geofence',
                'low_accuracy',
                'location_unavailable',
                'manually_approved',
            ])->default('inside_geofence');
            $table->timestamps();

            $table->unique(['schedule_session_id', 'teacher_id'], 'unique_teacher_session_teaching_attendance');
            $table->index(['teacher_id', 'attendance_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_teaching_attendances');
    }
};
