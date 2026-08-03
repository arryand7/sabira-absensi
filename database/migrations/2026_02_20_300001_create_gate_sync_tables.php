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
        Schema::create('gate_sync_runs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('initiated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', [
                'previewed',
                'applying',
                'applied',
                'report_pending',
                'completed',
                'failed',
            ])->default('previewed');
            $table->string('preview_hash')->nullable();
            $table->json('summary_counts')->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('previewed_at')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamp('reported_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('gate_sync_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gate_sync_run_id')->constrained('gate_sync_runs')->onDelete('cascade');
            $table->uuid('gate_user_uuid')->nullable();
            $table->foreignId('local_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('category', [
                'matched',
                'needs_update',
                'missing_in_application',
                'access_revoked',
                'inactive_in_gate',
                'reactivation_required',
                'local_only',
                'conflict',
            ]);
            $table->enum('selected_action', [
                'no_change',
                'create_local',
                'update_local',
                'suspend_local',
                'reactivate_local',
                'manual_review',
            ])->default('no_change');
            $table->enum('result_status', ['pending', 'success', 'failed', 'skipped'])->default('pending');
            $table->json('gate_snapshot')->nullable();
            $table->json('local_snapshot')->nullable();
            $table->json('field_differences')->nullable();
            $table->string('external_user_id')->nullable();
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['gate_sync_run_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gate_sync_items');
        Schema::dropIfExists('gate_sync_runs');
    }
};
