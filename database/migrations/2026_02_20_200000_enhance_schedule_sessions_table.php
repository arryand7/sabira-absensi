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
        Schema::table('schedule_sessions', function (Blueprint $table) {
            $table->foreignId('scheduled_teacher_id')->nullable()->after('schedule_id')->constrained('users')->nullOnDelete();
            $table->foreignId('actual_teacher_id')->nullable()->after('scheduled_teacher_id')->constrained('users')->nullOnDelete();
            $table->text('classroom_condition')->nullable()->after('status');
            $table->text('teacher_notes')->nullable()->after('classroom_condition');
            $table->decimal('latitude', 10, 7)->nullable()->after('teacher_notes');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->decimal('location_accuracy', 8, 2)->nullable()->after('longitude');
            $table->decimal('distance_from_location', 8, 2)->nullable()->after('location_accuracy');
            $table->enum('location_validation_status', [
                'inside_geofence',
                'outside_geofence',
                'low_accuracy',
                'location_unavailable',
                'manually_approved',
            ])->default('inside_geofence')->after('distance_from_location');
            $table->timestamp('submitted_at')->nullable()->after('location_validation_status');
            $table->timestamp('completed_at')->nullable()->after('submitted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule_sessions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('scheduled_teacher_id');
            $table->dropConstrainedForeignId('actual_teacher_id');
            $table->dropColumn([
                'classroom_condition',
                'teacher_notes',
                'latitude',
                'longitude',
                'location_accuracy',
                'distance_from_location',
                'location_validation_status',
                'submitted_at',
                'completed_at',
            ]);
        });
    }
};
