<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\ClassGroup;
use App\Models\ClassGroupStudent;
use App\Models\Guru;
use App\Models\Schedule;
use App\Models\ScheduleSession;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\StudentProgressReportService;
use App\Services\TeacherTeachingReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportAndRiskDetectionTest extends TestCase
{
    use RefreshDatabase;

    protected StudentProgressReportService $studentReportService;

    protected TeacherTeachingReportService $teacherReportService;

    protected AcademicYear $academicYear;

    protected Subject $subject;

    protected ClassGroup $classGroup;

    protected User $teacher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->studentReportService = new StudentProgressReportService;
        $this->teacherReportService = new TeacherTeachingReportService;

        $this->teacher = User::factory()->create(['role' => 'guru', 'status' => 'aktif']);
        Guru::create(['user_id' => $this->teacher->id, 'jenis' => 'formal']);

        $this->academicYear = AcademicYear::create([
            'name' => '2025/2026',
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $this->subject = Subject::create([
            'nama_mapel' => 'Matematika',
            'kode_mapel' => 'MTK',
            'jenis_mapel' => 'formal',
        ]);

        $this->classGroup = ClassGroup::create([
            'nama_kelas' => 'X IPA 1',
            'jenis_kelas' => 'formal',
            'academic_year_id' => $this->academicYear->id,
        ]);
    }

    protected function createSchedule(): Schedule
    {
        return Schedule::create([
            'user_id' => $this->teacher->id,
            'class_group_id' => $this->classGroup->id,
            'subject_id' => $this->subject->id,
            'hari' => 'Senin',
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '08:30:00',
            'academic_year_id' => $this->academicYear->id,
        ]);
    }

    /** @test */
    public function it_identifies_high_risk_students_when_alpa_reaches_three_or_more()
    {
        $student = Student::create([
            'nis' => '4001',
            'nama_lengkap' => 'Siswa Risko',
            'jenis_kelamin' => 'L',
        ]);

        $schedule = $this->createSchedule();
        ClassGroupStudent::create([
            'class_group_id' => $this->classGroup->id,
            'student_id' => $student->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'active',
        ]);

        // Create 3 Alpa attendances
        for ($i = 1; $i <= 3; $i++) {
            Attendance::create([
                'schedule_id' => $schedule->id,
                'student_id' => $student->id,
                'tanggal' => now()->subDays($i)->toDateString(),
                'status' => 'alpa',
                'pertemuan' => $i,
                'jam_mulai' => '07:00:00',
                'jam_selesai' => '08:30:00',
            ]);
        }

        $summary = $this->studentReportService->getStudentSummary($student->id);

        $this->assertEquals(3, $summary['alpa']);
        $this->assertEquals('high', $summary['risk_level']);
        $this->assertContains('Alpa sebanyak 3 kali.', $summary['risk_reasons']);

        $atRisk = $this->studentReportService->getAtRiskStudents();
        $this->assertNotEmpty($atRisk);
        $this->assertEquals($student->id, $atRisk[0]['student']->id);
    }

    /** @test */
    public function it_computes_teacher_geofence_compliance_and_detects_teaching_anomalies()
    {
        $schedule = $this->createSchedule();

        // Create 1 inside geofence session and 2 outside geofence sessions
        ScheduleSession::create([
            'schedule_id' => $schedule->id,
            'subject_id' => $schedule->subject_id,
            'class_group_id' => $schedule->class_group_id,
            'academic_year_id' => $schedule->academic_year_id,
            'date' => now()->subDays(2)->toDateString(),
            'start_time' => '07:00:00',
            'end_time' => '08:30:00',
            'meeting_no' => 1,
            'scheduled_teacher_id' => $this->teacher->id,
            'actual_teacher_id' => $this->teacher->id,
            'location_validation_status' => 'inside_geofence',
            'status' => 'completed',
        ]);

        ScheduleSession::create([
            'schedule_id' => $schedule->id,
            'subject_id' => $schedule->subject_id,
            'class_group_id' => $schedule->class_group_id,
            'academic_year_id' => $schedule->academic_year_id,
            'date' => now()->subDay()->toDateString(),
            'start_time' => '07:00:00',
            'end_time' => '08:30:00',
            'meeting_no' => 2,
            'scheduled_teacher_id' => $this->teacher->id,
            'actual_teacher_id' => $this->teacher->id,
            'location_validation_status' => 'outside_geofence',
            'status' => 'completed',
        ]);

        ScheduleSession::create([
            'schedule_id' => $schedule->id,
            'subject_id' => $schedule->subject_id,
            'class_group_id' => $schedule->class_group_id,
            'academic_year_id' => $schedule->academic_year_id,
            'date' => now()->toDateString(),
            'start_time' => '07:00:00',
            'end_time' => '08:30:00',
            'meeting_no' => 3,
            'scheduled_teacher_id' => $this->teacher->id,
            'actual_teacher_id' => $this->teacher->id,
            'location_validation_status' => 'outside_geofence',
            'status' => 'completed',
        ]);

        $summary = $this->teacherReportService->getTeacherSummary($this->teacher->id);

        $this->assertEquals(3, $summary['total_taught']);
        $this->assertEquals(1, $summary['inside_geofence']);
        $this->assertEquals(2, $summary['outside_geofence']);
        $this->assertEquals(33.3, $summary['geofence_compliance_rate']);
        $this->assertTrue($summary['has_anomaly']);

        $anomalies = $this->teacherReportService->getTeachingAnomalies();
        $this->assertNotEmpty($anomalies);
        $this->assertEquals($this->teacher->id, $anomalies[0]['teacher']->id);
    }
}
