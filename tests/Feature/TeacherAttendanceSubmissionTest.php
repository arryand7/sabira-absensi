<?php

namespace Tests\Feature;

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

class TeacherAttendanceSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacherUser;

    protected User $otherTeacherUser;

    protected Schedule $schedule;

    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacherUser = User::factory()->create([
            'role' => 'guru',
            'status' => 'aktif',
        ]);
        Guru::create([
            'user_id' => $this->teacherUser->id,
            'jenis' => 'formal',
        ]);

        $this->otherTeacherUser = User::factory()->create([
            'role' => 'guru',
            'status' => 'aktif',
        ]);
        Guru::create([
            'user_id' => $this->otherTeacherUser->id,
            'jenis' => 'formal',
        ]);

        $academicYear = AcademicYear::create([
            'name' => '2025/2026',
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $subject = Subject::create([
            'nama_mapel' => 'Matematika',
            'kode_mapel' => 'MTK',
            'jenis_mapel' => 'formal',
        ]);

        $classGroup = ClassGroup::create([
            'nama_kelas' => 'X IPA 1',
            'jenis_kelas' => 'formal',
            'academic_year_id' => $academicYear->id,
        ]);

        $this->schedule = Schedule::create([
            'user_id' => $this->teacherUser->id,
            'class_group_id' => $classGroup->id,
            'subject_id' => $subject->id,
            'hari' => 'Senin',
            'jam_mulai' => '07:15:00',
            'jam_selesai' => '08:35:00',
            'academic_year_id' => $academicYear->id,
        ]);

        $this->student = Student::create([
            'nis' => '1001',
            'nama_lengkap' => 'Ahmad Santri',
            'jenis_kelamin' => 'L',
        ]);

        ClassGroupStudent::create([
            'class_group_id' => $classGroup->id,
            'student_id' => $this->student->id,
            'academic_year_id' => $academicYear->id,
        ]);
    }

    /** @test */
    public function authorized_teacher_can_submit_attendance_successfully()
    {
        $response = $this->actingAs($this->teacherUser)
            ->post(route('guru.schedule.absen.submit', $this->schedule->class_group_id), [
                'schedule_id' => $this->schedule->id,
                'pertemuan' => 1,
                'materi' => 'Aljabar Dasar',
                'jam_mulai' => '07:15',
                'jam_selesai' => '08:35',
                'attendance' => [
                    $this->student->id => 'hadir',
                ],
            ]);

        $response->assertRedirect(route('guru.schedule'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('schedule_sessions', [
            'schedule_id' => $this->schedule->id,
            'meeting_no' => 1,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('student_attendance', [
            'schedule_id' => $this->schedule->id,
            'student_id' => $this->student->id,
            'status' => 'hadir',
            'materi' => 'Aljabar Dasar',
        ]);
    }

    /** @test */
    public function unauthorized_teacher_cannot_submit_attendance_for_another_teachers_schedule()
    {
        $response = $this->actingAs($this->otherTeacherUser)
            ->post(route('guru.schedule.absen.submit', $this->schedule->class_group_id), [
                'schedule_id' => $this->schedule->id,
                'pertemuan' => 1,
                'materi' => 'Mencoba Bypass IDOR',
                'attendance' => [
                    $this->student->id => 'hadir',
                ],
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseCount('schedule_sessions', 0);
        $this->assertDatabaseCount('student_attendance', 0);
    }

    /** @test */
    public function submission_fails_if_student_is_not_registered_in_the_class_group()
    {
        $outsideStudent = Student::create([
            'nis' => '9999',
            'nama_lengkap' => 'Siswa Luar Kelas',
            'jenis_kelamin' => 'P',
        ]);

        $response = $this->actingAs($this->teacherUser)
            ->post(route('guru.schedule.absen.submit', $this->schedule->class_group_id), [
                'schedule_id' => $this->schedule->id,
                'pertemuan' => 1,
                'materi' => 'Tes Siswa Luar',
                'attendance' => [
                    $outsideStudent->id => 'hadir',
                ],
            ]);

        $response->assertSessionHasErrors(["attendance.{$outsideStudent->id}"]);
        $this->assertDatabaseCount('schedule_sessions', 0);
        $this->assertDatabaseCount('student_attendance', 0);
    }

    /** @test */
    public function submission_fails_on_duplicate_meeting_number()
    {
        // Session 1 already exists
        ScheduleSession::create([
            'schedule_id' => $this->schedule->id,
            'subject_id' => $this->schedule->subject_id,
            'class_group_id' => $this->schedule->class_group_id,
            'academic_year_id' => $this->schedule->academic_year_id,
            'date' => '2026-02-01',
            'start_time' => '07:15',
            'end_time' => '08:35',
            'meeting_no' => 1,
            'created_by' => $this->teacherUser->id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->teacherUser)
            ->post(route('guru.schedule.absen.submit', $this->schedule->class_group_id), [
                'schedule_id' => $this->schedule->id,
                'pertemuan' => 1,
                'materi' => 'Pertemuan Duplikat',
                'attendance' => [
                    $this->student->id => 'hadir',
                ],
            ]);

        $response->assertSessionHas('error');
    }
}
