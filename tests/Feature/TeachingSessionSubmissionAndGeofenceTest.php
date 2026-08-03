<?php

namespace Tests\Feature;

use App\Models\AbsensiLokasi;
use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\ClassGroupStudent;
use App\Models\Guru;
use App\Models\Schedule;
use App\Models\ScheduleSession;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeachingSessionSubmissionAndGeofenceTest extends TestCase
{
    use RefreshDatabase;

    protected User $scheduledTeacher;

    protected User $substituteTeacher;

    protected Schedule $schedule;

    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        // School location set to Surabaya (-7.3108238, 112.7292373, 200m radius)
        AbsensiLokasi::create([
            'latitude' => -7.3108238,
            'longitude' => 112.7292373,
            'radius' => 0.2,
        ]);

        $this->scheduledTeacher = User::factory()->create([
            'role' => 'guru',
            'status' => 'aktif',
        ]);
        Guru::create(['user_id' => $this->scheduledTeacher->id, 'jenis' => 'formal']);

        $this->substituteTeacher = User::factory()->create([
            'role' => 'guru',
            'status' => 'aktif',
        ]);
        Guru::create(['user_id' => $this->substituteTeacher->id, 'jenis' => 'formal']);

        $academicYear = AcademicYear::create([
            'name' => '2025/2026',
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $subject = Subject::create([
            'nama_mapel' => 'Fisika',
            'kode_mapel' => 'FIS',
            'jenis_mapel' => 'formal',
        ]);

        $classGroup = ClassGroup::create([
            'nama_kelas' => 'XI IPA 1',
            'jenis_kelas' => 'formal',
            'academic_year_id' => $academicYear->id,
        ]);

        $this->schedule = Schedule::create([
            'user_id' => $this->scheduledTeacher->id,
            'class_group_id' => $classGroup->id,
            'subject_id' => $subject->id,
            'hari' => 'Senin',
            'jam_mulai' => '07:15:00',
            'jam_selesai' => '08:35:00',
            'academic_year_id' => $academicYear->id,
        ]);

        $this->student = Student::create([
            'nis' => '3001',
            'nama_lengkap' => 'Budi Pekerti',
            'jenis_kelamin' => 'L',
        ]);

        ClassGroupStudent::create([
            'class_group_id' => $classGroup->id,
            'student_id' => $this->student->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'active',
        ]);
    }

    public function test_teacher_wizard_renders_reactive_steps_and_guided_submit_validation(): void
    {
        $response = $this->actingAs($this->scheduledTeacher)
            ->get(route('guru.schedule.absen', $this->schedule));

        $response->assertOk()
            ->assertSee('Langkah <span x-text="step"></span> dari 5', escape: false)
            ->assertSee('step > 1', escape: false)
            ->assertSee('@click="nextStep(4)"', escape: false)
            ->assertSee('@submit="handleSubmit($event)"', escape: false)
            ->assertSee('Status kehadiran seluruh siswa wajib diisi sebelum sesi diselesaikan.');
    }

    /** @test */
    public function teaching_session_submission_creates_session_student_attendance_and_teacher_attendance_atomically()
    {
        $response = $this->actingAs($this->scheduledTeacher)
            ->post(route('guru.schedule.absen.submit', $this->schedule->class_group_id), [
                'schedule_id' => $this->schedule->id,
                'pertemuan' => 1,
                'materi' => 'Hukum Newton I',
                'classroom_condition' => 'Kondisi tenang dan rapi',
                'latitude' => -7.310820,
                'longitude' => 112.729230,
                'location_accuracy' => 10.5,
                'attendance' => [
                    $this->student->id => 'hadir',
                ],
            ]);

        $response->assertRedirect(route('guru.schedule'));

        // 1. Verify ScheduleSession
        $this->assertDatabaseHas('schedule_sessions', [
            'schedule_id' => $this->schedule->id,
            'meeting_no' => 1,
            'scheduled_teacher_id' => $this->scheduledTeacher->id,
            'actual_teacher_id' => $this->scheduledTeacher->id,
            'classroom_condition' => 'Kondisi tenang dan rapi',
            'location_validation_status' => 'inside_geofence',
            'status' => 'completed',
        ]);

        // 2. Verify Student Attendance
        $this->assertDatabaseHas('student_attendance', [
            'schedule_id' => $this->schedule->id,
            'student_id' => $this->student->id,
            'status' => 'hadir',
            'materi' => 'Hukum Newton I',
        ]);

        // 3. Verify Teacher Teaching Attendance
        $this->assertDatabaseHas('teacher_teaching_attendances', [
            'teacher_id' => $this->scheduledTeacher->id,
            'schedule_id' => $this->schedule->id,
            'status' => 'hadir',
            'source' => 'journal_submission',
            'location_validation_status' => 'inside_geofence',
        ]);
    }

    /** @test */
    public function session_submitted_outside_geofence_is_marked_as_outside_geofence_without_deleting_session()
    {
        $response = $this->actingAs($this->scheduledTeacher)
            ->post(route('guru.schedule.absen.submit', $this->schedule->class_group_id), [
                'schedule_id' => $this->schedule->id,
                'pertemuan' => 2,
                'materi' => 'Hukum Newton II',
                'latitude' => -7.400000, // Far outside school
                'longitude' => 112.800000,
                'location_accuracy' => 5.0,
                'attendance' => [
                    $this->student->id => 'hadir',
                ],
            ]);

        $response->assertRedirect(route('guru.schedule'));

        $this->assertDatabaseHas('schedule_sessions', [
            'schedule_id' => $this->schedule->id,
            'meeting_no' => 2,
            'location_validation_status' => 'outside_geofence',
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('teacher_teaching_attendances', [
            'teacher_id' => $this->scheduledTeacher->id,
            'location_validation_status' => 'outside_geofence',
        ]);
    }

    /** @test */
    public function substitute_teacher_submission_tracks_actual_teacher_and_substitute_status()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);

        $assignment = $this->actingAs($admin)
            ->post(route('admin.schedules.assign-substitute', $this->schedule), [
                'date' => now()->toDateString(),
                'substitute_teacher_id' => $this->substituteTeacher->id,
            ]);

        $assignment->assertRedirect();
        $this->assertDatabaseHas('schedule_sessions', [
            'schedule_id' => $this->schedule->id,
            'date' => now()->toDateString(),
            'actual_teacher_id' => $this->substituteTeacher->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->substituteTeacher)
            ->post(route('guru.schedule.absen.submit', $this->schedule->class_group_id), [
                'schedule_id' => $this->schedule->id,
                'pertemuan' => 3,
                'materi' => 'Hukum Newton III (Guru Pengganti)',
                'latitude' => -7.310820,
                'longitude' => 112.729230,
                'attendance' => [
                    $this->student->id => 'hadir',
                ],
            ]);

        $response->assertRedirect(route('guru.schedule'));

        $this->assertDatabaseHas('schedule_sessions', [
            'schedule_id' => $this->schedule->id,
            'scheduled_teacher_id' => $this->scheduledTeacher->id,
            'actual_teacher_id' => $this->substituteTeacher->id,
            'meeting_no' => 3,
        ]);

        $this->assertDatabaseHas('teacher_teaching_attendances', [
            'teacher_id' => $this->substituteTeacher->id,
            'status' => 'substitute',
            'source' => 'substitute_teacher',
        ]);
    }

    /** @test */
    public function teacher_cannot_self_declare_as_substitute_through_browser_payload()
    {
        $response = $this->actingAs($this->substituteTeacher)
            ->post(route('guru.schedule.absen.submit', $this->schedule->class_group_id), [
                'schedule_id' => $this->schedule->id,
                'pertemuan' => 4,
                'materi' => 'Percobaan bypass guru pengganti',
                'actual_teacher_id' => $this->substituteTeacher->id,
                'attendance' => [$this->student->id => 'hadir'],
            ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('schedule_sessions', [
            'schedule_id' => $this->schedule->id,
            'meeting_no' => 4,
        ]);
    }

    public function test_teacher_can_save_and_resume_an_incomplete_session_draft(): void
    {
        $response = $this->actingAs($this->scheduledTeacher)
            ->postJson(route('guru.schedule.draft', $this->schedule), [
                'pertemuan' => 5,
                'materi' => 'Draft Hukum Newton',
                'classroom_condition' => 'Kelas kondusif',
                'attendance' => [$this->student->id => 'izin'],
            ]);

        $response->assertOk()->assertJsonPath('message', 'Draft tersimpan.');
        $session = ScheduleSession::where('schedule_id', $this->schedule->id)->firstOrFail();
        $this->assertSame('draft', $session->status);
        $this->assertSame('Draft Hukum Newton', $session->draft_payload['materi']);
        $this->assertSame('izin', $session->draft_payload['attendance'][$this->student->id]);

        $this->actingAs($this->scheduledTeacher)
            ->get(route('guru.schedule.absen', $this->schedule))
            ->assertOk()
            ->assertSee('Draft Hukum Newton')
            ->assertSee('Draft terakhir dimuat');
    }

    public function test_unassigned_teacher_cannot_save_a_draft_for_another_teacher(): void
    {
        $this->actingAs($this->substituteTeacher)
            ->postJson(route('guru.schedule.draft', $this->schedule), ['materi' => 'Bypass'])
            ->assertForbidden();

        $this->assertDatabaseCount('schedule_sessions', 0);
    }
}
