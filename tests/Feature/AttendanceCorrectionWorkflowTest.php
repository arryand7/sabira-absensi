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

class AttendanceCorrectionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    private User $admin;

    private Schedule $schedule;

    private Student $student;

    private ScheduleSession $session;

    protected function setUp(): void
    {
        parent::setUp();

        AbsensiLokasi::create(['latitude' => -7.31, 'longitude' => 112.72, 'radius' => 0.2]);
        $this->teacher = User::factory()->create(['role' => 'guru', 'status' => 'aktif']);
        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        Guru::create(['user_id' => $this->teacher->id, 'jenis' => 'formal']);

        $year = AcademicYear::create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);
        $subject = Subject::create(['nama_mapel' => 'Biologi', 'kode_mapel' => 'BIO', 'jenis_mapel' => 'formal']);
        $class = ClassGroup::create(['nama_kelas' => 'XI IPA', 'jenis_kelas' => 'formal', 'academic_year_id' => $year->id]);
        $this->schedule = Schedule::create([
            'user_id' => $this->teacher->id,
            'class_group_id' => $class->id,
            'subject_id' => $subject->id,
            'academic_year_id' => $year->id,
            'hari' => 'Senin',
            'jam_mulai' => '07:15:00',
            'jam_selesai' => '07:55:00',
        ]);
        $this->student = Student::create(['nis' => 'COR-01', 'nama_lengkap' => 'Siswa Koreksi', 'jenis_kelamin' => 'L']);
        ClassGroupStudent::create([
            'class_group_id' => $class->id,
            'student_id' => $this->student->id,
            'academic_year_id' => $year->id,
            'status' => 'active',
        ]);

        $this->actingAs($this->teacher)->post(route('guru.schedule.absen.submit', $class), [
            'schedule_id' => $this->schedule->id,
            'pertemuan' => 1,
            'materi' => 'Materi awal',
            'attendance' => [$this->student->id => 'hadir'],
        ])->assertRedirect(route('guru.schedule'));

        $this->session = ScheduleSession::firstOrFail();
    }

    public function test_teacher_can_request_correction_and_admin_approval_applies_it_atomically(): void
    {
        $detail = $this->actingAs($this->teacher)->get(route('guru.history.detail', $this->session));
        $detail->assertOk()->assertSee('Ajukan Koreksi Sesi')->assertSee('Siswa Koreksi');

        $request = $this->post(route('guru.history.correction.store', $this->session), [
            'reason' => 'Status siswa keliru saat sesi diselesaikan.',
            'materi' => 'Materi hasil koreksi',
            'classroom_condition' => 'Kondusif',
            'teacher_notes' => 'Diverifikasi ulang',
            'attendance' => [$this->student->id => 'izin'],
        ]);
        $request->assertRedirect(route('guru.history.detail', $this->session));

        $this->assertDatabaseHas('student_attendance', [
            'schedule_session_id' => $this->session->id,
            'student_id' => $this->student->id,
            'status' => 'hadir',
            'materi' => 'Materi awal',
        ]);
        $correction = $this->session->corrections()->firstOrFail();
        $this->assertSame('pending', $correction->status);

        $reviewPage = $this->actingAs($this->admin)->get(route('admin.attendance-corrections.show', $correction));
        $reviewPage->assertOk()->assertSee('Materi hasil koreksi')->assertSee('Setujui dan Terapkan');

        $this->post(route('admin.attendance-corrections.review', $correction), [
            'decision' => 'approved',
            'review_notes' => 'Bukti sudah diperiksa.',
        ])->assertRedirect(route('admin.attendance-corrections.show', $correction));

        $this->assertDatabaseHas('attendance_corrections', [
            'id' => $correction->id,
            'status' => 'approved',
            'reviewed_by' => $this->admin->id,
        ]);
        $this->assertDatabaseHas('student_attendance', [
            'schedule_session_id' => $this->session->id,
            'student_id' => $this->student->id,
            'status' => 'izin',
            'materi' => 'Materi hasil koreksi',
        ]);
    }

    public function test_other_teacher_cannot_view_or_correct_session_and_teacher_cannot_self_approve(): void
    {
        $otherTeacher = User::factory()->create(['role' => 'guru', 'status' => 'aktif']);

        $this->actingAs($otherTeacher)
            ->get(route('guru.history.detail', $this->session))
            ->assertForbidden();

        $this->actingAs($otherTeacher)
            ->post(route('guru.history.correction.store', $this->session), [
                'reason' => 'Mencoba mengakses data guru yang lain.',
                'materi' => 'Tidak sah',
                'attendance' => [$this->student->id => 'alpa'],
            ])->assertForbidden();

        $this->actingAs($this->teacher)
            ->get(route('admin.attendance-corrections.index'))
            ->assertForbidden();

        $this->assertDatabaseCount('attendance_corrections', 0);
    }

    public function test_duplicate_pending_request_is_rejected_and_rejection_requires_notes(): void
    {
        $payload = [
            'reason' => 'Koreksi pertama karena salah memasukkan status.',
            'materi' => 'Materi awal',
            'attendance' => [$this->student->id => 'sakit'],
        ];

        $this->actingAs($this->teacher)->post(route('guru.history.correction.store', $this->session), $payload);
        $this->post(route('guru.history.correction.store', $this->session), $payload)
            ->assertSessionHasErrors('reason');

        $correction = $this->session->corrections()->firstOrFail();
        $this->actingAs($this->admin)
            ->post(route('admin.attendance-corrections.review', $correction), ['decision' => 'rejected'])
            ->assertSessionHasErrors('review_notes');

        $this->assertDatabaseHas('attendance_corrections', ['id' => $correction->id, 'status' => 'pending']);
    }
}
