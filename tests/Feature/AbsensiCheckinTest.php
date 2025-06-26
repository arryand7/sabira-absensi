<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\AbsensiKaryawan;
use App\Models\AbsensiLokasi;
use Carbon\Carbon;

class AbsensiCheckinTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat user karyawan
        $this->user = User::factory()->create([
            'role' => 'karyawan',
        ]);

        // Set lokasi sekolah
        AbsensiLokasi::create([
            'latitude' => -7.3108238,
            'longitude' => 112.7292373,
        ]);
    }

    /** @test */
    public function user_dapat_check_in_sesuai_radius_dan_waktu()
    {
        Carbon::setTestNow(Carbon::createFromTime(7, 15)); // sebelum 07:30

        $response = $this->actingAs($this->user)
            ->post('/absensi/checkin', [
                'latitude' => -7.310820,
                'longitude' => 112.729230,
            ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseCount('absensi_karyawans', 1);
        $this->assertEquals('Hadir', AbsensiKaryawan::first()->status);
    }

    /** @test */
    public function gagal_checkin_jika_sudah_checkin()
    {
        Carbon::setTestNow(Carbon::createFromTime(7, 10));

        // Simulasi check-in pertama
        AbsensiKaryawan::create([
            'user_id' => $this->user->id,
            'latitude' => -7.310820,
            'longitude' => 112.729230,
            'waktu_absen' => now(),
            'check_in' => now()->format('H:i:s'),
            'status' => 'Hadir',
        ]);

        $response = $this->actingAs($this->user)
            ->post('/absensi/checkin', [
                'latitude' => -7.310820,
                'longitude' => 112.729230,
            ]);

        $response->assertSessionHas('error', 'Anda sudah melakukan Check-In hari ini.');
        $this->assertDatabaseCount('absensi_karyawans', 1); // tidak nambah
    }

    /** @test */
    public function Checkin_gagal_karena_lokasi_terlalu_jauh()
    {
        Carbon::setTestNow(Carbon::createFromTime(7, 10));

        $response = $this->actingAs($this->user)
            ->post('/absensi/checkin', [
                'latitude' => -7.320000, // jauh
                'longitude' => 112.739000,
            ]);

        $response->assertSessionHas('error', 'Gagal Check-In: Lokasi terlalu jauh dari sekolah.');
        $this->assertDatabaseCount('absensi_karyawans', 0);
    }

    /** @test */
    public function tidak_bisa_checkin_diluar_jam_kerja()
    {
        Carbon::setTestNow(Carbon::createFromTime(16, 1)); // lewat 16:00

        $response = $this->actingAs($this->user)
            ->post('/absensi/checkin', [
                'latitude' => -7.310820,
                'longitude' => 112.729230,
            ]);

        $response->assertSessionHas('error', 'Absen Gagal: Sudah melewati jam absen.');
        $this->assertDatabaseCount('absensi_karyawans', 0);
    }

    /** @test */
    public function gagal_checkin_jika_izin_lokasi_mati()
    {
        $response = $this->actingAs($this->user)
            ->post('/absensi/checkin', []); // tidak kirim latitude/longitude

        $response->assertSessionHasErrors(['latitude', 'longitude']);
        $this->assertDatabaseCount('absensi_karyawans', 0);
    }
}
