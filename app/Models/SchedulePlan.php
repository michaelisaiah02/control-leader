<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchedulePlan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $fillable = ['scheduler_id', 'month', 'year', 'type'];

    public function scheduler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scheduler_id', 'employeeID');
    }

    public function details(): HasMany
    {
        return $this->hasMany(ScheduleDetail::class);
    }
}
