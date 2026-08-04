<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KaryawanManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_open_and_update_employee_detail_from_browser_routes(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin', 'status' => 'aktif']);
        $employee = User::factory()->create(['role' => 'karyawan', 'status' => 'aktif']);
        $division = Divisi::create(['nama' => 'Kurikulum']);
        $profile = Karyawan::create([
            'user_id' => $employee->id,
            'nama_lengkap' => $employee->name,
            'divisi_id' => $division->id,
            'no_hp' => '08123456789',
            'alamat' => 'Bandung',
        ]);

        $this->actingAs($superAdmin)
            ->get(route('karyawan.index'))
            ->assertOk()
            ->assertSee($employee->name)
            ->assertSee(route('karyawan.show', $profile), false);

        $this->actingAs($superAdmin)
            ->get(route('karyawan.show', $profile))
            ->assertOk()
            ->assertSee('Jadwal Mengajar')
            ->assertSee($employee->email);

        $this->actingAs($superAdmin)
            ->put(route('karyawan.update', $profile), [
                'divisi_id' => $division->id,
                'no_hp' => '08999999999',
                'alamat' => 'Jakarta',
            ])
            ->assertRedirect(route('karyawan.show', $profile));

        $this->assertDatabaseHas('karyawan', [
            'id' => $profile->id,
            'no_hp' => '08999999999',
            'alamat' => 'Jakarta',
        ]);
    }

    public function test_regular_employee_cannot_access_employee_management(): void
    {
        $employee = User::factory()->create(['role' => 'karyawan', 'status' => 'aktif']);

        $this->actingAs($employee)->get(route('karyawan.index'))->assertForbidden();
    }
}
