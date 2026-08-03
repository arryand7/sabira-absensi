<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\ClassGroup;
use App\Models\ClassGroupStudent;
use App\Models\Schedule;
use App\Models\ScheduleSession;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportDrillDownTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_drill_down_from_student_and_teacher_reports_using_database_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $teacher = User::factory()->create(['role' => 'guru', 'status' => 'aktif']);
        $year = AcademicYear::create(['name' => '2026/2027', 'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => true]);
        $subject = Subject::create(['nama_mapel' => 'Kimia', 'kode_mapel' => 'KIM', 'jenis_mapel' => 'formal']);
        $class = ClassGroup::create(['nama_kelas' => 'XII Sains', 'jenis_kelas' => 'formal', 'academic_year_id' => $year->id]);
        $student = Student::create(['nis' => 'RPT-01', 'nama_lengkap' => 'Siswa Laporan', 'jenis_kelamin' => 'P']);
        ClassGroupStudent::create(['class_group_id' => $class->id, 'student_id' => $student->id, 'academic_year_id' => $year->id, 'status' => 'active']);
        $schedule = Schedule::create(['user_id' => $teacher->id, 'class_group_id' => $class->id, 'subject_id' => $subject->id, 'academic_year_id' => $year->id, 'hari' => 'Senin', 'jam_mulai' => '07:15:00', 'jam_selesai' => '07:55:00']);
        $session = ScheduleSession::create(['schedule_id' => $schedule->id, 'subject_id' => $subject->id, 'class_group_id' => $class->id, 'academic_year_id' => $year->id, 'date' => now()->toDateString(), 'start_time' => '07:15:00', 'end_time' => '07:55:00', 'meeting_no' => 1, 'scheduled_teacher_id' => $teacher->id, 'actual_teacher_id' => $teacher->id, 'status' => 'completed', 'location_validation_status' => 'inside_geofence']);
        Attendance::create(['schedule_id' => $schedule->id, 'schedule_session_id' => $session->id, 'student_id' => $student->id, 'tanggal' => now()->toDateString(), 'status' => 'hadir', 'pertemuan' => 1, 'jam_mulai' => '07:15:00', 'jam_selesai' => '07:55:00', 'materi' => 'Ikatan Kimia']);

        $this->actingAs($admin)
            ->get(route('laporan.murid', ['tahun_ajaran' => $year->id]))
            ->assertOk()
            ->assertSee('Siswa Laporan')
            ->assertSee(route('laporan.murid.show', ['student' => $student, 'tahun_ajaran' => $year->id]), false);

        $this->get(route('laporan.murid.show', ['student' => $student, 'tahun_ajaran' => $year->id]))
            ->assertOk()
            ->assertSee('Ikatan Kimia')
            ->assertSee('100.0%');

        $this->get(route('laporan.pertemuan.teacher', ['teacher' => $teacher, 'start_date' => now()->startOfMonth()->toDateString(), 'end_date' => now()->endOfMonth()->toDateString()]))
            ->assertOk()
            ->assertSee($teacher->name)
            ->assertSee('Kimia')
            ->assertSee('Kepatuhan Lokasi');
    }

    public function test_teacher_cannot_access_management_report_drill_down(): void
    {
        $teacher = User::factory()->create(['role' => 'guru', 'status' => 'aktif']);
        $student = Student::create(['nis' => 'PRIVATE-01', 'nama_lengkap' => 'Data Privat', 'jenis_kelamin' => 'L']);

        $this->actingAs($teacher)->get(route('laporan.murid.show', $student))->assertForbidden();
        $this->get(route('laporan.pertemuan.teacher', $teacher))->assertForbidden();
    }
}
