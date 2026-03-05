<?php

namespace App\Models;

use Carbon\Carbon;
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
        return $this->belongsTo(Checksheet::class);
    }

    protected static function booted()
    {
        // Event ini otomatis ke-trigger SATU KALI setelah record baru dibuat
        static::created(function ($answer) {

            // Validasi: Cuma jalan kalau problem & countermeasure punya isi (bukan null/kosong)
            if (! empty($answer->problem) && ! empty($answer->countermeasure)) {

                // Load relasi checksheet buat dapetin data 'target'
                $checksheet = $answer->checksheet;

                Problem::create([
                    'checksheet_answer_id' => $answer->id,

                    'user_id' => auth()->user()->employeeID,
                    'inferior_id' => $checksheet->target,

                    'problem' => $answer->problem,
                    'countermeasure' => $answer->countermeasure,
                    'status' => 'open',

                    // Pake Carbon buat nambahin 2 hari (H+2) dari waktu checksheet answer dibuat
                    'due_date' => Carbon::parse($answer->created_at)->addDays(2),
                ]);
            }
        });
    }
}
