<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AbsensiKaryawan;
use App\Models\User;

class AbsensiKaryawanFactory extends Factory
{
    protected $model = AbsensiKaryawan::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'latitude' => -7.31,
            'longitude' => 112.72,
            'waktu_absen' => now(),
            'check_in' => now()->format('H:i:s'),
            'check_out' => null,
            'status' => 'Hadir',
        ];
    }
}
