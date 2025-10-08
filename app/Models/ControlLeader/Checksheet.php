<?php

namespace App\Models\ControlLeader;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $schedule_detail_id
 * @property string $type
 * @property int $stopwatch_duration Durasi dalam detik
 * @property string|null $part_a_answer_1
 * @property string|null $part_a_answer_2
 * @property string|null $part_a_answer_3
 * @property string|null $part_a_answer_4
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ChecksheetAnswer> $answers
 * @property-read int|null $answers_count
 * @property-read ScheduleDetail $scheduleDetail
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checksheet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checksheet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checksheet query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checksheet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checksheet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checksheet wherePartAAnswer1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checksheet wherePartAAnswer2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checksheet wherePartAAnswer3($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checksheet wherePartAAnswer4($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checksheet whereScheduleDetailId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checksheet whereStopwatchDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checksheet whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checksheet whereUpdatedAt($value)
 * @property int $schedule_plan_id
 * @property string $phase
 * @property-read ScheduleDetail|null $detail
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checksheet wherePhase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checksheet whereSchedulePlanId($value)
 * @property string $shift
 * @property string $target Format: id - nama
 * @property string $division
 * @property string $attendance
 * @property string|null $condition
 * @property string|null $replacement_name
 * @property string|null $replacement_division
 * @property string|null $replacement_condition
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checksheet whereAttendance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checksheet whereCondition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checksheet whereDivision($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checksheet whereReplacementCondition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checksheet whereReplacementDivision($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checksheet whereReplacementName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checksheet whereShift($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checksheet whereTarget($value)
 * @property int $replacement false = scheduled/original; true = replacement (yang dinilai)
 * @property int|null $replacement_of_id ID checksheet parent (scheduled). Null bila scheduled/hadir
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checksheet whereReplacement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checksheet whereReplacementOfId($value)
 * @mixin \Eloquent
 */
class Checksheet extends ControlLeaderModel
{
    use HasFactory;

    protected $table = 'checksheets';

    protected $fillable = [
        'schedule_plan_id',
        'stopwatch_duration',
        'phase',
        'shift',
        'target',
        'division',
        'attendance',
        'condition',
        'replacement',
        'replacement_of_id',
        'replacement_name',
        'replacement_division',
        'replacement_condition',
    ];

    public function answers()
    {
        return $this->hasMany(ChecksheetAnswer::class, 'checksheet_id');
    }

    public function detail()
    {
        return $this->belongsTo(ScheduleDetail::class, 'schedule_detail_id');
    }
}
