<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $id_num
 * @property \Illuminate\Support\Carbon $calibration_date
 * @property string|null $calibrator_equipment
 * @property string|null $param_01
 * @property string|null $param_02
 * @property string|null $param_03
 * @property string|null $param_04
 * @property string|null $param_05
 * @property string|null $param_06
 * @property string|null $param_07
 * @property string|null $param_08
 * @property string|null $param_09
 * @property string|null $param_10
 * @property string $judgement
 * @property string $created_by
 * @property string|null $certificate
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\MasterList|null $calibrator
 * @property-read \App\Models\User $creator
 * @property-read \App\Models\MasterList $masterList
 *
 * @method static \Database\Factories\ResultFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Result newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Result newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Result query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Result whereCalibrationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Result whereCalibratorEquipment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Result whereCertificate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Result whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Result whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Result whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Result whereIdNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Result whereJudgement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Result whereParam01($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Result whereParam02($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Result whereParam03($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Result whereParam04($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Result whereParam05($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Result whereParam06($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Result whereParam07($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Result whereParam08($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Result whereParam09($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Result whereParam10($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Result whereUpdatedAt($value)
 *
 * @property int $is_approved
 * @property int $is_checked
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Result whereIsApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Result whereIsChecked($value)
 *
 * @mixin \Eloquent
 */
class Result extends Model
{
    /** @use HasFactory<\Database\Factories\ResultFactory> */
    use HasFactory;

    protected $fillable = [
        'id_num',
        'calibration_date',
        'calibrator_equipment',
        'param_01',
        'param_02',
        'param_03',
        'param_04',
        'param_05',
        'param_06',
        'param_07',
        'param_08',
        'param_09',
        'param_10',
        'judgement',
        'created_by',
        'certificate',
        'is_approved',
        'is_checked',
    ];

    protected $casts = [
        'calibration_date' => 'date',
    ];

    public function masterList()
    {
        return $this->belongsTo(\App\Models\MasterList::class, 'id_num', 'id_num');
    }

    public function calibrator()
    {
        return $this->belongsTo(\App\Models\MasterList::class, 'calibrator_equipment', 'id_num');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by', 'employeeID');
    }

    protected static function booted()
    {
        static::deleting(function ($result) {
            if ($result->certificate && Storage::exists($result->certificate)) {
                Storage::delete($result->certificate);
            }
        });
    }
}
