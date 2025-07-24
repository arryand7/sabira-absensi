<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiKaryawan extends Model
{
    use HasFactory;

    protected $table = 'absensi_karyawans';

    protected $fillable = [
        'user_id',
        'latitude',
        'longitude',
        'waktu_absen',
        'check_in',
        'check_out',
        'status',
        'device_hash'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
