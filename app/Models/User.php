<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Beri tahu model ini nama tabelnya adalah 'users'.
     */
    protected $table = 'users';

    protected $fillable = [
        'name',
        'employeeID',
        'department_id',
        'division_id',
        'password',
        'role',
        'superior_id',
        'can_login',
        'is_active',
    ];

    protected $hidden = [
        'password',
    ];

    public function superior(): BelongsTo
    {
        return $this->belongsTo(User::class, 'superior_id', 'employeeID');
    }

    public function inferiors(): HasMany
    {
        return $this->hasMany(User::class, 'superior_id', 'employeeID');
    }

    // User ini milik satu departemen
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    // User ini (sebagai scheduler) membuat banyak jadwal
    public function createdSchedules(): HasMany
    {
        return $this->hasMany(SchedulePlan::class, 'scheduler_id');
    }
}
