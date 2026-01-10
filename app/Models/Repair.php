<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $id_num
 * @property \Illuminate\Support\Carbon $problem_date
 * @property \Illuminate\Support\Carbon $repair_date
 * @property string $problem
 * @property string $countermeasure
 * @property string $pic_repair
 * @property string $judgement
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\MasterList $masterList
 *
 * @method static \Database\Factories\RepairFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repair newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repair newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repair query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repair whereCountermeasure($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repair whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repair whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repair whereIdNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repair whereJudgement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repair wherePicRepair($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repair whereProblem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repair whereProblemDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repair whereRepairDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repair whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Repair extends Model
{
    /** @use HasFactory<\Database\Factories\RepairFactory> */
    use HasFactory;

    protected $fillable = [
        'id_num',
        'problem_date',
        'repair_date',
        'problem',
        'countermeasure',
        'pic_repair',
        'judgement',
    ];

    protected $casts = [
        'problem_date' => 'date',
        'repair_date' => 'date',
    ];

    public function masterList()
    {
        return $this->belongsTo(MasterList::class, 'id_num', 'id_num');
    }
}
