<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_conflicts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->restrictOnDelete();
            $table->foreignId('conflicting_schedule_id')->constrained('schedules')->restrictOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->restrictOnDelete();
            $table->string('conflict_type', 40);
            $table->string('conflict_scope', 40)->default('weekly_recurring');
            $table->string('status', 40)->default('pending_review')->index();
            $table->timestamp('detected_at');
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution_note')->nullable();
            $table->timestamps();

            $table->unique(
                ['schedule_id', 'conflicting_schedule_id', 'conflict_type'],
                'schedule_conflict_pair_type_unique'
            );
            $table->index(['teacher_id', 'status'], 'schedule_conflict_teacher_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_conflicts');
    }
};
