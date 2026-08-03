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
        Schema::table('class_groups', function (Blueprint $table) {
            $table->foreignId('education_program_id')
                ->nullable()
                ->after('jenis_kelas')
                ->constrained('education_programs')
                ->nullOnDelete();
            $table->enum('class_type', ['reguler', 'non_reguler'])
                ->default('reguler')
                ->after('education_program_id');
            $table->string('grade_level')->nullable()->after('class_type');
            $table->boolean('is_active')->default(true)->after('grade_level');
        });

        // Backfill education_program_id based on existing jenis_kelas
        $formalProgram = DB::table('education_programs')->where('code', 'formal')->first();
        $muadalahProgram = DB::table('education_programs')->where('code', 'muadalah')->first();

        if ($formalProgram) {
            DB::table('class_groups')
                ->whereIn('jenis_kelas', ['formal', 'tambahan'])
                ->update(['education_program_id' => $formalProgram->id]);
        }

        if ($muadalahProgram) {
            DB::table('class_groups')
                ->where('jenis_kelas', 'muadalah')
                ->update(['education_program_id' => $muadalahProgram->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_groups', function (Blueprint $table) {
            $table->dropConstrainedForeignId('education_program_id');
            $table->dropColumn(['class_type', 'grade_level', 'is_active']);
        });
    }
};
