<?php

namespace App\Models\ControlLeader;

use App\Models\ControlLeader\Question;
use App\Models\ControlLeader\Checksheet;
use App\Models\ControlLeader\ControlLeaderModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
/**
 * @property int $id
 * @property int $checksheet_id
 * @property int $question_id
 * @property string $answer
 * @property string $question_text_snapshot
 * @property string|null $question_options_snapshot
 * @property string|null $problem
 * @property string|null $countermeasure
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Checksheet $checksheet
 * @property-read Question $question
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetAnswer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetAnswer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetAnswer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetAnswer whereAnswer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetAnswer whereChecksheetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetAnswer whereCountermeasure($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetAnswer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetAnswer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetAnswer whereProblem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetAnswer whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetAnswer whereQuestionOptionsSnapshot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetAnswer whereQuestionTextSnapshot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetAnswer whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ChecksheetAnswer extends ControlLeaderModel
{
    protected $table = 'checksheet_answers';

    protected $fillable = [
        'checksheet_id',
        'question_id',
        'question_text',
        'answer_type',
        'choices',
        'answer_value',
        'answer_label',
        'problem',
        'countermeasure',
    ];

    protected $casts = [
        'choices' => 'array',
    ];

    public function checksheet()
    {
        return $this->belongsTo(Checksheet::class, 'checksheet_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }
}
