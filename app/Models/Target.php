<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Target extends Model
{
    protected $fillable = ['report', 'value'];
    protected $primaryKey = 'report';
    public $incrementing = false;
}
