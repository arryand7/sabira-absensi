<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KegiatanAsrama;

class KegiatanAsramaSeeder extends Seeder
{
    public function run()
    {
        $sholats = ['tahajjud', 'subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'];

        foreach ($sholats as $nama) {
            KegiatanAsrama::updateOrCreate([
                'nama' => ucfirst($nama),
            ], [
                'jenis' => 'sholat',
                'berulang' => true,
            ]);
        }
    }
}
