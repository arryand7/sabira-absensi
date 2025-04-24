<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TandaiTidakHadir extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:tandai-tidak-hadir';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hariIni = Carbon::today();

        $karyawanIds = User::where('role', 'karyawan')->pluck('id');

        foreach ($karyawanIds as $id) {
            $sudahAbsen = AbsensiKaryawan::where('user_id', $id)
                ->whereDate('created_at', $hariIni)
                ->exists();

            if (!$sudahAbsen) {
                AbsensiKaryawan::create([
                    'user_id' => $id,
                    'waktu_absen' => now(),
                    'check_in' => null,
                    'check_out' => null,
                    'status' => 'Tidak Hadir',
                ]);
            }
        }

        $this->info('Semua karyawan yang tidak absen ditandai sebagai Tidak Hadir.');
    }

}
