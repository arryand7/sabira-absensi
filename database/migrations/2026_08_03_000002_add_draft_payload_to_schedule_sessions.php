<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedule_sessions', function (Blueprint $table) {
            $table->json('draft_payload')->nullable()->after('teacher_notes');
        });
    }

    public function down(): void
    {
        Schema::table('schedule_sessions', function (Blueprint $table) {
            $table->dropColumn('draft_payload');
        });
    }
};
