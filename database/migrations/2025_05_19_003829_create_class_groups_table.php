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
            $table->foreignId('academic_year_id')->constrained('academic_years');
            $table->foreignId('wali_kelas_id')->nullable()->constrained('gurus')->onDelete('set null');
            $table->timestamps();
            // constraint unik gabungan
            $table->unique(['nama_kelas', 'academic_year_id'], 'unique_kelas_per_tahun');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_groups', function (Blueprint $table) {
            // Hapus constraint unik dulu
            $table->dropUnique('unique_kelas_per_tahun');

            $table->dropForeign(['academic_year_id']);
            $table->dropForeign(['wali_kelas_id']);
        });

        Schema::dropIfExists('class_groups');
    }
};
