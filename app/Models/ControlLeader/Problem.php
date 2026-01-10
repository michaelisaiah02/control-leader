<?php

namespace App\Models\ControlLeader;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Problem extends Model
{
    use HasFactory;
    protected $connection = 'mysql_control_leader';
    protected $table = 'problems';
    protected $fillable = ['checksheet_answer_id', 'leader_name', 'operator_name', 'operator_id', 'problem', 'countermeasure', 'status', 'due_date'];

    public function answers()
    {
        return $this->hasMany(ChecksheetAnswer::class, 'checksheet_id');
    }
}
