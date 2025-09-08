<?php

namespace App\Models\ControlLeader;

use Illuminate\Database\Eloquent\Model;
use App\Models\ControlLeader\ScheduleDetail;
use App\Models\ControlLeader\ChecksheetAnswer;
use App\Models\ControlLeader\ControlLeaderModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
 * @mixin \Eloquent
 */
class Checksheet extends ControlLeaderModel
{
    use HasFactory;
    protected $fillable = [
        'schedule_id',
        'type',
        'stopwatch_duration',
        'part_a_answer_1',
        'part_a_answer_2',
        'part_a_answer_3',
        'part_a_answer_4',
    ];

    // Checksheet ini milik satu jadwal (Detail)
    public function scheduleDetail(): BelongsTo
    {
        return $this->belongsTo(ScheduleDetail::class);
    }

    // Checksheet ini punya banyak jawaban (Bagian B)
    public function answers(): HasMany
    {
        return $this->hasMany(ChecksheetAnswer::class);
    }
}
