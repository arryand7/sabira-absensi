<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Divisi extends Model
{
    use HasFactory;

    protected $fillable = ['nama'];

    // Relasi: 1 divisi punya banyak karyawan
    public function karyawans()
    {
        return $this->hasMany(Karyawan::class);
    }
}
