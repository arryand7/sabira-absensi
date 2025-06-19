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
        Schema::create('kegiatan_asrama', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // contoh: Sholat Subuh, Kajian Malam Jumat
            $table->enum('jenis', ['sholat', 'kegiatan']); // sholat atau kegiatan khusus
            $table->boolean('berulang')->default(false); // apakah kegiatan ini rutin
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kegiatan_asrama');
    }
};
