<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\ClassGroup;
use Illuminate\Database\Seeder;

class ClassGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil tahun ajaran yang aktif
        $activeYear = AcademicYear::where('is_active', true)->first();

        if (!$activeYear) {
            $this->command->error('Tahun ajaran aktif tidak ditemukan.');
            return;
        }

        // Daftar kelas
        $muadalah = ['الوسطى-٤', 'الوسطى-٢', 'الوسطى-٣', 'الوسطى-١', 'الأولى-١', 'الأولى-٢', 'الأولى-٣', 'الأولى-٤'];
        $formal = ['XI.1', 'XI.2', 'XI.3', 'XI.4', 'X.1', 'X.2', 'X.3', 'X.4'];

        // Masukkan kelas muadalah
        foreach ($muadalah as $nama) {
            ClassGroup::create([
                'nama_kelas' => $nama,
                'jenis_kelas' => 'muadalah',
                'academic_year_id' => $activeYear->id,
                'wali_kelas_id' => null,
            ]);
        }

        // Masukkan kelas formal
        foreach ($formal as $nama) {
            ClassGroup::create([
                'nama_kelas' => $nama,
                'jenis_kelas' => 'formal',
                'academic_year_id' => $activeYear->id,
                'wali_kelas_id' => null,
            ]);
        }
    }
}
