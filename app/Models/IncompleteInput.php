<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $master_list_id
 * @property string $stage
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\MasterList|null $masterList
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncompleteInput atStage($stage)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncompleteInput forCurrentUser()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncompleteInput newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncompleteInput newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncompleteInput query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncompleteInput whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncompleteInput whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncompleteInput whereMasterListId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncompleteInput whereStage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncompleteInput whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncompleteInput whereUserId($value)
 * @mixin \Eloquent
 */
class IncompleteInput extends Model
{
    protected $fillable = [
        'user_id',
        'master_list_id',
        'stage',
    ];

    /**
     * Relasi ke user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke master list / equipment
     */
    public function masterList()
    {
        return $this->belongsTo(MasterList::class, 'master_list_id', 'id');
    }

    public function scopeForCurrentUser($query)
    {
        return $query->where('user_id', auth()->id());
    }

    public function scopeAtStage($query, $stage)
    {
        return $query->where('stage', $stage);
    }
}
