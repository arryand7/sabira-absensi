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
        Schema::create('teacher_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('gurus')->onDelete('cascade');
            $table->foreignId('education_program_id')->constrained('education_programs')->onDelete('cascade');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['guru_id', 'education_program_id'], 'teacher_programs_guru_program_unique');
        });

        // Backfill existing teacher program assignments based on gurus.jenis
        $formalProgram = DB::table('education_programs')->where('code', 'formal')->first();
        $muadalahProgram = DB::table('education_programs')->where('code', 'muadalah')->first();

        $gurus = DB::table('gurus')->get();

        foreach ($gurus as $guru) {
            if ($guru->jenis === 'formal' && $formalProgram) {
                DB::table('teacher_programs')->insertOrIgnore([
                    'guru_id' => $guru->id,
                    'education_program_id' => $formalProgram->id,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } elseif ($guru->jenis === 'muadalah' && $muadalahProgram) {
                DB::table('teacher_programs')->insertOrIgnore([
                    'guru_id' => $guru->id,
                    'education_program_id' => $muadalahProgram->id,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_programs');
    }
};
