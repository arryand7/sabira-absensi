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
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->get()
            ->keyBy(fn($a) => Carbon::parse($a->created_at)->toDateString());

        $absensiMap = [];

        for ($i = 0; $i < $daysInMonth; $i++) {
            $tanggal = $startOfMonth->copy()->addDays($i)->toDateString();

            if ($tanggal > $today) {
                $absensiMap[$tanggal] = '-';
            } elseif ($absens->has($tanggal)) {
                $absensiMap[$tanggal] = $absens[$tanggal]->status;
            } else {
                $absensiMap[$tanggal] = 'Tidak Hadir';
            }
        }

        return view('livewire.kalender-absensi', compact('absensiMap'));
    }
}
