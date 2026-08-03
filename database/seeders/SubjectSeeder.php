<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Subject::insert([
            ['nama_mapel' => 'Matematika', 'kode_mapel' => 'MTK1', 'jenis_mapel' => 'formal'],
            ['nama_mapel' => 'Tafsir', 'kode_mapel' => 'TAF1', 'jenis_mapel' => 'muadalah'],
        ]);
    }
}
