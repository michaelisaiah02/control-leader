<?php
namespace App\Models\ControlLeader;

use App\Models\ControlLeader\ScheduleDetail;
use App\Models\ControlLeader\ControlLeaderModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecksheetDraft extends ControlLeaderModel
{
    protected $table = 'checksheet_drafts';
    protected $casts = ['is_active' => 'bool', 'started_at' => 'datetime', 'last_ping' => 'datetime'];
    public function detail(): BelongsTo
    {
        return $this->belongsTo(ScheduleDetail::class, 'schedule_detail_id');
    }
}
