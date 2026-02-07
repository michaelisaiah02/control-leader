<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecksheetField extends Model
{
    protected $fillable = ['label', 'type', 'options'];

    protected $casts = [
        'options' => 'array',
    ];
}
