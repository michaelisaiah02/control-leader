<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $id_num
 * @property string $param_01
 * @property string $param_02
 * @property string $param_03
 * @property string $param_04
 * @property string $param_05
 * @property string $param_06
 * @property string $param_07
 * @property string $param_08
 * @property string $param_09
 * @property string $param_10
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\MasterList $masterList
 * @method static \Database\Factories\StandardFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Standard newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Standard newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Standard query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Standard whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Standard whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Standard whereIdNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Standard whereParam01($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Standard whereParam02($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Standard whereParam03($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Standard whereParam04($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Standard whereParam05($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Standard whereParam06($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Standard whereParam07($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Standard whereParam08($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Standard whereParam09($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Standard whereParam10($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Standard whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Standard extends Model
{
    /** @use HasFactory<\Database\Factories\StandardFactory> */
    use HasFactory;

    protected $fillable = [
        'id_num',
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
    ];

    public function masterList()
    {
        return $this->belongsTo(MasterList::class, 'id_num', 'id_num');
    }
}
