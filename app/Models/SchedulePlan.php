<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// app/Models/SchedulePlan.php
class SchedulePlan extends Model
{
    use HasFactory;
    protected $connection = 'mysql_control_leader';
    protected $fillable = ['scheduler_id', 'month', 'year', 'type'];

    public function scheduler(): BelongsTo
    {
        return $this->belongsTo(ControlLeaderUser::class, 'scheduler_id');
    }
    public function details(): HasMany
    {
        return $this->hasMany(ScheduleDetail::class);
    }
}
