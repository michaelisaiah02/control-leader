<?php

namespace App\Models\ControlLeader;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetField newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetField newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChecksheetField query()
 * @mixin \Eloquent
 */
class ChecksheetField extends Model
{
    protected $fillable = ['label', 'type', 'options'];

    protected $casts = [
        'options' => 'array',
    ];
}
