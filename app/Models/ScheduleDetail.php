<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// app/Models/ScheduleDetail.php
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
