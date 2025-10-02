<?php

namespace App\Models\ControlLeader;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $division_name
 * @property int $department_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Department $department
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division whereDivisionName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Division whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Division extends ControlLeaderModel
{
    use HasFactory;

    protected $fillable = ['division_name', 'department_id'];

    // Satu divisi dimiliki oleh satu departemen
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
