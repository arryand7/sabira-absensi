<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\EducationProgram;
use App\Models\Guru;
use App\Models\Schedule;
use App\Models\ScheduleSession;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationMenuRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        AcademicYear::create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);

        EducationProgram::create([
            'code' => 'FORMAL',
            'name' => 'Program Formal',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function admin_can_access_all_master_data_and_management_pages()
    {
        $admin = User::factory()->create(['role' => 'super_admin', 'status' => 'aktif']);

        $routes = [
            'admin.dashboard',
            'users.index',
            'admin.students.index',
            'admin.class-groups.index',
            'subjects.index',
            'admin.schedules.index',
            'admin.schedule-time-slots.index',
            'academic-years.index',
            'promotion.index',
            'divisis.index',
            'karyawan.index',
            'admin.education-programs.index',
            'laporan.karyawan',
            'laporan.murid',
            'laporan.pertemuan',
            'admin.sync.index',
            'admin.lokasi.edit',
            'admin.settings.app',
            'admin.settings.sso',
        ];

        foreach ($routes as $routeName) {
            $response = $this->actingAs($admin)->get(route($routeName));
            $response->assertStatus(200);
        }
    }

    /** @test */
    public function guru_can_access_guru_dashboard_schedules_history_and_reports()
    {
        $guruUser = User::factory()->create(['role' => 'guru', 'status' => 'aktif']);
        Guru::create(['user_id' => $guruUser->id, 'nip' => '123456', 'jenis' => 'formal']);

        $academicYear = AcademicYear::firstOrFail();
        $program = EducationProgram::firstOrFail();
        $subject = Subject::create([
            'nama_mapel' => 'Matematika Dashboard',
            'kode_mapel' => 'MTK-DASH',
            'jenis_mapel' => 'formal',
        ]);
        $classGroup = ClassGroup::create([
            'nama_kelas' => 'X Dashboard',
            'jenis_kelas' => 'formal',
            'academic_year_id' => $academicYear->id,
            'education_program_id' => $program->id,
        ]);
        $schedule = Schedule::create([
            'user_id' => $guruUser->id,
            'class_group_id' => $classGroup->id,
            'subject_id' => $subject->id,
            'academic_year_id' => $academicYear->id,
            'hari' => now()->locale('id')->isoFormat('dddd'),
            'jam_mulai' => '07:15:00',
            'jam_selesai' => '07:55:00',
        ]);
        ScheduleSession::create([
            'schedule_id' => $schedule->id,
            'scheduled_teacher_id' => $guruUser->id,
            'actual_teacher_id' => $guruUser->id,
            'subject_id' => $subject->id,
            'class_group_id' => $classGroup->id,
            'academic_year_id' => $academicYear->id,
            'date' => now()->toDateString(),
            'start_time' => '07:15:00',
            'end_time' => '07:55:00',
            'meeting_no' => 1,
            'status' => 'open',
        ]);

        $response = $this->actingAs($guruUser)->get(route('guru.dashboard'));
        $response->assertOk()
            ->assertSee('Matematika Dashboard')
            ->assertSee('X Dashboard')
            ->assertSee(route('guru.schedule.absen', $schedule), escape: false);

        $response = $this->actingAs($guruUser)->get(route('guru.schedule'));
        $response->assertStatus(200);

        $response = $this->actingAs($guruUser)->get(route('guru.history.index'));
        $response->assertStatus(200);
    }

    /** @test */
    public function non_admin_users_cannot_access_admin_management_pages()
    {
        $karyawan = User::factory()->create(['role' => 'karyawan', 'status' => 'aktif']);

        $response = $this->actingAs($karyawan)->get(route('users.index'));
        $response->assertStatus(403);

        $response = $this->actingAs($karyawan)->get(route('admin.sync.index'));
        $response->assertStatus(403);
    }
}
