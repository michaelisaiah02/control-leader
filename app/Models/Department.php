<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $department_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Division> $divisions
 * @property-read int|null $divisions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ControlLeaderUser> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereDepartmentName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Department extends Model
{
    use HasFactory;
    protected $connection = 'mysql_control_leader';
    protected $fillable = ['department_name'];

    // Satu departemen punya banyak divisi
    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class);
    }

    // Satu departemen punya banyak user (leader/supervisor)
    public function users(): HasMany
    {
        return $this->hasMany(ControlLeaderUser::class);
    }
}
