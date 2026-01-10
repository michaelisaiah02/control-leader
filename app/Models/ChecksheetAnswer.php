<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecksheetAnswer extends Model
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

    public function problem()
    {
        return $this->belongsTo(Problem::class, 'checksheet_answer_id');
    }
}
