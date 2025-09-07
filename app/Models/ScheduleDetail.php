<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// app/Models/ScheduleDetail.php
/**
 * @property-read \App\Models\SchedulePlan|null $plan
 * @property-read \App\Models\ControlLeaderUser|null $targetLeader
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduleDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduleDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduleDetail query()
 * @mixin \Eloquent
 */
class ScheduleDetail extends Model
{
    use HasFactory;
    protected $connection = 'mysql_control_leader';
    protected $fillable = ['schedule_plan_id', 'target_leader_id', 'target_operator_id', 'schedule_date'];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SchedulePlan::class, 'schedule_plan_id');
    }
    public function targetLeader(): BelongsTo
    {
        return $this->belongsTo(ControlLeaderUser::class, 'target_leader_id');
    }
}
