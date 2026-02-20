<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Division extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'department_id'];

    // Satu divisi dimiliki oleh satu departemen
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
