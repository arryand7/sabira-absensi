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
        Schema::create('karyawan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('divisi_id')->nullable();
            $table->string('nama_lengkap');
            $table->string('alamat')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();

            $table->foreign('divisi_id')->references('id')->on('divisis')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawan'); // <- ini sudah benar
    }
};
