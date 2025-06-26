<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AbsensiKaryawan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AbsensiCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => 'karyawan',
        ]);
    }

    /** @test */
    public function user_can_checkout_successfully_after_checkin()
    {
        // Simulasi sudah check-in
        AbsensiKaryawan::create([
            'user_id' => $this->user->id,
            'latitude' => -7.31,
            'longitude' => 112.72,
            'waktu_absen' => now(),
            'check_in' => now()->format('H:i:s'),
        ]);

        $response = $this->actingAs($this->user)->post('/absensi/checkout', [
            'latitude' => -7.31,
            'longitude' => 112.72,
        ]);

        $response->assertSessionHas('success', 'Berhasil Check-Out!');

        $this->assertDatabaseHas('absensi_karyawans', [
            'user_id' => $this->user->id,
        ]);

        $this->assertNotNull(AbsensiKaryawan::first()->check_out);
    }

    /** @test */
    public function checkout_fails_if_not_checked_in()
    {
        $response = $this->actingAs($this->user)->post('/absensi/checkout', [
            'latitude' => -7.31,
            'longitude' => 112.72,
        ]);

        $response->assertSessionHas('error', 'Gagal Check-Out: Anda belum melakukan Check-In hari ini.');
    }

    /** @test */
    public function checkout_fails_if_already_checked_out()
    {
        AbsensiKaryawan::create([
            'user_id' => $this->user->id,
            'latitude' => -7.31,
            'longitude' => 112.72,
            'waktu_absen' => now(),
            'check_in' => now()->format('H:i:s'),
            'check_out' => now()->format('H:i:s'),
        ]);

        $response = $this->actingAs($this->user)->post('/absensi/checkout', [
            'latitude' => -7.31,
            'longitude' => 112.72,
        ]);

        $response->assertSessionHas('error', 'Anda sudah melakukan Check-Out sebelumnya.');
    }

    /** @test */
    public function checkout_fails_if_location_is_missing()
    {
        // Untuk skenario ini sebenarnya belum ditangani oleh controller
        // Jadi perlu validasi request location seperti checkin
        $absen = AbsensiKaryawan::create([
            'user_id' => $this->user->id,
            'latitude' => -7.31,
            'longitude' => 112.72,
            'waktu_absen' => now(),
            'check_in' => now()->format('H:i:s'),
        ]);

        $response = $this->actingAs($this->user)->post('/absensi/checkout', []);

        // Misalnya kamu menambahkan validasi di controller: 'latitude' dan 'longitude' wajib
        $response->assertSessionHasErrors(['latitude', 'longitude']);
    }
}
