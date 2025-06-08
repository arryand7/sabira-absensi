<?php

namespace App\Models;
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    protected $fillable = ['user_id', 'jenis'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
