<?php

namespace App\Models\ControlLeader;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $scheduler_id
 * @property int $month
 * @property int $year
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ScheduleDetail> $details
 * @property-read int|null $details_count
 * @property-read User $scheduler
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchedulePlan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchedulePlan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchedulePlan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchedulePlan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchedulePlan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchedulePlan whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchedulePlan whereSchedulerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchedulePlan whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchedulePlan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchedulePlan whereYear($value)
 * @mixin \Eloquent
 */
class SchedulePlan extends ControlLeaderModel
{
    use HasFactory;

    protected $fillable = ['scheduler_id', 'month', 'year', 'type'];

    public function scheduler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scheduler_id', 'employeeID');
    }

    public function details(): HasMany
    {
        return $this->hasMany(ScheduleDetail::class);
    }
}
