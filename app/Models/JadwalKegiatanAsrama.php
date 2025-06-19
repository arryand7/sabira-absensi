<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalKegiatanAsrama extends Model
{
    use HasFactory;

    // Nama tabel jika tidak mengikuti konvensi Laravel
    protected $table = 'jadwal_kegiatan_asrama';

    // Field yang boleh diisi mass-assignment
    protected $fillable = [
        'kegiatan_asrama_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'dibuat_oleh',
        // Tambahkan field lain sesuai kebutuhan
    ];

    // Relasi ke absensi
    public function absensi()
    {
        return $this->hasMany(AbsensiAsrama::class);
    }

    public function kegiatanAsrama()
    {
        return $this->belongsTo(KegiatanAsrama::class);
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }
}
