<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AbsensiKaryawan;
use App\Models\AbsensiLokasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Carbon\Carbon;

class AbsensiControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkin_berhasil()
    {
        // Setup
        $user = User::factory()->create(['role' => 'karyawan']);
        $this->actingAs($user);

        AbsensiLokasi::factory()->create([
            'latitude' => -7.31,
            'longitude' => 112.72,
        ]);

        // Aksi
        $response = $this->post('/checkin', [
            'latitude' => -7.31,
            'longitude' => 112.72,
        ]);

        // Verifikasi
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('absensi_karyawans', [
            'user_id' => $user->id,
        ]);
    }

    public function test_checkin_gagal_sudah_checkin()
    {
        $user = User::factory()->create(['role' => 'karyawan']);
        $this->actingAs($user);

        AbsensiKaryawan::factory()->create([
            'user_id' => $user->id,
            'created_at' => now(),
        ]);

        $response = $this->post('/checkin', [
            'latitude' => -7.31,
            'longitude' => 112.72,
        ]);

        $response->assertSessionHas('error', 'Anda sudah melakukan Check-In hari ini.');
    }

    public function test_checkin_gagal_karena_lokasi_tidak_sesuai()
    {
        $user = User::factory()->create(['role' => 'karyawan']);
        $this->actingAs($user);

        AbsensiLokasi::factory()->create([
            'latitude' => -7.31,
            'longitude' => 112.72,
        ]);

        $response = $this->post('/checkin', [
            'latitude' => -8.0, // jauh banget
            'longitude' => 110.0,
        ]);

        $response->assertSessionHas('error', 'Gagal Check-In: Lokasi terlalu jauh dari sekolah.');
    }
}
