<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
