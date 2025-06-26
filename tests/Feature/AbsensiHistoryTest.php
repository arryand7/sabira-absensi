<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\AbsensiKaryawan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class AbsensiHistoryTest extends TestCase
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
    public function it_displays_current_month_history_by_default()
    {
        Carbon::setTestNow(Carbon::create(2025, 6, 17));

        AbsensiKaryawan::factory()->create([
            'user_id' => $this->user->id,
            'waktu_absen' => now(),
            'status' => 'Hadir',
        ]);

        $response = $this->actingAs($this->user)->get('/history-absensi');

        $response->dump(); // DEBUG: Tampilkan seluruh konten response

        $response->assertStatus(200);
        $response->assertSee('Juni');
        $response->assertSee('Hadir');

    }

    /** @test */
    public function it_filters_history_by_selected_month_and_year()
    {
        Carbon::setTestNow(Carbon::create(2025, 6, 1));

        AbsensiKaryawan::factory()->create([
            'user_id' => $this->user->id,
            'waktu_absen' => Carbon::create(2025, 5, 3, 8), // bulan Mei
            'status' => 'Terlambat',
        ]);

        $response = $this->actingAs($this->user)->get('/history-absensi?bulan=5&tahun=2025');

        $response->assertStatus(200);
        $response->assertSee('Mei');
        $response->assertSee('Terlambat');
    }
}
