<?php

namespace App\Models\ControlLeader;

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
 *
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
 *
 * @property string $question_text
 * @property string $answer_type
 * @property array<array-key, mixed>|null $choices
 * @property string|null $answer_value
 * @property string|null $answer_label
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetAnswer whereAnswerLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetAnswer whereAnswerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetAnswer whereAnswerValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetAnswer whereChoices($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetAnswer whereQuestionText($value)
 *
 * @mixin \Eloquent
 */
class ChecksheetAnswer extends ControlLeaderModel
{
    protected $table = 'checksheet_answers';

    protected $fillable = [
        'checksheet_id',
        'question_text',
        'choices',
        'answer_value',
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
