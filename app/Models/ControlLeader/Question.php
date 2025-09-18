<?php

namespace App\Models\ControlLeader;

use Illuminate\Database\Eloquent\Model;
use App\Models\ControlLeader\ControlLeaderModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $question_code Kode unik untuk konsep pertanyaan
 * @property string $question_text
 * @property array<array-key, mixed>|null $options
 * @property int $display_order Urutan tampil di checksheet
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereOptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereQuestionCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereQuestionText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Question extends ControlLeaderModel
{
    use HasFactory;
    protected $table = 'questions';

    protected $fillable = [
        'package',
        'question_text',
        'answer_type',
        'choices',
        'require_problem_when',
        'problem_label',
        'countermeasure_label',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'choices' => 'array',
        'require_problem_when' => 'array',
        'is_active' => 'boolean',
    ];

    // scopes
    public function scopeForPackage($q, string $package)
    {
        return $q->where('package', $package);
    }
    public function scopeActiveOrdered($q)
    {
        return $q->where('is_active', true)->orderBy('display_order');
    }
}
