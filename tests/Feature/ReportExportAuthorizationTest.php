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

class ReportExportAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_exports_generate_real_pdf_and_excel_files_from_the_same_records(): void
    {
        [$admin, $teacher, $year, $student] = $this->createReportData();
        $this->actingAs($admin);

        $studentPdf = $this->get(route('laporan.murid.download', ['student' => $student, 'tahun_ajaran' => $year->id]));
        $studentPdf->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $studentPdf->getContent());

        $studentExcel = $this->get(route('laporan.murid.download.excel', ['student' => $student, 'tahun_ajaran' => $year->id]));
        $studentExcel->assertOk();
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $studentExcel->headers->get('content-type'));
        $this->assertStringStartsWith('PK', file_get_contents($studentExcel->baseResponse->getFile()->getPathname()));

        $filters = [
            'tahun_ajaran' => $year->id,
            'guru_id' => $teacher->id,
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
        ];
        $teacherPdf = $this->get(route('laporan.pertemuan.export.pdf', $filters));
        $teacherPdf->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $teacherPdf->getContent());

        $teacherExcel = $this->get(route('laporan.pertemuan.export.excel', $filters));
        $teacherExcel->assertOk();
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $teacherExcel->headers->get('content-type'));
        $this->assertStringStartsWith('PK', file_get_contents($teacherExcel->baseResponse->getFile()->getPathname()));
    }

    public function test_teacher_cannot_download_management_exports(): void
    {
        [, $teacher, $year, $student] = $this->createReportData();

        $this->actingAs($teacher)
            ->get(route('laporan.murid.download', ['student' => $student, 'tahun_ajaran' => $year->id]))
            ->assertForbidden();
        $this->get(route('laporan.murid.download.excel', ['student' => $student, 'tahun_ajaran' => $year->id]))
            ->assertForbidden();
        $this->get(route('laporan.pertemuan.export.pdf'))->assertForbidden();
        $this->get(route('laporan.pertemuan.export.excel'))->assertForbidden();
    }

    private function createReportData(): array
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $teacher = User::factory()->create(['role' => 'guru', 'status' => 'aktif']);
        $year = AcademicYear::create(['name' => '2026/2027', 'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => true]);
        $subject = Subject::create(['nama_mapel' => 'Fisika Export', 'kode_mapel' => 'FEX', 'jenis_mapel' => 'formal']);
        $class = ClassGroup::create(['nama_kelas' => 'X Export', 'jenis_kelas' => 'formal', 'academic_year_id' => $year->id]);
        $student = Student::create(['nis' => 'EXP-01', 'nama_lengkap' => 'Siswa Export', 'jenis_kelamin' => 'L']);
        ClassGroupStudent::create(['class_group_id' => $class->id, 'student_id' => $student->id, 'academic_year_id' => $year->id, 'status' => 'active']);
        $schedule = Schedule::create(['user_id' => $teacher->id, 'class_group_id' => $class->id, 'subject_id' => $subject->id, 'academic_year_id' => $year->id, 'hari' => 'Senin', 'jam_mulai' => '07:15:00', 'jam_selesai' => '07:55:00']);
        $session = ScheduleSession::create(['schedule_id' => $schedule->id, 'subject_id' => $subject->id, 'class_group_id' => $class->id, 'academic_year_id' => $year->id, 'date' => now()->toDateString(), 'start_time' => '07:15:00', 'end_time' => '07:55:00', 'meeting_no' => 1, 'scheduled_teacher_id' => $teacher->id, 'actual_teacher_id' => $teacher->id, 'status' => 'completed', 'location_validation_status' => 'inside_geofence']);
        Attendance::create(['schedule_id' => $schedule->id, 'schedule_session_id' => $session->id, 'student_id' => $student->id, 'tanggal' => now()->toDateString(), 'status' => 'hadir', 'pertemuan' => 1, 'jam_mulai' => '07:15:00', 'jam_selesai' => '07:55:00', 'materi' => 'Materi Export']);

        return [$admin, $teacher, $year, $student];
    }
}
