<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('class_group_student', function (Blueprint $table) {
            $table->timestamp('joined_at')->nullable()->after('academic_year_id');
            $table->timestamp('left_at')->nullable()->after('joined_at');
            $table->enum('status', ['active', 'inactive', 'completed', 'transferred'])
                ->default('active')
                ->after('left_at');
            $table->string('enrollment_source')->default('manual')->after('status');
        });

        // Backfill joined_at for existing records
        DB::table('class_group_student')->whereNull('joined_at')->update([
            'joined_at' => DB::raw('created_at'),
            'status' => 'active',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_group_student', function (Blueprint $table) {
            $table->dropColumn(['joined_at', 'left_at', 'status', 'enrollment_source']);
        });
    }
};
