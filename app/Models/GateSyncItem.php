<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GateSyncItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'gate_snapshot' => 'array',
        'local_snapshot' => 'array',
        'field_differences' => 'array',
    ];

    public function syncRun(): BelongsTo
    {
        return $this->belongsTo(GateSyncRun::class, 'gate_sync_run_id');
    }

    public function localUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'local_user_id');
    }
}
