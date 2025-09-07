<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// app/Models/SchedulePlan.php
/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ScheduleDetail> $details
 * @property-read int|null $details_count
 * @property-read \App\Models\ControlLeaderUser|null $scheduler
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchedulePlan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchedulePlan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchedulePlan query()
 * @mixin \Eloquent
 */
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
