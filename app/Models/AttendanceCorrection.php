<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrection extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'before_payload' => 'array',
        'proposed_payload' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(ScheduleSession::class, 'schedule_session_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
