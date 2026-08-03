<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->foreignId('education_program_id')
                ->nullable()
                ->after('class_group_id')
                ->constrained('education_programs')
                ->nullOnDelete();
        });

        $programs = DB::table('education_programs')
            ->where('is_active', true)
            ->get(['id', 'default_start_time', 'default_end_time']);
        $classPrograms = DB::table('class_groups')->pluck('education_program_id', 'id');

        DB::table('schedules')->orderBy('id')->chunkById(250, function ($schedules) use ($programs, $classPrograms) {
            foreach ($schedules as $schedule) {
                $start = substr((string) $schedule->jam_mulai, 0, 5);
                $end = substr((string) $schedule->jam_selesai, 0, 5);
                $matchingPrograms = $programs->filter(fn ($program) => $start >= substr((string) $program->default_start_time, 0, 5)
                    && $end <= substr((string) $program->default_end_time, 0, 5));
                $programId = $matchingPrograms->count() === 1
                    ? $matchingPrograms->first()->id
                    : $classPrograms->get($schedule->class_group_id);

                DB::table('schedules')->where('id', $schedule->id)->update([
                    'education_program_id' => $programId,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropConstrainedForeignId('education_program_id');
        });
    }
};
