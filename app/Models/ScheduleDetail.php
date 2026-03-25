<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleDetail extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $table = 'schedule_details';

    protected $fillable = [
        'schedule_plan_id',
        'target_user_id',
        'scheduled_date',
        'shift',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SchedulePlan::class, 'schedule_plan_id');
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id', 'employeeID');
    }
}
