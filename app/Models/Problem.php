<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ControlLeader\ChecksheetAnswer;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Problem extends Model
{
    use HasFactory;
    protected $table = 'problems';
    protected $fillable = ['checksheet_answer_id', 'leader_name', 'operator_name', 'operator_id', 'problem', 'countermeasure', 'status', 'due_date'];

    public function answers()
    {
        return $this->hasMany(ChecksheetAnswer::class, 'checksheet_id');
    }
}
