<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Checksheet extends Model
{
    use HasFactory;

    protected $table = 'checksheets';
    protected $appends = ['remark'];

    protected $fillable = [
        'schedule_plan_id',
        'stopwatch_duration',
        'score',
        'scheduled_target',
        'phase',
        'shift',
        'target',
        'division',
        'attendance',
        'condition',
        'replacement',
        'replacement_of_id',
        'replacement_name',
        'replacement_division',
        'replacement_condition',
    ];

    public function getRemarkAttribute(): string
    {
        $createdAt = Carbon::parse($this->created_at);
        $schedule  = $this->getSchedule();

        if ($createdAt->diffInDays(now()) >= 7) {
            return 'Miss';
        }

        if ($createdAt->gt($schedule)) {
            return 'Late';
        }

        if ($createdAt->lt($schedule) && auth()->guard('web_control_leader')->user()->role === 'supervisor') {
            return 'Advanced';
        }

        return "On Time";
    }

    private function getSchedule(): Carbon
    {
        return Carbon::parse($this->created_at->format('Y-m-d'));
    }

    public function answers()
    {
        return $this->hasMany(ChecksheetAnswer::class, 'checksheet_id');
    }

    public function detail()
    {
        return $this->belongsTo(ScheduleDetail::class, 'schedule_detail_id');
    }
}
