<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;
    protected $connection = 'mysql_control_leader';
    protected $fillable = ['question_text', 'options', 'is_active'];
    protected $casts = [
        'options' => 'array', // Otomatis konversi JSON ke array
    ];
}
