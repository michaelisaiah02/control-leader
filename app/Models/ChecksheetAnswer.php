<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $checksheet_id
 * @property int $question_id
 * @property string $answer
 * @property string|null $problem
 * @property string|null $countermeasure
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Checksheet $checksheet
 * @property-read \App\Models\Question $question
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetAnswer whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ChecksheetAnswer extends Model
{
    use HasFactory;
    protected $connection = 'mysql_control_leader';
    protected $fillable = ['checksheet_id', 'question_id', 'answer', 'problem', 'countermeasure'];

    // Jawaban ini milik satu checksheet
    public function checksheet(): BelongsTo
    {
        return $this->belongsTo(Checksheet::class);
    }

    // Jawaban ini untuk satu pertanyaan
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
