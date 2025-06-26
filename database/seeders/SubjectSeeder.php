<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Subject;

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
