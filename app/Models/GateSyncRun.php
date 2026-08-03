<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GateSyncRun extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'summary_counts' => 'array',
        'started_at' => 'datetime',
        'previewed_at' => 'datetime',
        'applied_at' => 'datetime',
        'reported_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(GateSyncItem::class);
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }
}
