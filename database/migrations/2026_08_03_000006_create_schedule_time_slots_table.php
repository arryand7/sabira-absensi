<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_time_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('education_program_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->unsignedSmallInteger('slot_number')->nullable();
            $table->string('label')->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_break')->default(false);
            $table->boolean('friday_enabled')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['education_program_id', 'position'], 'schedule_slots_program_position_unique');
            $table->index(['education_program_id', 'is_active', 'position'], 'schedule_slots_program_active_position_index');
        });

        $now = now();
        $formalId = DB::table('education_programs')->whereRaw('LOWER(code) = ?', ['formal'])->value('id');
        $muadalahId = DB::table('education_programs')->whereRaw('LOWER(code) = ?', ['muadalah'])->value('id');

        if ($formalId) {
            DB::table('schedule_time_slots')->insert([
                $this->slot($formalId, 1, 1, '07:15:00', '07:55:00', true, $now),
                $this->slot($formalId, 2, 2, '07:55:00', '08:35:00', true, $now),
                $this->slot($formalId, 3, 3, '08:35:00', '09:15:00', true, $now),
                $this->slot($formalId, 4, 4, '09:15:00', '09:55:00', true, $now),
                $this->slot($formalId, 5, null, '09:55:00', '10:25:00', true, $now, true, 'Istirahat'),
                $this->slot($formalId, 6, 5, '10:25:00', '11:05:00', true, $now),
                $this->slot($formalId, 7, 6, '11:05:00', '11:45:00', false, $now),
                $this->slot($formalId, 8, 7, '11:45:00', '12:25:00', false, $now),
                $this->slot($formalId, 9, 8, '12:25:00', '13:05:00', false, $now),
            ]);
        }

        if ($muadalahId) {
            DB::table('schedule_time_slots')->insert([
                $this->slot($muadalahId, 1, 1, '16:00:00', '16:40:00', true, $now),
                $this->slot($muadalahId, 2, 2, '16:40:00', '17:20:00', true, $now),
                $this->slot($muadalahId, 3, 3, '17:20:00', '18:00:00', true, $now),
                $this->slot($muadalahId, 4, 4, '18:00:00', '18:40:00', true, $now),
                $this->slot($muadalahId, 5, 5, '18:40:00', '19:20:00', true, $now),
                $this->slot($muadalahId, 6, 6, '19:20:00', '20:00:00', true, $now),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_time_slots');
    }

    private function slot(
        int $programId,
        int $position,
        ?int $slotNumber,
        string $start,
        string $end,
        bool $fridayEnabled,
        mixed $now,
        bool $isBreak = false,
        ?string $label = null,
    ): array {
        return [
            'education_program_id' => $programId,
            'position' => $position,
            'slot_number' => $slotNumber,
            'label' => $label,
            'start_time' => $start,
            'end_time' => $end,
            'is_break' => $isBreak,
            'friday_enabled' => $fridayEnabled,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
};
