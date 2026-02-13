<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ScheduleDetail extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $table = 'schedule_details';

    protected $fillable = [
        'schedule_plan_id',
        'target_user_id',
        'division',
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
