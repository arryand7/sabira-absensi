<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KegiatanAsrama extends Model
{
    use HasFactory;

    protected $table = 'kegiatan_asrama';

    protected $fillable = [
        'nama',
        'jenis',
        'berulang',
    ];

    public function jadwal()
    {
        return $this->hasMany(JadwalKegiatanAsrama::class, 'kegiatan_asrama_id');
    }
}
