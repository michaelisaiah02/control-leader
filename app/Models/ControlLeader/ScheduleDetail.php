<?php

namespace App\Models\ControlLeader;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $schedule_plan_id
 * @property int|null $target_leader_id
 * @property string|null $target_operator_id
 * @property string $schedule_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read SchedulePlan $plan
 * @property-read User|null $targetLeader
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduleDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduleDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduleDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduleDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduleDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduleDetail whereScheduleDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduleDetail whereSchedulePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduleDetail whereTargetLeaderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduleDetail whereTargetOperatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduleDetail whereUpdatedAt($value)
 * @property string|null $target_operator_name
 * @property string $scheduled_date
 * @property string|null $started_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduleDetail whereScheduledDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduleDetail whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduleDetail whereTargetOperatorName($value)
 * @property string|null $division
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduleDetail whereDivision($value)
 * @mixin \Eloquent
 */
class ScheduleDetail extends ControlLeaderModel
{
    use HasFactory;

    protected $table = 'schedule_details';

    protected $fillable = [
        'schedule_plan_id',
        'target_leader_id',
        'target_operator_id',
        'target_operator_name',
        'division',
        'scheduled_date',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SchedulePlan::class, 'schedule_plan_id');
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}
