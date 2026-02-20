<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    // Satu departemen punya banyak divisi
    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class);
    }

    // Satu departemen punya banyak user (leader/supervisor)
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
