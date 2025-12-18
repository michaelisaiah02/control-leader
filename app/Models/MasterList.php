<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $type_id
 * @property string $id_num
 * @property string $sn_num
 * @property string $capacity
 * @property string $accuracy
 * @property int|null $unit_id
 * @property string $brand
 * @property string $calibration_type
 * @property \Illuminate\Support\Carbon $first_used
 * @property string $rank
 * @property int $calibration_freq
 * @property string $acceptance_criteria
 * @property string $pic
 * @property string $location
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Equipment $equipment
 * @property-read \App\Models\Result|null $latestResult
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Repair> $repairs
 * @property-read int|null $repairs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Result> $results
 * @property-read int|null $results_count
 * @property-read \App\Models\Standard|null $standard
 * @property-read \App\Models\Unit|null $unit
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterList newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterList newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterList query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterList whereAcceptanceCriteria($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterList whereAccuracy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterList whereBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterList whereCalibrationFreq($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterList whereCalibrationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterList whereCapacity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterList whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterList whereFirstUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterList whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterList whereIdNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterList whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterList wherePic($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterList whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterList whereSnNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterList whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterList whereUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterList whereUpdatedAt($value)
 *
 * @property-read mixed $status
 *
 * @mixin \Eloquent
 */
class MasterList extends Model
{
    protected $fillable = [
        'type_id',
        'id_num',
        'sn_num',
        'capacity',
        'accuracy',
        'unit_id',
        'brand',
        'calibration_type',
        'first_used',
        'rank',
        'calibration_freq',
        'acceptance_criteria',
        'pic',
        'location',
    ];

    protected $casts = [
        'first_used' => 'date',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'type_id', 'type_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function standard()
    {
        return $this->hasOne(Standard::class, 'id_num', 'id_num');
    }

    public function results()
    {
        return $this->hasMany(Result::class, 'id_num', 'id_num');
    }

    public function repairs()
    {
        return $this->hasMany(Repair::class, 'id_num', 'id_num');
    }

    public function latestResult()
    {
        return $this->hasOne(Result::class, 'id_num', 'id_num')->latestOfMany('calibration_date');
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->calculateStatus(),
        );
    }

    private function calculateStatus()
    {
        $latestResult = $this->latestResult;

        if (! $latestResult) {
            return 'NEW';
        }

        $previousResult = $this->results()
            ->where('id', '!=', $latestResult->id)
            ->orderBy('calibration_date', 'desc')
            ->first();

        if (! $previousResult) {
            return 'NEW';
        }

        $expectedDate = $previousResult->calibration_date->addMonths($this->calibration_freq);
        $actualDate = $latestResult->calibration_date;

        if ($actualDate->lt($expectedDate->copy()->subMonth())) {
            return 'EARLY';
        } elseif ($actualDate->gt($expectedDate->copy()->addMonth())) {
            return 'DELAY';
        } else {
            return 'ON TIME';
        }
    }
}
