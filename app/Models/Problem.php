<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Problem extends Model
{
    use HasFactory;

    protected $table = 'problems';

    protected $fillable = [
        'checksheet_answer_id',
        'user_id',
        'inferior_id',
        'problem',
        'countermeasure',
        'status',
        'due_date',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function checksheetAnswer()
    {
        return $this->belongsTo(ChecksheetAnswer::class, 'checksheet_answer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'employeeID');
    }

    public function inferior()
    {
        return $this->belongsTo(User::class, 'inferior_id', 'employeeID');
    }

    public function getIsDueDateChangedAttribute()
    {
        // Hitung default H+2 (format Y-m-d karena tipe data di DB lo date, bukan timestamp)
        $defaultDueDate = Carbon::parse($this->created_at)->addDays(2)->format('Y-m-d');
        $currentDueDate = Carbon::parse($this->due_date)->format('Y-m-d');

        // Kalau beda, berarti udah pernah diedit = true
        return $defaultDueDate !== $currentDueDate;
    }
}
