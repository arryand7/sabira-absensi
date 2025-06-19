<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isGuru()
    {
        return $this->role === 'guru';
    }

    public function isKaryawan()
    {
        return $this->role === 'karyawan';
    }

    public function karyawan()
    {
        return $this->hasOne(Karyawan::class);
    }

    public function absensis()
    {
        return $this->hasMany(\App\Models\AbsensiKaryawan::class, 'user_id');
    }

    public function guru()
    {
        return $this->hasOne(Guru::class);
    }

    public function isAktif()
    {
        return $this->status === 'aktif';
    }



}
