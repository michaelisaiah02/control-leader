<?php

namespace App\Models\ControlLeader;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $schedule_plan_id
 * @property string $phase
 * @property string|null $session_id
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property string|null $payload
 * @property \Illuminate\Support\Carbon|null $last_ping
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read ScheduleDetail|null $detail
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetDraft newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetDraft newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetDraft query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetDraft whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetDraft whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetDraft whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetDraft whereLastPing($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetDraft wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetDraft wherePhase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetDraft whereSchedulePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetDraft whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetDraft whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetDraft whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetDraft whereUserId($value)
 * @mixin \Eloquent
 */
class ChecksheetDraft extends ControlLeaderModel
{
    protected $table = 'checksheet_drafts';

    protected $casts = ['is_active' => 'bool', 'started_at' => 'datetime', 'last_ping' => 'datetime'];

    public function detail(): BelongsTo
    {
        return $this->belongsTo(ScheduleDetail::class, 'schedule_detail_id');
    }
}
