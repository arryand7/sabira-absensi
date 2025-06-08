<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Schedule;
use App\Models\ClassGroup;
use App\Models\Subject;
use App\Models\User;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $guru1 = User::where('email', 'guru1@example.com')->first();
        $guru2 = User::where('email', 'guru2@example.com')->first();

        $kelasAkademik = ClassGroup::where('nama_kelas', '7A')->first();
        $kelasMuadalah = ClassGroup::where('nama_kelas', 'Muadalah 1')->first();

        $mtk = Subject::where('kode_mapel', 'MTK1')->first();
        $tafsir = Subject::where('kode_mapel', 'TAF1')->first();

        Schedule::create([
            'user_id' => $guru1->id,
            'class_group_id' => $kelasAkademik->id,
            'subject_id' => $mtk->id,
            'hari' => 'Senin',
            'jam_mulai' => '08:00',
            'jam_selesai' => '09:30',
        ]);

        Schedule::create([
            'user_id' => $guru2->id,
            'class_group_id' => $kelasMuadalah->id,
            'subject_id' => $tafsir->id,
            'hari' => 'Selasa',
            'jam_mulai' => '10:00',
            'jam_selesai' => '11:30',
        ]);
    }
}
