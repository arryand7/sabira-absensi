<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\AbsensiKaryawan;
use Carbon\Carbon;

class KalenderAbsensi extends Component
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
        $user = Auth::user();

        $startOfMonth = Carbon::createFromDate($this->tahun, $this->bulan, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $today = Carbon::now()->toDateString();
        $now = Carbon::now();

        $daysInMonth = $startOfMonth->diffInDays($endOfMonth) + 1;

        $absens = AbsensiKaryawan::where('user_id', $user->id)
            ->whereBetween('waktu_absen', [$startOfMonth, $endOfMonth])
            ->get()
            ->keyBy(fn($a) => Carbon::parse($a->waktu_absen)->toDateString());

        $absensiMap = [];

        for ($i = 0; $i < $daysInMonth; $i++) {
            $tanggal = $startOfMonth->copy()->addDays($i)->toDateString();

            if ($tanggal > $today) {
                $absensiMap[$tanggal] = [
                    'status' => '-',
                    'check_in' => null,
                    'check_out' => null,
                ];
            } elseif ($absens->has($tanggal)) {
                $absen = $absens[$tanggal];
                $absensiMap[$tanggal] = [
                    'status' => $absen->status,
                    'check_in' => $absen->check_in,
                    'check_out' => $absen->check_out,
                ];
            } else {
                $absensiMap[$tanggal] = [
                    'status' => 'Tidak Hadir',
                    'check_in' => null,
                    'check_out' => null,
                ];
            }
        }

        return view('livewire.kalender-absensi', compact('absensiMap'));
    }
}
