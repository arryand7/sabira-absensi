<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// File: app/Models/AbsensiAsrama.php

class AbsensiAsrama extends Model
{
    use HasFactory;

    protected $table = 'absensi_asrama';

    // Tambahkan ini:
    protected $fillable = [
        'student_id',
        'status',
        'jadwal_kegiatan_asrama_id',
        'created_at',
        'updated_at',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function kegiatan()
    {
        return $this->belongsTo(JadwalKegiatanAsrama::class, 'jadwal_kegiatan_asrama_id');
    }
}

