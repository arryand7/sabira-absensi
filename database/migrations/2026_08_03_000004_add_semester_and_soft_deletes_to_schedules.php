<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->string('semester', 16)->default('ganjil')->after('academic_year_id')->index();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex(['semester']);
            $table->dropColumn('semester');
            $table->dropSoftDeletes();
        });
    }
};
