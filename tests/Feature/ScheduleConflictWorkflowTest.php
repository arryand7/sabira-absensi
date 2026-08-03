<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\EducationProgram;
use App\Models\Guru;
use App\Models\Schedule;
use App\Models\ScheduleConflict;
use App\Models\Subject;
use App\Models\User;
use App\Services\ScheduleConflictService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleConflictWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $teacher;

    private User $otherTeacher;

    private AcademicYear $year;

    private Subject $subject;

    private ClassGroup $firstClass;

    private ClassGroup $secondClass;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $this->teacher = User::factory()->create(['role' => 'guru', 'status' => 'aktif']);
        $this->otherTeacher = User::factory()->create(['role' => 'guru', 'status' => 'aktif']);
        Guru::create(['user_id' => $this->teacher->id, 'jenis' => 'formal']);
        Guru::create(['user_id' => $this->otherTeacher->id, 'jenis' => 'formal']);

        $this->year = AcademicYear::create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);
        $program = EducationProgram::query()->where('code', 'formal')->firstOrFail();
        $this->subject = Subject::create([
            'nama_mapel' => 'Informatika',
            'kode_mapel' => 'INF',
            'jenis_mapel' => 'formal',
        ]);
        $this->firstClass = ClassGroup::create([
            'nama_kelas' => 'X.1',
            'jenis_kelas' => 'formal',
            'education_program_id' => $program->id,
            'academic_year_id' => $this->year->id,
        ]);
        $this->secondClass = ClassGroup::create([
            'nama_kelas' => 'X.2',
            'jenis_kelas' => 'formal',
            'education_program_id' => $program->id,
            'academic_year_id' => $this->year->id,
        ]);
    }

    public function test_overlapping_schedule_is_saved_and_creates_pending_conflict(): void
    {
        $existing = $this->schedule($this->teacher, $this->firstClass, '07:15', '08:35');

        $response = $this->actingAs($this->admin)->post(route('admin.schedules.store'), [
            'user_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'academic_year_id' => $this->year->id,
            'semester' => 'ganjil',
            'details' => [[
                'class_group_id' => $this->secondClass->id,
                'hari' => 'Senin',
                'jam_mulai' => '07:55',
                'jam_selesai' => '09:15',
            ]],
        ]);

        $response->assertRedirect(route('admin.schedules.show-by-teacher', $this->teacher));
        $response->assertSessionHas('warning');
        $this->assertDatabaseCount('schedules', 2);
        $this->assertDatabaseHas('schedule_conflicts', [
            'schedule_id' => Schedule::query()->whereKeyNot($existing->id)->value('id'),
            'conflicting_schedule_id' => $existing->id,
            'teacher_id' => $this->teacher->id,
            'conflict_type' => 'teacher_overlap',
            'status' => ScheduleConflict::STATUS_PENDING,
        ]);
    }

    public function test_non_overlapping_and_other_teacher_schedules_do_not_create_teacher_conflict(): void
    {
        $this->schedule($this->teacher, $this->firstClass, '07:15', '07:55');
        $nonOverlap = $this->schedule($this->teacher, $this->secondClass, '07:55', '08:35');
        $otherTeacher = $this->schedule($this->otherTeacher, $this->secondClass, '07:15', '07:55');
        $service = app(ScheduleConflictService::class);

        $this->assertCount(0, $service->refreshFor($nonOverlap));
        $conflicts = $service->refreshFor($otherTeacher);

        $this->assertFalse($conflicts->contains('conflict_type', 'teacher_overlap'));
    }

    public function test_teacher_overlap_submission_is_saved_with_warning_instead_of_validation_failure(): void
    {
        $this->schedule($this->teacher, $this->firstClass, '07:15', '08:35');

        $response = $this->actingAs($this->teacher)->post(route('guru.schedule.store'), [
            'user_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'academic_year_id' => $this->year->id,
            'semester' => 'ganjil',
            'details' => [[
                'class_group_id' => $this->secondClass->id,
                'hari' => 'Senin',
                'jam_mulai' => '07:55',
                'jam_selesai' => '09:15',
            ]],
        ]);

        $response->assertRedirect(route('guru.schedule'));
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('warning');
        $this->assertDatabaseCount('schedules', 2);
        $this->assertDatabaseHas('schedule_conflicts', [
            'teacher_id' => $this->teacher->id,
            'conflict_type' => 'teacher_overlap',
            'status' => ScheduleConflict::STATUS_PENDING,
        ]);
    }

    public function test_teacher_schedule_page_renders_slot_grid_list_mode_and_conflict_status(): void
    {
        $first = $this->schedule($this->teacher, $this->firstClass, '07:15', '08:35');
        $second = $this->schedule($this->teacher, $this->secondClass, '07:55', '09:15');
        app(ScheduleConflictService::class)->refreshFor($second);

        $response = $this->actingAs($this->teacher)->get(route('guru.schedule'));

        $response->assertOk();
        $response->assertSee('sabira-app-shell', false);
        $response->assertSee('Jadwal mingguan berdasarkan jam pelajaran');
        $response->assertSee('Mingguan');
        $response->assertSee('Daftar');
        $response->assertSee('Bentrok');
        $response->assertSee($first->classGroup->nama_kelas);
    }

    public function test_admin_can_open_conflict_list_and_detail_and_dashboard_uses_pending_source(): void
    {
        $conflict = $this->makeConflict();

        $this->actingAs($this->admin)
            ->get(route('admin.schedule-conflicts.index'))
            ->assertOk()
            ->assertSee('Benturan Jadwal Guru')
            ->assertSee($this->teacher->name);

        $this->actingAs($this->admin)
            ->get(route('admin.schedule-conflicts.show', $conflict))
            ->assertOk()
            ->assertSee('Review Benturan Jadwal')
            ->assertSee('Pertahankan Jadwal Baru');

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Tinjau Konflik');
    }

    public function test_admin_can_keep_current_schedule_and_soft_delete_existing_schedule(): void
    {
        $conflict = $this->makeConflict();

        $response = $this->actingAs($this->admin)->post(
            route('admin.schedule-conflicts.resolve', $conflict),
            ['resolution' => 'keep_current', 'resolution_note' => 'Jadwal terbaru telah dikonfirmasi kurikulum.']
        );

        $response->assertRedirect(route('admin.schedule-conflicts.show', $conflict));
        $this->assertSoftDeleted('schedules', ['id' => $conflict->conflicting_schedule_id]);
        $this->assertDatabaseHas('schedule_conflicts', [
            'id' => $conflict->id,
            'status' => ScheduleConflict::STATUS_KEEP_CURRENT,
            'resolved_by' => $this->admin->id,
        ]);
    }

    public function test_admin_can_verify_both_or_dismiss_without_deleting_schedules(): void
    {
        $conflict = $this->makeConflict();

        $this->actingAs($this->admin)->post(
            route('admin.schedule-conflicts.resolve', $conflict),
            ['resolution' => 'keep_both', 'resolution_note' => 'Penugasan paralel disetujui.']
        )->assertRedirect();

        $this->assertDatabaseHas('schedule_conflicts', ['id' => $conflict->id, 'status' => ScheduleConflict::STATUS_CONFIRMED]);
        $this->assertDatabaseCount('schedules', 2);
    }

    public function test_admin_can_keep_existing_schedule_and_soft_delete_current_schedule(): void
    {
        $conflict = $this->makeConflict();

        $this->actingAs($this->admin)->post(
            route('admin.schedule-conflicts.resolve', $conflict),
            ['resolution' => 'keep_existing', 'resolution_note' => 'Jadwal lama tetap berlaku.']
        )->assertRedirect();

        $this->assertSoftDeleted('schedules', ['id' => $conflict->schedule_id]);
        $this->assertDatabaseHas('schedule_conflicts', [
            'id' => $conflict->id,
            'status' => ScheduleConflict::STATUS_KEEP_EXISTING,
        ]);
    }

    public function test_teacher_cannot_open_or_resolve_admin_conflict_workflow(): void
    {
        $conflict = $this->makeConflict();

        $this->actingAs($this->teacher)->get(route('admin.schedule-conflicts.index'))->assertForbidden();
        $this->actingAs($this->teacher)->post(
            route('admin.schedule-conflicts.resolve', $conflict),
            ['resolution' => 'dismiss']
        )->assertForbidden();

        $this->assertDatabaseHas('schedule_conflicts', ['id' => $conflict->id, 'status' => ScheduleConflict::STATUS_PENDING]);
    }

    private function makeConflict(): ScheduleConflict
    {
        $this->schedule($this->teacher, $this->firstClass, '07:15', '08:35');
        $current = $this->schedule($this->teacher, $this->secondClass, '07:55', '09:15');

        return app(ScheduleConflictService::class)->refreshFor($current)->firstWhere('conflict_type', 'teacher_overlap');
    }

    private function schedule(User $teacher, ClassGroup $classGroup, string $start, string $end): Schedule
    {
        return Schedule::create([
            'user_id' => $teacher->id,
            'class_group_id' => $classGroup->id,
            'subject_id' => $this->subject->id,
            'hari' => 'Senin',
            'jam_mulai' => $start,
            'jam_selesai' => $end,
            'academic_year_id' => $this->year->id,
            'semester' => 'ganjil',
        ]);
    }
}
