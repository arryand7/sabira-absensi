<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\EducationProgram;
use App\Models\Guru;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleTimeSlotPolicyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $teacher;

    private AcademicYear $year;

    private EducationProgram $formal;

    private EducationProgram $muadalah;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $this->teacher = User::factory()->create(['role' => 'guru', 'status' => 'aktif']);
        Guru::create(['user_id' => $this->teacher->id, 'jenis' => 'formal']);
        $this->year = AcademicYear::create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);
        $this->formal = EducationProgram::where('code', 'formal')->firstOrFail();
        $this->muadalah = EducationProgram::where('code', 'muadalah')->firstOrFail();
    }

    public function test_admin_can_open_and_update_dynamic_time_slot_policy(): void
    {
        $slot = $this->formal->timeSlots()->where('slot_number', 1)->firstOrFail();

        $this->actingAs($this->admin)
            ->get(route('admin.schedule-time-slots.index', ['program_id' => $this->formal->id]))
            ->assertOk()
            ->assertSee('Kebijakan Jam Pelajaran')
            ->assertSee('07:15')
            ->assertSee('Jumat');

        $this->actingAs($this->admin)
            ->put(route('admin.schedule-time-slots.update', $slot), [
                'education_program_id' => $this->formal->id,
                'position' => $slot->position,
                'slot_number' => 1,
                'label' => 'Jam Pembuka',
                'start_time' => '07:20',
                'end_time' => '08:00',
                'friday_enabled' => '1',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.schedule-time-slots.index', ['program_id' => $this->formal->id]));

        $this->assertDatabaseHas('schedule_time_slots', [
            'id' => $slot->id,
            'label' => 'Jam Pembuka',
            'start_time' => '07:20',
            'end_time' => '08:00',
        ]);
    }

    public function test_teacher_cannot_manage_time_slot_policy(): void
    {
        $slot = $this->formal->timeSlots()->where('slot_number', 1)->firstOrFail();

        $this->actingAs($this->teacher)->get(route('admin.schedule-time-slots.index'))->assertForbidden();
        $this->actingAs($this->teacher)->delete(route('admin.schedule-time-slots.destroy', $slot))->assertForbidden();
    }

    public function test_program_filter_changes_schedule_records_and_time_axis(): void
    {
        $subject = Subject::create(['nama_mapel' => 'Informatika', 'kode_mapel' => 'INF', 'jenis_mapel' => 'formal']);
        $formalClass = $this->classGroup('X.1', $this->formal);
        $muadalahClass = $this->classGroup('Muadalah 1', $this->muadalah);

        Schedule::create([
            'user_id' => $this->teacher->id,
            'class_group_id' => $formalClass->id,
            'subject_id' => $subject->id,
            'academic_year_id' => $this->year->id,
            'semester' => 'ganjil',
            'hari' => 'Senin',
            'jam_mulai' => '07:15',
            'jam_selesai' => '07:55',
        ]);
        Schedule::create([
            'user_id' => $this->teacher->id,
            'class_group_id' => $muadalahClass->id,
            'subject_id' => $subject->id,
            'academic_year_id' => $this->year->id,
            'semester' => 'ganjil',
            'hari' => 'Senin',
            'jam_mulai' => '16:00',
            'jam_selesai' => '16:40',
        ]);

        $this->actingAs($this->teacher)
            ->get(route('guru.schedule', [
                'program_id' => $this->formal->id,
                'academic_year_id' => $this->year->id,
                'semester' => 'ganjil',
            ]))
            ->assertOk()
            ->assertSee('07:15–07:55')
            ->assertSee('X.1')
            ->assertDontSee('16:00–16:40')
            ->assertDontSee('Muadalah 1');

        $this->actingAs($this->teacher)
            ->get(route('guru.schedule', [
                'program_id' => $this->muadalah->id,
                'academic_year_id' => $this->year->id,
                'semester' => 'ganjil',
            ]))
            ->assertOk()
            ->assertSee('16:00–16:40')
            ->assertSee('Muadalah 1')
            ->assertDontSee('07:15–07:55')
            ->assertDontSee('X.1');
    }

    public function test_all_programs_render_separate_morning_and_afternoon_grids(): void
    {
        $response = $this->actingAs($this->teacher)->get(route('guru.schedule', [
            'academic_year_id' => $this->year->id,
            'semester' => 'ganjil',
        ]));

        $response->assertOk()
            ->assertSee('Program Formal')
            ->assertSee('Program Muadalah')
            ->assertSee('07:15–07:55')
            ->assertSee('16:00–16:40');
    }

    public function test_formal_teacher_can_create_muadalah_schedule_for_formal_class_and_see_it_in_muadalah_grid(): void
    {
        $subject = Subject::create(['nama_mapel' => "Qur'an", 'kode_mapel' => 'QRN', 'jenis_mapel' => 'muadalah']);
        $formalClass = $this->classGroup('X.3', $this->formal);

        $this->actingAs($this->teacher)->post(route('guru.schedule.store'), [
            'user_id' => $this->teacher->id,
            'subject_id' => $subject->id,
            'academic_year_id' => $this->year->id,
            'semester' => 'ganjil',
            'details' => [[
                'education_program_id' => $this->muadalah->id,
                'class_group_id' => $formalClass->id,
                'hari' => 'Senin',
                'jam_mulai' => '16:00',
                'jam_selesai' => '16:40',
            ]],
        ])->assertRedirect(route('guru.schedule'));

        $this->assertDatabaseHas('schedules', [
            'user_id' => $this->teacher->id,
            'class_group_id' => $formalClass->id,
            'education_program_id' => $this->muadalah->id,
            'hari' => 'Senin',
            'jam_mulai' => '16:00',
        ]);

        $this->actingAs($this->teacher)
            ->get(route('guru.schedule', [
                'program_id' => $this->muadalah->id,
                'academic_year_id' => $this->year->id,
                'semester' => 'ganjil',
            ]))
            ->assertOk()
            ->assertSee("Qur'an")
            ->assertSee('X.3')
            ->assertSee('16:00–16:40');
    }

    private function classGroup(string $name, EducationProgram $program): ClassGroup
    {
        return ClassGroup::create([
            'nama_kelas' => $name,
            'jenis_kelas' => $program->code,
            'education_program_id' => $program->id,
            'academic_year_id' => $this->year->id,
        ]);
    }
}
