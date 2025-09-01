<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Checksheet extends Model
{
    use HasFactory;
    protected $connection = 'mysql_control_leader';
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
