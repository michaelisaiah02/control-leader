<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecksheetDraft extends Model
{
    protected $table = 'checksheet_drafts';

    protected $fillable = [
        'user_id',
        'schedule_plan_id',
        'phase',
        'session_id',
        'started_at',
        'payload',
        'is_active',
        'last_ping',
    ];

    protected $casts = ['is_active' => 'bool', 'started_at' => 'datetime', 'last_ping' => 'datetime'];

    public function detail(): BelongsTo
    {
        return $this->belongsTo(ScheduleDetail::class, 'schedule_detail_id');
    }
}
