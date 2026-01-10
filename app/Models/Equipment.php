<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $type_id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MasterList> $masterLists
 * @property-read int|null $master_lists_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Equipment whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Equipment extends Model
{
    protected $table = 'equipments';

    protected $fillable = [
        'type_id',
        'name',
    ];

    public function masterLists()
    {
        return $this->hasMany(MasterList::class);
    }
}
