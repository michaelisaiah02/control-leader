<?php
namespace App\Models\ControlLeader;

use App\Models\ControlLeader\Department;
use Illuminate\Notifications\Notifiable;
use App\Models\ControlLeader\SchedulePlan;
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
 * @property string|null $control_session_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, SchedulePlan> $createdSchedules
 * @property-read int|null $created_schedules_count
 * @property-read Department|null $department
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereControlSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmployeeID($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;
    protected $connection = 'mysql_control_leader'; // override juga di sini
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
