<?php

namespace App\Models\ControlLeader;

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
 *
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
 *
 * @property string $package
 * @property string $answer_type
 * @property array<array-key, mixed>|null $choices
 * @property array<array-key, mixed>|null $require_problem_when
 * @property string|null $problem_label
 * @property string|null $countermeasure_label
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question activeOrdered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question forPackage(string $package)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereAnswerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereChoices($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereCountermeasureLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question wherePackage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereProblemLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereRequireProblemWhen($value)
 *
 * @property bool $extra_fields
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Question whereExtraFields($value)
 *
 * @mixin \Eloquent
 */
class Question extends ControlLeaderModel
{
    use HasFactory;

    protected $table = 'questions';

    protected $fillable = [
        'package',
        'question_text',
        'choices',
        'extra_fields',
        'problem_label',
        'countermeasure_label',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'choices' => 'array',
        'extra_fields' => 'boolean',
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

    public static function getNextOrder(string $package): int
    {
        $last = self::where('package', $package)->max('display_order');

        return $last ? $last + 1 : 1;
    }

    protected static function booted()
    {
        static::creating(function ($question) {
            $lastOrder = self::where('package', $question->package)->max('display_order');
            $question->display_order = $lastOrder ? $lastOrder + 1 : 1;
        });
    }
}
