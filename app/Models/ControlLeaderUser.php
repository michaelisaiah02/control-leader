<?php

namespace App\Models;

use App\Models\Department;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $employeeID
 * @property int|null $department_id
 * @property string $password
 * @property string $role
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ControlLeaderUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ControlLeaderUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ControlLeaderUser query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ControlLeaderUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ControlLeaderUser whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ControlLeaderUser whereEmployeeID($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ControlLeaderUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ControlLeaderUser whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ControlLeaderUser wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ControlLeaderUser whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ControlLeaderUser whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SchedulePlan> $createdSchedules
 * @property-read int|null $created_schedules_count
 * @property-read Department|null $department
 * @mixin \Eloquent
 */
class ControlLeaderUser extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Beri tahu model ini untuk selalu menggunakan koneksi 'mysql_control_leader'.
     */
    protected $connection = 'mysql_control_leader';

    /**
     * Beri tahu model ini nama tabelnya adalah 'users'.
     */
    protected $table = 'users';

    protected $fillable = [
        'name',
        'employeeID',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
    ];

    // User ini milik satu departemen
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    // User ini (sebagai scheduler) membuat banyak jadwal
    public function createdSchedules(): HasMany
    {
        return $this->hasMany(SchedulePlan::class, 'scheduler_id');
    }
}
