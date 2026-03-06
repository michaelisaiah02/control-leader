<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ConsistencyProblem extends Model
{
    protected $fillable = [
        'user_id',
        'inferior_id',
        'role_type',
        'remark',
        'schedule_detail_id',
        'problem',
        'countermeasure',
        'status',
        'due_date',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'employeeID');
    }

    public function inferior()
    {
        return $this->belongsTo(User::class, 'inferior_id', 'employeeID');
    }

    public function scheduleDetail()
    {
        return $this->belongsTo(ScheduleDetail::class, 'schedule_detail_id');
    }

    public function getIsDueDateChangedAttribute()
    {
        // Hitung default H+2 dari tanggal create
        $defaultDueDate = Carbon::parse($this->created_at)->addDays(2)->format('Y-m-d');
        $currentDueDate = Carbon::parse($this->due_date)->format('Y-m-d');

        return $defaultDueDate !== $currentDueDate;
    }
}
