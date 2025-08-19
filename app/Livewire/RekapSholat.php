<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\AbsensiAsrama;
use App\Models\Student;
use Illuminate\Support\Carbon;

class RekapSholat extends Component
{
    public $bulan;
    public $tahun;

    public function mount($bulan, $tahun)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function render()
    {
        $students = Student::orderBy('nama_lengkap')->get();
        $tanggal = collect(range(1, Carbon::create($this->tahun, $this->bulan)->daysInMonth));

        $sholatList = \App\Models\KegiatanAsrama::where('jenis', 'sholat')
            ->where('berulang', true)
            ->orderBy('id')
            ->get();

        // Ambil semua jadwal & absensi sekaligus
        $tanggalAwal = Carbon::create($this->tahun, $this->bulan, 1)->toDateString();
        $tanggalAkhir = Carbon::create($this->tahun, $this->bulan, $tanggal->count())->toDateString();

        $jadwalList = \App\Models\JadwalKegiatanAsrama::whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir])
            ->whereIn('kegiatan_asrama_id', $sholatList->pluck('id'))
            ->get();

        $absensiList = AbsensiAsrama::whereIn('jadwal_kegiatan_asrama_id', $jadwalList->pluck('id'))
            ->get();

        $jadwalByDateSholat = $jadwalList->keyBy(function ($item) {
            return $item->kegiatan_asrama_id . '|' . $item->tanggal;
        });

        $absenByStudentJadwal = $absensiList->keyBy(function ($item) {
            return $item->student_id . '|' . $item->jadwal_kegiatan_asrama_id;
        });

        $data = [];

        foreach ($students as $student) {
            foreach ($sholatList as $sholat) {
                $data[$student->id][$sholat->id] = [];

                foreach ($tanggal as $day) {
                    $tanggalLengkap = Carbon::create($this->tahun, $this->bulan, $day)->toDateString();
                    $jadwalKey = $sholat->id . '|' . $tanggalLengkap;

                    if (isset($jadwalByDateSholat[$jadwalKey])) {
                        $jadwalId = $jadwalByDateSholat[$jadwalKey]->id;
                        $absenKey = $student->id . '|' . $jadwalId;

                        // Sudah ada jadwal
                        $data[$student->id][$sholat->id][$day] =
                            $absenByStudentJadwal[$absenKey]->status ?? 'alpa';
                    } else {
                        // Jadwal belum dibuat
                        $data[$student->id][$sholat->id][$day] = '-';
                    }
                }
            }
        }

        return view('livewire.rekap-sholat', [
            'students' => $students,
            'tanggal' => $tanggal,
            'sholatList' => $sholatList,
            'data' => $data,
        ]);
    }
}
