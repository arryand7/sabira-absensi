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
        Schema::create('class_groups', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kelas');
            $table->enum('jenis_kelas', ['formal', 'muadalah']);
            $table->foreignId('academic_year_id')
                ->constrained('academic_years');
            $table->timestamps();
            $table->foreignId('wali_kelas_id')
                ->nullable()
                ->constrained('gurus')
                ->onDelete('set null');
       });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_groups', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn('academic_year_id');
            $table->dropForeign(['wali_kelas_id']);
            $table->dropColumn('wali_kelas_id');
        });
    }
};
