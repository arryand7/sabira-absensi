<?php

namespace Database\Factories;

use App\Models\AbsensiLokasi;
use Illuminate\Database\Eloquent\Factories\Factory;

class AbsensiLokasiFactory extends Factory
{
    protected $model = AbsensiLokasi::class;

    public function definition(): array
    {
        return [
            'latitude' => -7.3,
            'longitude' => 112.72,
        ];
    }
}
